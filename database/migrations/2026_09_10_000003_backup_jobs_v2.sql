-- Scheduled Backup Jobs (Contents/Matrix, daily/weekly/monthly)
-- Idempotent: safe to re-run on every deploy.

CREATE TABLE IF NOT EXISTS backup_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    contents TEXT NULL,
    schedule_type VARCHAR(10) NOT NULL DEFAULT 'daily',
    run_time VARCHAR(5) NOT NULL DEFAULT '03:00',
    run_day INT NULL,
    destination_id INT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_run_at DATETIME DEFAULT NULL,
    last_status VARCHAR(20) DEFAULT NULL,
    last_message VARCHAR(500) DEFAULT NULL,
    next_run_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_jobs_active (is_active),
    INDEX idx_jobs_next (next_run_at),
    INDEX idx_jobs_dest (destination_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;