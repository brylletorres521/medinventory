# Medical Inventory Management System

A comprehensive medical inventory management system with user authentication, expiry date tracking, and inventory management features.

## Features

- **User Authentication**
  - Secure login/logout system
  - Role-based access control (Admin and Regular User)
  - Profile management

- **Dashboard**
  - Overview of key metrics
  - Quick access to important features
  - Alerts for expiring medicines

- **Inventory Management**
  - Add, edit, view, and manage inventory items
  - Track medicine details (name, generic name, category, supplier, etc.)
  - Track batch numbers, quantities, and pricing

- **Expiry Date Tracking**
  - Filter medicines by expiry date (expired, expiring in 1/3/6 months)
  - Visual indicators for expiring medicines
  - Dashboard alerts for items expiring within 3 months

- **Advanced Filtering**
  - Filter by medicine name, category, supplier
  - Filter by batch number, storage location
  - Filter by expiry status

- **Transaction Management**
  - Record purchases, sales, returns, and adjustments
  - Track transaction history
  - User-specific transaction logs

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

## Installation

1. **Clone the repository or extract the files to your web server directory**

2. **Create the database**
   - Create a new MySQL database named `medical_inventory`
   - Import the `database.sql` file to create tables and sample data

3. **Configure database connection**
   - Open `config/db.php`
   - Update the database connection details if needed:
     ```php
     $host = "localhost";
     $username = "root";
     $password = "";
     $database = "medical_inventory";
     ```

4. **Access the system**
   - Navigate to the application URL in your browser
   - Default login credentials:
     - Admin: Username: `admin`, Password: `admin123`
     - User: Username: `user`, Password: `user123`

## Directory Structure

```
Medical Inventory/
├── admin/                  # Admin pages
├── user/                   # Regular user pages
├── assets/                 # CSS, JS, and other assets
├── config/                 # Configuration files
├── includes/               # Reusable PHP components
├── database.sql            # Database schema and sample data
├── index.php               # Entry point
├── login.php               # Login page
├── logout.php              # Logout script
└── README.md               # This file
```

## Usage

### Admin Features

- **Dashboard**: View system overview, expiring medicines, and recent transactions
- **Medicines**: Manage medicine catalog
- **Inventory**: Manage inventory items with expiry tracking
- **Categories**: Manage medicine categories
- **Suppliers**: Manage medicine suppliers
- **Transactions**: View and manage all transactions
- **Reports**: Generate and view reports
- **Users**: Manage system users

### User Features

- **Dashboard**: View inventory overview and expiring medicines
- **Inventory**: View inventory with filtering options
- **Transactions**: Record and view transactions

## Security Features

- Password hashing
- Prepared statements to prevent SQL injection
- Input validation and sanitization
- Role-based access control

## License

This project is licensed under the MIT License. 