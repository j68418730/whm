-- 2026-08-30: Message delivery/read status for chat_messages
-- Enables read receipts, delivery confirmation, typing indicators

ALTER TABLE chat_messages
    ADD COLUMN IF NOT EXISTS delivered_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS read_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS message_type ENUM('text','system','file','image','transfer','note') DEFAULT 'text',
    ADD COLUMN IF NOT EXISTS metadata JSON DEFAULT NULL; -- extensible: {"typing":true,"reply_to":123,"mentions":[1,2]}

-- Indexes for status queries
CREATE INDEX IF NOT EXISTS idx_chat_messages_status ON chat_messages (session_id, read_at);
CREATE INDEX IF NOT EXISTS idx_chat_messages_delivered ON chat_messages (session_id, delivered_at);