-- Billing Plugin Schema
-- Run this to add billing tables

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    reseller_id INT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    date DATE NOT NULL,
    due_date DATE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,4) DEFAULT 0.0000,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled', 'refunded') DEFAULT 'draft',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE SET NULL,
    FOREIGN KEY (reseller_id) REFERENCES resellers(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_reseller_id (reseller_id),
    INDEX idx_status (status),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
