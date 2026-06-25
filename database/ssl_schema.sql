CREATE TABLE IF NOT EXISTS ssl_certs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) NOT NULL,
    certificate TEXT,
    private_key TEXT,
    ca_chain TEXT,
    issuer VARCHAR(255) DEFAULT '',
    expires_at DATETIME DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'active',
    auto_renew TINYINT(1) DEFAULT 1,
    last_renewal DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_domain (domain)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ssl_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    port INT DEFAULT 443,
    protocol VARCHAR(20) DEFAULT 'https',
    cert_id INT DEFAULT NULL,
    config_file TEXT,
    config_parsed TEXT,
    ssl_enabled TINYINT(1) DEFAULT 0,
    ssl_mode VARCHAR(50) DEFAULT 'native',
    status VARCHAR(50) DEFAULT 'pending',
    last_verified DATETIME DEFAULT NULL,
    last_error TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cert_id) REFERENCES ssl_certs(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ssl_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    domain VARCHAR(255) DEFAULT '',
    status VARCHAR(50) DEFAULT 'info',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO ssl_certs (domain, status) VALUES ('planet-hosts.com', 'active');
INSERT IGNORE INTO ssl_certs (domain, status) VALUES ('server.planet-hosts.com', 'active');
