-- Create the database
CREATE DATABASE IF NOT EXISTS medical_inventory;
USE medical_inventory;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- Create suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT
);

-- Create medicines table
CREATE TABLE IF NOT EXISTS medicines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    generic_name VARCHAR(100),
    category_id INT,
    supplier_id INT,
    description TEXT,
    storage_location VARCHAR(100),
    unit VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- Create inventory table
CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medicine_id INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    purchase_price DECIMAL(10, 2) NOT NULL,
    selling_price DECIMAL(10, 2) NOT NULL,
    expiry_date DATE NOT NULL,
    manufacturing_date DATE,
    purchase_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE
);

-- Create inventory_transactions table
CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    transaction_type ENUM('purchase', 'sale', 'return', 'adjustment', 'expired') NOT NULL,
    quantity INT NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL,
    notes TEXT,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role) VALUES 
('admin', '$2y$10$8WxhBjFyQHids5YjWOJUAu7/QrFa3IVdBxZRD1DC3XZcD3GzuYPTO', 'Administrator', 'admin@example.com', 'admin');

-- Insert default user (password: user123)
INSERT INTO users (username, password, full_name, email, role) VALUES 
('user', '$2y$10$FvSDmYTYTSUfZ7xsrPX9xOAJl0cN9JJ7Wd0GYrUlwY9OHUVPYsdZO', 'Regular User', 'user@example.com', 'user');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Antibiotics', 'Medications that inhibit the growth of or destroy bacteria'),
('Analgesics', 'Pain relievers'),
('Antivirals', 'Medications used to treat viral infections'),
('Antipyretics', 'Fever reducers'),
('Vaccines', 'Preparations that provide immunity to specific diseases');

-- Insert sample suppliers
INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES 
('MediPharm Supplies', 'John Smith', '555-123-4567', 'contact@medipharm.com', '123 Pharma St, Medical City'),
('HealthCare Products', 'Jane Doe', '555-987-6543', 'info@healthcareproducts.com', '456 Health Ave, Wellness Town');

-- Insert sample medicines
INSERT INTO medicines (name, generic_name, category_id, supplier_id, description, storage_location, unit) VALUES 
('Amoxicillin 500mg', 'Amoxicillin', 1, 1, 'Broad-spectrum antibiotic', 'Shelf A1', 'Capsule'),
('Paracetamol 500mg', 'Acetaminophen', 2, 2, 'Pain reliever and fever reducer', 'Shelf B2', 'Tablet'),
('Ibuprofen 400mg', 'Ibuprofen', 2, 1, 'NSAID pain reliever', 'Shelf B3', 'Tablet'),
('Oseltamivir 75mg', 'Oseltamivir Phosphate', 3, 2, 'Antiviral medication for influenza', 'Shelf C1', 'Capsule'),
('Flu Vaccine', 'Influenza Vaccine', 5, 1, 'Annual flu vaccine', 'Refrigerator 1', 'Vial');

-- Insert sample inventory with different expiry dates
-- Current date medicines
INSERT INTO inventory (medicine_id, batch_number, quantity, purchase_price, selling_price, expiry_date, manufacturing_date, purchase_date) VALUES 
(1, 'AMX20230101', 100, 0.50, 1.00, DATE_ADD(CURRENT_DATE(), INTERVAL 24 MONTH), DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH), CURRENT_DATE()),
(2, 'PCM20230201', 200, 0.20, 0.50, DATE_ADD(CURRENT_DATE(), INTERVAL 36 MONTH), DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH), CURRENT_DATE()),
(3, 'IBU20230301', 150, 0.30, 0.75, DATE_ADD(CURRENT_DATE(), INTERVAL 30 MONTH), DATE_SUB(CURRENT_DATE(), INTERVAL 4 MONTH), CURRENT_DATE()),
-- Medicines expiring in 2 months (for testing expiry filter)
(4, 'OSL20220501', 50, 2.00, 4.50, DATE_ADD(CURRENT_DATE(), INTERVAL 2 MONTH), DATE_SUB(CURRENT_DATE(), INTERVAL 10 MONTH), DATE_SUB(CURRENT_DATE(), INTERVAL 9 MONTH)),
-- Medicines expiring in 3 months (for testing expiry filter)
(5, 'FLU20220601', 30, 10.00, 15.00, DATE_ADD(CURRENT_DATE(), INTERVAL 3 MONTH), DATE_SUB(CURRENT_DATE(), INTERVAL 9 MONTH), DATE_SUB(CURRENT_DATE(), INTERVAL 8 MONTH)); 