-- Update database schema to add missing columns

USE medical_inventory;

-- Add missing columns to medicines table
ALTER TABLE medicines 
ADD COLUMN dosage_form VARCHAR(50) AFTER generic_name,
ADD COLUMN strength VARCHAR(50) AFTER dosage_form,
ADD COLUMN unit_price DECIMAL(10, 2) DEFAULT 0.00 AFTER strength;

-- Add missing columns to suppliers table
ALTER TABLE suppliers 
ADD COLUMN city VARCHAR(100) AFTER address,
ADD COLUMN state VARCHAR(100) AFTER city,
ADD COLUMN zip_code VARCHAR(20) AFTER state,
ADD COLUMN country VARCHAR(100) AFTER zip_code;

-- Add missing columns to users table
ALTER TABLE users 
ADD COLUMN phone VARCHAR(20) AFTER email,
ADD COLUMN last_login TIMESTAMP NULL AFTER created_at;

-- Add missing columns to inventory_transactions table
ALTER TABLE inventory_transactions 
ADD COLUMN unit_price DECIMAL(10, 2) DEFAULT 0.00 AFTER quantity,
ADD COLUMN total_amount DECIMAL(10, 2) DEFAULT 0.00 AFTER unit_price;

-- Update existing medicines with sample data
UPDATE medicines SET 
dosage_form = 'Tablet',
strength = '500mg',
unit_price = 1.00
WHERE id = 1;

UPDATE medicines SET 
dosage_form = 'Tablet',
strength = '500mg',
unit_price = 0.50
WHERE id = 2;

UPDATE medicines SET 
dosage_form = 'Tablet',
strength = '400mg',
unit_price = 0.75
WHERE id = 3;

UPDATE medicines SET 
dosage_form = 'Capsule',
strength = '75mg',
unit_price = 4.50
WHERE id = 4;

UPDATE medicines SET 
dosage_form = 'Vial',
strength = 'Standard',
unit_price = 15.00
WHERE id = 5;

-- Update existing suppliers with sample data
UPDATE suppliers SET 
city = 'Medical City',
state = 'Health State',
zip_code = '12345',
country = 'USA'
WHERE id = 1;

UPDATE suppliers SET 
city = 'Wellness Town',
state = 'Wellness State',
zip_code = '67890',
country = 'USA'
WHERE id = 2;

-- Update existing users with sample data
UPDATE users SET 
phone = '555-000-0001'
WHERE id = 1;

UPDATE users SET 
phone = '555-000-0002'
WHERE id = 2; 