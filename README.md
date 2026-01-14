# Travelism Office Management

A comprehensive office management system designed specifically for travel agencies and tourism businesses. This plugin provides tools for managing teams, clients, bookings, and office operations efficiently.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Architecture](#architecture)
- [API Reference](#api-reference)
- [Database Schema](#database-schema)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## Features

### Core Functionality

- **Employee Management**
  - Team member profiles and roles
  - Department organization
  - Performance tracking
  - Leave and attendance management

- **Client Management**
  - Client database with contact information
  - Client history and preferences
  - Communication log
  - Customer relationship tracking

- **Booking Management**
  - Travel package creation and management
  - Booking status tracking
  - Itinerary planning
  - Payment processing integration

- **Office Operations**
  - Resource management
  - Schedule management
  - Document management
  - Reporting and analytics

- **Dashboard & Analytics**
  - Real-time performance metrics
  - Revenue tracking
  - Client satisfaction scores
  - Team productivity insights

## Installation

### Prerequisites

- PHP 7.4 or higher
- WordPress 5.0 or higher
- MySQL 5.7 or higher
- Required PHP Extensions: cURL, JSON, PDO

### Step 1: Download the Plugin

```bash
# Clone the repository
git clone https://github.com/AlauddinAnsari/travelism-office-management.git
cd travelism-office-management
```

### Step 2: Install Dependencies

```bash
# Install composer dependencies (if applicable)
composer install

# Install npm dependencies (if applicable)
npm install
npm run build
```

### Step 3: Activate the Plugin

1. Copy the plugin folder to `/wp-content/plugins/`
2. Log in to WordPress admin panel
3. Navigate to **Plugins** > **Installed Plugins**
4. Find "Travelism Office Management" and click **Activate**

### Step 4: Initial Setup

1. Go to **Travelism** menu in WordPress admin
2. Navigate to **Settings** to configure the plugin
3. Set up your first office location
4. Invite team members

## Configuration

### Basic Settings

Access plugin settings via **Travelism > Settings** in the WordPress admin panel.

#### General Settings

```php
// In your admin panel, configure:
- Office Name
- Office Address
- Contact Email
- Support Phone
- Business Hours
```

#### API Configuration

```php
// Settings > API Keys
- Enable REST API
- API Key Generation
- API Rate Limiting
- IP Whitelist Management
```

#### Email Configuration

```php
// Settings > Email Templates
- SMTP Server
- Sender Email Address
- Reply-To Address
- Email Templates for notifications
```

#### Database Configuration

The plugin automatically creates necessary tables on activation. No manual configuration needed.

### Role & Permissions

**Available Roles:**

- **Admin** - Full system access
- **Manager** - Department/team management
- **Staff** - Basic access to assigned tasks
- **Viewer** - Read-only access
- **Client** - Client portal access

## Usage

### Getting Started

#### 1. Creating an Employee

```
Steps:
1. Navigate to Travelism > Employees
2. Click "Add New Employee"
3. Fill in:
   - Full Name
   - Email Address
   - Department
   - Role
   - Phone Number
   - Date of Joining
4. Click "Save Employee"
```

#### 2. Managing Clients

```
Steps:
1. Go to Travelism > Clients
2. Click "Add New Client"
3. Enter:
   - Client Name
   - Email
   - Phone
   - Address
   - Client Type (Individual/Corporate)
4. Save Client Information
```

#### 3. Creating Bookings

```
Steps:
1. Navigate to Travelism > Bookings
2. Click "New Booking"
3. Select Client
4. Choose Travel Package
5. Set Travel Dates
6. Add Special Requirements
7. Calculate and Confirm Price
8. Process Payment
```

#### 4. Viewing Reports

```
Steps:
1. Go to Travelism > Reports
2. Select Report Type:
   - Sales Report
   - Booking Report
   - Employee Performance
   - Revenue Analysis
3. Choose Date Range
4. Export as PDF/Excel if needed
```

### Advanced Features

#### Custom Workflows

Create custom booking workflows:

```
Settings > Workflows > Create New
- Define workflow stages
- Set automatic triggers
- Configure notifications
- Assign responsible parties
```

#### Integration with Third-Party Services

The plugin supports integration with:

- **Payment Gateways**: Stripe, PayPal, Razorpay
- **Email Services**: SendGrid, Mailgun
- **Calendar Systems**: Google Calendar, Outlook
- **CRM Systems**: HubSpot, Salesforce

#### Bulk Operations

```
1. Select multiple items (Bookings, Clients, etc.)
2. Choose "Bulk Actions" from dropdown
3. Select action (Export, Archive, Update)
4. Confirm and execute
```

## Architecture

### Plugin Structure

```
travelism-office-management/
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   ├── js/
│   │   ├── admin.js
│   │   └── frontend.js
│   └── images/
├── includes/
│   ├── classes/
│   │   ├── Employee.php
│   │   ├── Client.php
│   │   ├── Booking.php
│   │   ├── Report.php
│   │   └── Settings.php
│   ├── admin/
│   │   ├── pages/
│   │   └── menus/
│   ├── api/
│   │   └── endpoints/
│   ├── templates/
│   └── helpers/
├── templates/
│   ├── admin/
│   └── frontend/
├── languages/
├── travelism-office-management.php
└── README.md
```

### Core Classes

#### Employee Class

```php
namespace Travelism\Classes;

class Employee {
    public function __construct($id = null) { }
    public function create($data) { }
    public function update($data) { }
    public function delete() { }
    public function get_all() { }
    public function get_by_department($dept_id) { }
}
```

#### Client Class

```php
namespace Travelism\Classes;

class Client {
    public function __construct($id = null) { }
    public function create($data) { }
    public function update($data) { }
    public function delete() { }
    public function get_bookings() { }
    public function get_contact_history() { }
}
```

#### Booking Class

```php
namespace Travelism\Classes;

class Booking {
    public function __construct($id = null) { }
    public function create($data) { }
    public function update_status($status) { }
    public function calculate_cost() { }
    public function generate_itinerary() { }
    public function process_payment() { }
}
```

## API Reference

### REST API Endpoints

The plugin provides comprehensive REST API endpoints for programmatic access.

#### Authentication

All API endpoints require authentication using API keys or OAuth tokens.

```bash
# Add API key to request header
Authorization: Bearer YOUR_API_KEY
```

### Employee Endpoints

#### GET /wp-json/travelism/v1/employees

Get all employees.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/employees" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "department": "Sales",
      "role": "Manager",
      "status": "active"
    }
  ]
}
```

#### POST /wp-json/travelism/v1/employees

Create a new employee.

```bash
curl -X POST "https://example.com/wp-json/travelism/v1/employees" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "department": "Operations",
    "role": "Staff"
  }'
```

#### GET /wp-json/travelism/v1/employees/{id}

Get specific employee details.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/employees/1" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

#### PUT /wp-json/travelism/v1/employees/{id}

Update employee information.

```bash
curl -X PUT "https://example.com/wp-json/travelism/v1/employees/1" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Smith Updated",
    "role": "Senior Manager"
  }'
```

#### DELETE /wp-json/travelism/v1/employees/{id}

Delete an employee.

```bash
curl -X DELETE "https://example.com/wp-json/travelism/v1/employees/1" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

### Client Endpoints

#### GET /wp-json/travelism/v1/clients

Get all clients.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/clients" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

#### POST /wp-json/travelism/v1/clients

Create a new client.

```bash
curl -X POST "https://example.com/wp-json/travelism/v1/clients" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Client Name",
    "email": "client@example.com",
    "phone": "+1234567890",
    "type": "individual"
  }'
```

#### GET /wp-json/travelism/v1/clients/{id}

Get specific client details.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/clients/1" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

#### PUT /wp-json/travelism/v1/clients/{id}

Update client information.

```bash
curl -X PUT "https://example.com/wp-json/travelism/v1/clients/1" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Client Name",
    "phone": "+1234567890"
  }'
```

### Booking Endpoints

#### GET /wp-json/travelism/v1/bookings

Get all bookings.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/bookings" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

**Query Parameters:**
- `status` - Filter by status (pending, confirmed, completed, cancelled)
- `client_id` - Filter by client
- `from_date` - Start date (YYYY-MM-DD)
- `to_date` - End date (YYYY-MM-DD)

#### POST /wp-json/travelism/v1/bookings

Create a new booking.

```bash
curl -X POST "https://example.com/wp-json/travelism/v1/bookings" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "package_id": 5,
    "travel_date": "2026-03-15",
    "number_of_travelers": 2,
    "special_requirements": "Vegetarian meals"
  }'
```

#### GET /wp-json/travelism/v1/bookings/{id}

Get specific booking details.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/bookings/1" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

#### PUT /wp-json/travelism/v1/bookings/{id}

Update booking information.

```bash
curl -X PUT "https://example.com/wp-json/travelism/v1/bookings/1" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "confirmed",
    "special_requirements": "Updated requirements"
  }'
```

#### POST /wp-json/travelism/v1/bookings/{id}/payment

Process payment for a booking.

```bash
curl -X POST "https://example.com/wp-json/travelism/v1/bookings/1/payment" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_method": "credit_card",
    "amount": 50000,
    "currency": "USD"
  }'
```

### Report Endpoints

#### GET /wp-json/travelism/v1/reports/sales

Get sales report.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/reports/sales?from_date=2026-01-01&to_date=2026-01-31" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

#### GET /wp-json/travelism/v1/reports/bookings

Get bookings report.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/reports/bookings?status=confirmed" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

#### GET /wp-json/travelism/v1/reports/employee-performance

Get employee performance report.

```bash
curl -X GET "https://example.com/wp-json/travelism/v1/reports/employee-performance?employee_id=1" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

## Database Schema

### Tables

#### travelism_employees

```sql
CREATE TABLE travelism_employees (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  phone VARCHAR(20),
  department_id BIGINT UNSIGNED,
  role VARCHAR(50) NOT NULL,
  status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
  joining_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES travelism_departments(id)
);
```

#### travelism_clients

```sql
CREATE TABLE travelism_clients (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  phone VARCHAR(20),
  address TEXT,
  city VARCHAR(100),
  country VARCHAR(100),
  type ENUM('individual', 'corporate') DEFAULT 'individual',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### travelism_bookings

```sql
CREATE TABLE travelism_bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_reference VARCHAR(50) UNIQUE NOT NULL,
  client_id BIGINT UNSIGNED NOT NULL,
  package_id BIGINT UNSIGNED NOT NULL,
  travel_date DATE NOT NULL,
  return_date DATE,
  number_of_travelers INT DEFAULT 1,
  total_cost DECIMAL(10, 2),
  status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
  special_requirements TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES travelism_clients(id),
  FOREIGN KEY (package_id) REFERENCES travelism_packages(id)
);
```

#### travelism_packages

```sql
CREATE TABLE travelism_packages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description LONGTEXT,
  destination VARCHAR(255) NOT NULL,
  duration INT DEFAULT 1,
  duration_unit ENUM('days', 'weeks', 'months') DEFAULT 'days',
  base_price DECIMAL(10, 2),
  currency VARCHAR(3) DEFAULT 'USD',
  status ENUM('active', 'inactive', 'draft') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### travelism_payments

```sql
CREATE TABLE travelism_payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(10, 2) NOT NULL,
  currency VARCHAR(3) DEFAULT 'USD',
  payment_method VARCHAR(50),
  status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  transaction_id VARCHAR(255),
  payment_date TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES travelism_bookings(id)
);
```

#### travelism_departments

```sql
CREATE TABLE travelism_departments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL UNIQUE,
  description TEXT,
  manager_id BIGINT UNSIGNED,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Troubleshooting

### Common Issues and Solutions

#### 1. Plugin Not Activating

**Problem:** Plugin fails to activate with database error.

**Solution:**
- Check if all required tables are created
- Verify database user has proper permissions
- Check WordPress error logs at `/wp-content/debug.log`
- Run database repair: `wp db repair` (WP-CLI)

#### 2. API Endpoints Returning 403 Errors

**Problem:** API calls failing with permission denied.

**Solution:**
- Verify API key is valid
- Check user role has API access permissions
- Ensure REST API is enabled in settings
- Verify IP address is not blocked

#### 3. Slow Dashboard Loading

**Problem:** Admin dashboard takes too long to load.

**Solution:**
- Enable caching in settings
- Optimize database queries
- Clear browser cache
- Check for background tasks: `wp travelism queue list`

#### 4. Email Notifications Not Sending

**Problem:** Booking confirmations and notifications not being sent.

**Solution:**
- Verify SMTP settings in plugin configuration
- Check email service status (SendGrid, Mailgun, etc.)
- Review email logs: **Travelism > Logs > Email**
- Test email: **Settings > Email > Send Test Email**

#### 5. Payment Processing Failures

**Problem:** Payments not being processed correctly.

**Solution:**
- Verify payment gateway API keys are correct
- Check payment logs: **Travelism > Logs > Payments**
- Ensure SSL certificate is valid
- Contact payment gateway support if issue persists

### Debug Mode

Enable debug mode for detailed logging:

```php
// Add to wp-config.php
define('TRAVELISM_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Support and Documentation

- **Documentation:** [Full Documentation](https://docs.travelism-office.local/)
- **Support Email:** support@travelism-office.local
- **Issue Tracker:** [GitHub Issues](https://github.com/AlauddinAnsari/travelism-office-management/issues)

## Contributing

We welcome contributions! Please follow these guidelines:

### Development Setup

```bash
# Clone repository
git clone https://github.com/AlauddinAnsari/travelism-office-management.git
cd travelism-office-management

# Install dependencies
composer install
npm install

# Create development branch
git checkout -b feature/your-feature-name
```

### Code Standards

- Follow PSR-12 coding standards
- Write unit tests for new features
- Add documentation for API changes
- Use meaningful commit messages

### Submitting Pull Requests

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests and documentation
5. Submit a pull request with detailed description

### Testing

```bash
# Run unit tests
npm run test

# Run PHP tests
composer test

# Check code standards
composer lint
```

## License

This project is licensed under the GPL v2 or later License. See LICENSE file for details.

---

## Changelog

### Version 1.0.0 (2026-01-14)

**Initial Release**
- Employee management system
- Client relationship management
- Booking and itinerary management
- Payment processing integration
- Reporting and analytics dashboard
- REST API with comprehensive endpoints
- Multi-role access control
- Email notification system
- Database schema and migrations

---

**Last Updated:** 2026-01-14

For more information and updates, visit our [GitHub repository](https://github.com/AlauddinAnsari/travelism-office-management).