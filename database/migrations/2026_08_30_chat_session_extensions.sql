-- 2026-08-30: Extend chat_sessions for routing, queues, assignment, and priority

-- Add routing/assignment columns to chat_sessions
ALTER TABLE chat_sessions
    ADD COLUMN IF NOT EXISTS assigned_agent_id INT(11) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS department_id INT(11) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS queue_position INT(11) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS priority ENUM('low','normal','high','urgent') DEFAULT 'normal',
    ADD COLUMN IF NOT EXISTS last_activity DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS transferred_from_id INT(11) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS transfer_reason VARCHAR(255) DEFAULT '',
    ADD COLUMN IF NOT EXISTS closed_by INT(11) DEFAULT NULL;

-- Indexes for routing queries
CREATE INDEX IF NOT EXISTS idx_chat_sessions_assigned ON chat_sessions (assigned_agent_id);
CREATE INDEX IF NOT EXISTS idx_chat_sessions_department ON chat_sessions (department_id);
CREATE INDEX IF NOT EXISTS idx_chat_sessions_status_dept ON chat_sessions (status, department_id);
CREATE INDEX IF NOT EXISTS idx_chat_sessions_queue ON chat_sessions (status, queue_position);
CREATE INDEX IF NOT EXISTS idx_chat_sessions_last_activity ON chat_sessions (last_activity);