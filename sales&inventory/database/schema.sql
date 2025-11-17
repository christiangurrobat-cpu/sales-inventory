-- Sales & Inventory System for Fast Food Restaurant
-- Database Schema

CREATE DATABASE IF NOT EXISTS fastfood_inventory;
USE fastfood_inventory;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products/Items table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    cost DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'piece',
    image_path VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Sales/Orders table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    discount DECIMAL(10, 2) DEFAULT 0,
    final_amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'card', 'mobile') DEFAULT 'cash',
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sales Items table (order details)
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Inventory transactions (for tracking stock movements)
CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    transaction_type ENUM('purchase', 'sale', 'adjustment', 'return') NOT NULL,
    quantity INT NOT NULL,
    reference_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Burgers', 'Various burger options'),
('Fries & Sides', 'French fries and side dishes'),
('Beverages', 'Soft drinks, juices, and other beverages'),
('Desserts', 'Ice cream, cakes, and sweet treats'),
('Combo Meals', 'Complete meal packages');

-- Insert default users
INSERT IGNORE INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$51BgZhwZVAGtfdtKYJHn8ObyPPLFfasv3yKmalf4/J5Sp2lp8OTYG', 'admin'),
('customer', '$2y$10$q/vD/3pLUlnGayOl83FBRuH.4tynE/qpIDHEBOyiKHOwj6GWDhvCC', 'customer');

-- Insert sample products
INSERT INTO products (name, category_id, description, price, cost, stock_quantity, unit) VALUES
('Classic Burger', 1, 'Beef patty with lettuce, tomato, and special sauce', 5.99, 2.50, 50, 'piece'),
('Cheese Burger', 1, 'Classic burger with cheese', 6.99, 3.00, 45, 'piece'),
('Bacon Burger', 1, 'Burger with crispy bacon', 7.99, 3.50, 40, 'piece'),
('French Fries (Small)', 2, 'Crispy golden fries', 2.99, 0.80, 100, 'serving'),
('French Fries (Large)', 2, 'Large portion of fries', 4.99, 1.50, 80, 'serving'),
('Onion Rings', 2, 'Crispy fried onion rings', 3.99, 1.20, 60, 'serving'),
('Cola', 3, 'Refreshing cola drink', 1.99, 0.50, 200, 'bottle'),
('Orange Juice', 3, 'Fresh orange juice', 2.49, 0.80, 150, 'bottle'),
('Ice Cream', 4, 'Vanilla ice cream', 3.99, 1.50, 70, 'serving'),
('Combo Meal #1', 5, 'Burger + Fries + Drink', 8.99, 4.00, 30, 'set');


