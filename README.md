# Travelism Office Management

A comprehensive WordPress plugin for managing travel agency operations including customers, services, visa applications, tasks, and analytics.

## Description

Travelism Office Management is a production-ready WordPress plugin designed specifically for travel agencies to streamline their operations. It provides a complete solution for managing customer relationships, service bookings, visa applications, task assignments, and business analytics.

## Features

### 🎯 Core Modules

- **Customer Management** - Full CRUD operations, email notifications, assignment tracking
- **Service Management** - Booking management, payment tracking, CSV export
- **Visa Management** - Application tracking, document management, status updates
- **Task Management** - Assignment system, progress tracking, comments, reminders
- **Analytics** - Dashboard with statistics, charts (Chart.js), and comprehensive reports

### 🔒 Security Features

- Nonce verification on all AJAX requests
- Input sanitization and output escaping
- SQL injection prevention with prepared statements
- Capability-based access control
- Rate limiting on API endpoints
- Complete security audit trail

### 👥 Role-Based Access Control

- **CEO** - Full access to all features including reports and user management
- **Manager** - Project and team management capabilities
- **Employee** - Limited access to assigned tasks and personal records

### 📊 Database

- 6 optimized custom tables with proper indexing
- Foreign key relationships
- Activity logging for audit trail
- Versioned schema with safe migrations

### 🎨 User Interface

- Professional responsive dashboard
- Real-time charts using Chart.js
- AJAX-powered modal forms
- Travelism Red + White branding
- Mobile, tablet, and desktop responsive

### ⚡ Performance

- Database query optimization
- Proper indexing on all tables
- Query result caching
- Lazy module loading
- Efficient AJAX handling

## Installation

1. Upload the plugin files to `/wp-content/plugins/travelism-office-management/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to 'Travelism Office' in the admin menu
4. Configure settings and start managing your travel agency

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Modern web browser with JavaScript enabled

## Database Tables

The plugin creates the following tables:

- `wp_travelism_customers` - Customer information
- `wp_travelism_leads` - Lead tracking
- `wp_travelism_services` - Service offerings
- `wp_travelism_visas` - Visa applications
- `wp_travelism_tasks` - Task management
- `wp_travelism_activity_logs` - Activity audit trail

## File Structure

```
travelism-office-management/
├── assets/
│   ├── css/
│   │   ├── admin-main.css
│   │   ├── dashboard.css
│   │   └── brand-style.css
│   └── js/
│       ├── admin-main.js
│       ├── modal-handler.js
│       ├── chart-manager.js
│       ├── dashboard.js
│       └── dashboard-charts.js
├── includes/
│   ├── Admin/
│   │   ├── class-admin-menu.php
│   │   └── class-admin-dashboard.php
│   ├── API/
│   │   └── class-rest-api.php
│   ├── Modules/
│   │   ├── Customers/
│   │   ├── Services/
│   │   ├── Visa/
│   │   ├── Tasks/
│   │   └── Analytics/
│   ├── Utilities/
│   │   ├── Logger.php
│   │   ├── Validator.php
│   │   ├── Formatter.php
│   │   ├── Notifications.php
│   │   └── Security.php
│   ├── class-travelism-plugin.php
│   ├── class-database.php
│   └── class-capabilities.php
├── templates/
│   └── admin/
│       ├── dashboard.php
│       ├── customers/
│       ├── services/
│       ├── visas/
│       ├── tasks/
│       ├── leads/
│       ├── analytics.php
│       └── settings.php
├── languages/
│   └── travelism-office-management.pot
├── travelism-office-management.php
├── uninstall.php
├── composer.json
└── README.md
```

## Usage

### Managing Customers

1. Navigate to Travelism Office > Customers
2. Click "Add New Customer" to create customer records
3. View, edit, or delete customers from the list
4. Assign customers to team members

### Processing Visas

1. Go to Travelism Office > Visas
2. Create new visa applications
3. Upload required documents
4. Track application status
5. Update completion dates

### Managing Tasks

1. Access Travelism Office > Tasks
2. Create tasks and assign to team members
3. Set priorities and due dates
4. Track progress and completion

### Viewing Analytics

1. Visit Travelism Office > Dashboard
2. View key statistics and metrics
3. Analyze charts for trends
4. Export reports as needed

## Development

### Coding Standards

- Follows WordPress Coding Standards
- PSR-4 autoloading for classes
- Comprehensive inline documentation
- Proper error handling throughout

### Hooks and Filters

The plugin provides numerous hooks for extensibility:

```php
// Filter modules to load
add_filter('travelism_modules', 'custom_modules');

// Action after plugin activation
add_action('travelism_plugin_activated', 'custom_activation');

// Filter default options
add_filter('travelism_default_options', 'custom_options');
```

## Support

For support, feature requests, or bug reports:
- Email: support@travelism.com
- Website: https://travelism.com/support

## Changelog

### 1.0.0 - 2026-01-14
- Initial release
- Customer management module
- Service management module
- Visa tracking module
- Task management module
- Analytics dashboard
- Role-based access control
- REST API endpoints
- Comprehensive security features

## Credits

Developed by Travelism Team

## License

This plugin is licensed under GPL v2 or later.
Copyright (C) 2026 Travelism

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
