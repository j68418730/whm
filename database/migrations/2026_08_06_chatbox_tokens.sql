-- 2026-08-06: Chatbox token auth for desktop/embedded apps.
-- Tokens let a user authenticate without PHP session cookies (WebView2 / native clients).
CREATE TABLE IF NOT EXISTS chatbox_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(80) NOT NULL,
  user_id INT NOT NULL,
  tenant_id INT NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'member',
  created_by INT DEFAULT NULL,
  last_used_at DATETIME DEFAULT NULL,
  expires_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_token (token),
  KEY idx_user (user_id),
  KEY idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
