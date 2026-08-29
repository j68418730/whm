-- 2026-08-30: Agent notification preferences and DND
-- Controls how/when agents receive push notifications

CREATE TABLE IF NOT EXISTS agent_notification_prefs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    admin_id INT(11) NOT NULL,
    -- Sound notifications
    sound_new_chat TINYINT(1) NOT NULL DEFAULT 1,
    sound_new_message TINYINT(1) NOT NULL DEFAULT 1,
    sound_mention TINYINT(1) NOT NULL DEFAULT 1,
    sound_transfer TINYINT(1) NOT NULL DEFAULT 1,
    sound_custom VARCHAR(100) DEFAULT 'default', -- sound file name
    -- Visual notifications
    flash_taskbar TINYINT(1) NOT NULL DEFAULT 1,
    show_popup TINYINT(1) NOT NULL DEFAULT 1,
    badge_count TINYINT(1) NOT NULL DEFAULT 1,
    -- Delivery channels
    push_desktop TINYINT(1) NOT NULL DEFAULT 1,
    push_web TINYINT(1) NOT NULL DEFAULT 1,
    push_mobile TINYINT(1) NOT NULL DEFAULT 0, -- future: mobile app
    -- Do Not Disturb
    dnd_enabled TINYINT(1) NOT NULL DEFAULT 0,
    dnd_start TIME DEFAULT NULL, -- e.g. '22:00:00'
    dnd_end TIME DEFAULT NULL, -- e.g. '08:00:00'
    dnd_timezone VARCHAR(50) DEFAULT 'UTC',
    -- Per-event overrides (JSON)
    overrides JSON DEFAULT NULL, -- {"chat": {"sound":false}, "transfer": {"popup":true}}
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foreign key to admins
-- ALTER TABLE agent_notification_prefs ADD CONSTRAINT fk_notif_prefs_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE;