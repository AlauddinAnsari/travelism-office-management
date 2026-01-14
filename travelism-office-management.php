<?php
/**
 * Travelism Office Management System
 * Enhanced Version with Security Features
 * 
 * @package TravelismOfficeManagement
 * @version 2.0.0
 * @author Alauddin Ansari
 * @created 2026-01-14
 * @license MIT
 */

// ============================================================================
// SECURITY: Define Security Constants & Headers
// ============================================================================

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Set security headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// ============================================================================
// CONFIGURATION
// ============================================================================

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'travelism_office');

// Security configuration
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: '');
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// ============================================================================
// SECURITY CLASS: Core Security Functions
// ============================================================================

class SecurityManager {
    
    /**
     * Initialize security measures
     */
    public static function init() {
        session_set_cookie_params([
            'lifetime' => SESSION_TIMEOUT,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically
        self::regenerateSessionId();
    }
    
    /**
     * Regenerate session ID for security
     */
    private static function regenerateSessionId() {
        $timeout = 5 * 60; // 5 minutes
        $last_regenerate = $_SESSION['last_regenerate'] ?? 0;
        
        if (time() - $last_regenerate > $timeout) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();
        }
    }
    
    /**
     * Sanitize input to prevent XSS attacks
     * 
     * @param string $input User input
     * @param string $type Type of sanitization (text, email, url, number)
     * @return string Sanitized input
     */
    public static function sanitize($input, $type = 'text') {
        if (empty($input)) {
            return '';
        }
        
        $input = trim($input);
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'number':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'text':
            default:
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate input with rules
     * 
     * @param array $data Input data
     * @param array $rules Validation rules
     * @return array Validation errors
     */
    public static function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            if ($rule['required'] && empty($value)) {
                $errors[$field] = "{$field} is required";
                continue;
            }
            
            if (isset($rule['min']) && strlen($value) < $rule['min']) {
                $errors[$field] = "{$field} must be at least {$rule['min']} characters";
            }
            
            if (isset($rule['max']) && strlen($value) > $rule['max']) {
                $errors[$field] = "{$field} cannot exceed {$rule['max']} characters";
            }
            
            if (isset($rule['email']) && $rule['email']) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "{$field} must be a valid email";
                }
            }
            
            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = "{$field} format is invalid";
            }
        }
        
        return $errors;
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string CSRF token
     */
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @return bool Token is valid
     */
    public static function verifyCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Hash password securely
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     * 
     * @param string $password Plain text password
     * @param string $hash Password hash
     * @return bool Password matches
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Log security events
     * 
     * @param string $event Event type
     * @param string $details Event details
     * @param string $level Log level (info, warning, error)
     */
    public static function logEvent($event, $details = '', $level = 'info') {
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getClientIp();
        $user = $_SESSION['user_id'] ?? 'ANONYMOUS';
        
        $logMessage = "[{$timestamp}] [{$level}] [{$user}] [{$ip}] {$event}";
        if (!empty($details)) {
            $logMessage .= " - {$details}";
        }
        
        $logDir = ABSPATH . 'logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0750, true);
        }
        
        $logFile = $logDir . '/security_' . date('Y-m-d') . '.log';
        error_log($logMessage . PHP_EOL, 3, $logFile);
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP
     */
    public static function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        }
        
        return filter_var(trim($ip), FILTER_VALIDATE_IP) ? trim($ip) : 'INVALID_IP';
    }
    
    /**
     * Check rate limiting
     * 
     * @param string $key Rate limit key (e.g., 'login_' . $email)
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $windowSeconds Time window in seconds
     * @return array Rate limit status
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $windowSeconds = 900) {
        $cacheKey = 'ratelimit_' . md5($key);
        
        // Initialize or get existing attempts
        if (!isset($_SESSION[$cacheKey])) {
            $_SESSION[$cacheKey] = [
                'attempts' => 0,
                'first_attempt' => time(),
                'locked_until' => 0
            ];
        }
        
        $record = &$_SESSION[$cacheKey];
        $now = time();
        
        // Check if lockout period has expired
        if ($record['locked_until'] > 0 && $now > $record['locked_until']) {
            $record['attempts'] = 0;
            $record['locked_until'] = 0;
        }
        
        // Check if within time window
        if ($now - $record['first_attempt'] > $windowSeconds) {
            $record['attempts'] = 0;
            $record['first_attempt'] = $now;
        }
        
        return [
            'allowed' => $record['attempts'] < $maxAttempts && $record['locked_until'] <= $now,
            'attempts' => $record['attempts'],
            'max_attempts' => $maxAttempts,
            'locked_until' => $record['locked_until'],
            'retry_after' => max(0, $record['locked_until'] - $now)
        ];
    }
    
    /**
     * Record failed attempt for rate limiting
     * 
     * @param string $key Rate limit key
     */
    public static function recordFailedAttempt($key, $maxAttempts = 5) {
        $cacheKey = 'ratelimit_' . md5($key);
        
        if (!isset($_SESSION[$cacheKey])) {
            $_SESSION[$cacheKey] = [
                'attempts' => 0,
                'first_attempt' => time(),
                'locked_until' => 0
            ];
        }
        
        $_SESSION[$cacheKey]['attempts']++;
        
        if ($_SESSION[$cacheKey]['attempts'] >= $maxAttempts) {
            $_SESSION[$cacheKey]['locked_until'] = time() + LOCKOUT_DURATION;
        }
    }
    
    /**
     * Clear failed attempts
     * 
     * @param string $key Rate limit key
     */
    public static function clearFailedAttempts($key) {
        $cacheKey = 'ratelimit_' . md5($key);
        unset($_SESSION[$cacheKey]);
    }
}

// ============================================================================
// DATABASE CLASS: Secure Database Operations
// ============================================================================

class Database {
    private $connection;
    private $prepared_stmt;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        try {
            $this->connection = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME
            );
            
            if ($this->connection->connect_error) {
                throw new Exception('Database connection failed');
            }
            
            // Set charset to utf8mb4
            $this->connection->set_charset('utf8mb4');
            
        } catch (Exception $e) {
            SecurityManager::logEvent('DB_CONNECTION_FAILED', $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Execute prepared statement to prevent SQL injection
     * 
     * @param string $query SQL query with placeholders (?)
     * @param array $params Query parameters
     * @return mixed Query result
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $this->connection->error);
            }
            
            if (!empty($params)) {
                $types = $this->getParamTypes($params);
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            return $stmt->get_result();
            
        } catch (Exception $e) {
            SecurityManager::logEvent('DB_QUERY_ERROR', $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get parameter types for prepared statements
     * 
     * @param array $params Parameters
     * @return string Type string
     */
    private function getParamTypes($params) {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
    
    /**
     * Get single row
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array|null Single row or null
     */
    public function getRow($query, $params = []) {
        $result = $this->query($query, $params);
        return $result->fetch_assoc();
    }
    
    /**
     * Get all rows
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array All rows
     */
    public function getRows($query, $params = []) {
        $result = $this->query($query, $params);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }
}

// ============================================================================
// AUTHENTICATION CLASS
// ============================================================================

class AuthenticationManager {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    /**
     * Register new user
     * 
     * @param array $data User data (email, password, name, etc.)
     * @return array Success status and message
     */
    public function register($data) {
        $errors = SecurityManager::validate($data, [
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'min' => 8, 'max' => 255],
            'confirm_password' => ['required' => true],
            'name' => ['required' => true, 'min' => 2, 'max' => 255]
        ]);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Check password strength
        if (!self::isPasswordStrong($data['password'])) {
            return [
                'success' => false,
                'message' => 'Password must contain uppercase, lowercase, numbers, and special characters'
            ];
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }
        
        // Check if email already exists
        $existing = $this->db->getRow(
            'SELECT id FROM users WHERE email = ?',
            [$data['email']]
        );
        
        if ($existing) {
            SecurityManager::logEvent('REGISTER_DUPLICATE_EMAIL', $data['email']);
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Create user
        $hashed_password = SecurityManager::hashPassword($data['password']);
        $email = SecurityManager::sanitize($data['email'], 'email');
        $name = SecurityManager::sanitize($data['name'], 'text');
        
        try {
            $this->db->query(
                'INSERT INTO users (email, password, name, created_at) VALUES (?, ?, ?, NOW())',
                [$email, $hashed_password, $name]
            );
            
            SecurityManager::logEvent('USER_REGISTERED', $email);
            return ['success' => true, 'message' => 'Registration successful'];
            
        } catch (Exception $e) {
            SecurityManager::logEvent('REGISTER_ERROR', $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    /**
     * Login user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return array Success status and message
     */
    public function login($email, $password) {
        // Check rate limiting
        $rateLimit = SecurityManager::checkRateLimit(
            'login_' . $email,
            MAX_LOGIN_ATTEMPTS,
            LOCKOUT_DURATION
        );
        
        if (!$rateLimit['allowed']) {
            SecurityManager::logEvent('LOGIN_BLOCKED_RATELIMIT', $email, 'warning');
            return [
                'success' => false,
                'message' => 'Too many failed attempts. Please try again later.',
                'retry_after' => $rateLimit['retry_after']
            ];
        }
        
        $email = SecurityManager::sanitize($email, 'email');
        
        $user = $this->db->getRow(
            'SELECT id, password FROM users WHERE email = ? AND status = "active"',
            [$email]
        );
        
        if (!$user || !SecurityManager::verifyPassword($password, $user['password'])) {
            SecurityManager::recordFailedAttempt('login_' . $email, MAX_LOGIN_ATTEMPTS);
            SecurityManager::logEvent('LOGIN_FAILED', $email);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Clear failed attempts on successful login
        SecurityManager::clearFailedAttempts('login_' . $email);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        $_SESSION['login_time'] = time();
        
        SecurityManager::logEvent('USER_LOGIN', $email);
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool User is authenticated
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Check session timeout
     * 
     * @return bool Session is valid
     */
    public static function isSessionValid() {
        if (!self::isAuthenticated()) {
            return false;
        }
        
        $currentTime = time();
        $loginTime = $_SESSION['login_time'] ?? 0;
        
        if ($currentTime - $loginTime > SESSION_TIMEOUT) {
            session_destroy();
            return false;
        }
        
        // Update last activity time
        $_SESSION['login_time'] = $currentTime;
        return true;
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        if (self::isAuthenticated()) {
            SecurityManager::logEvent('USER_LOGOUT', $_SESSION['email']);
        }
        
        $_SESSION = [];
        session_destroy();
    }
    
    /**
     * Check password strength
     * 
     * @param string $password Password to check
     * @return bool Password is strong
     */
    private static function isPasswordStrong($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
    }
}

// ============================================================================
// INITIALIZATION
// ============================================================================

// Initialize security manager
SecurityManager::init();

// Create CSRF token for forms
$csrf_token = SecurityManager::generateCsrfToken();

?>
