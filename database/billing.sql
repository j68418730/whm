CREATE TABLE IF NOT EXISTS billing_products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  type ENUM('hosting','radio','vps','domain','addon','other') DEFAULT 'hosting',
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  setup_fee DECIMAL(10,2) DEFAULT 0.00,
  billing_cycle ENUM('monthly','quarterly','semiannual','annual','biennial') DEFAULT 'monthly',
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  type ENUM('new','renewal','upgrade','downgrade') DEFAULT 'new',
  status ENUM('pending','active','suspended','cancelled') DEFAULT 'pending',
  total DECIMAL(10,2) DEFAULT 0.00,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  order_id INT DEFAULT NULL,
  domain VARCHAR(255) DEFAULT '',
  status ENUM('active','suspended','terminated','pending') DEFAULT 'pending',
  billing_cycle ENUM('monthly','quarterly','semiannual','annual','biennial') DEFAULT 'monthly',
  price DECIMAL(10,2) DEFAULT 0.00,
  next_due_date DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  invoice_id INT DEFAULT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('credit_card','paypal','bank_transfer','manual','credit') DEFAULT 'manual',
  status ENUM('pending','completed','failed','refunded') DEFAULT 'completed',
  transaction_id VARCHAR(255) DEFAULT '',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_taxes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  rate DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
  country VARCHAR(5) DEFAULT '',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_coupons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  type ENUM('percentage','fixed') DEFAULT 'percentage',
  value DECIMAL(10,2) NOT NULL,
  max_uses INT DEFAULT 0,
  used_count INT DEFAULT 0,
  min_total DECIMAL(10,2) DEFAULT 0.00,
  expires_at DATE DEFAULT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_credits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  description VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_refunds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  payment_id INT DEFAULT NULL,
  invoice_id INT DEFAULT NULL,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
