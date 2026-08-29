-- 2026-08-30: Private agent notes on chat sessions
-- Agents can add notes visible only to staff, not to customers

CREATE TABLE IF NOT EXISTS chat_notes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    session_id INT(11) NOT NULL,
    admin_id INT(11) NOT NULL,
    note TEXT NOT NULL,
    is_private TINYINT(1) NOT NULL DEFAULT 1, -- 1 = agent-only, 0 = visible to customer (future)
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_session (session_id),
    KEY idx_admin (admin_id),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foreign key to chat_sessions (if exists)
-- ALTER TABLE chat_notes ADD CONSTRAINT fk_chat_notes_session FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE;
-- ALTER TABLE chat_notes ADD CONSTRAINT fk_chat_notes_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE;