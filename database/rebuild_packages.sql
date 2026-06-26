-- Rebuild all packages: deactivate old, create 5 per category + testinglive

-- Deactivate all existing packages
UPDATE hosting_packages SET is_active = 0 WHERE 1;

-- ========================
-- testinglive (all features)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, databases, subdomains, parked_domains, addon_domains, listener_limit, bitrate, storage_limit, dj_accounts, live_chat_enabled, shoutcast_enabled, icecast_enabled, chatroom_enabled, chatroom_voice_enabled, game_enabled, dj_panel_enabled, is_active, sort_order)
VALUES ('web_hosting', 'testinglive', 'Full access development package with all features enabled.', 0.00, 50, 500, 50, 25, 25, 50, 25, 15, 500, 320, 50, 100, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- ========================
-- SHOUTcast (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, databases, subdomains, listener_limit, bitrate, storage_limit, dj_accounts, shoutcast_enabled, is_active, sort_order) VALUES
('shoutcast', 'SC Basic', 'Basic SHOUTcast streaming.', 2.99, 1, 10, 0, 0, 0, 0, 10, 64, 1, 1, 1, 1, 1),
('shoutcast', 'SC Standard', 'Standard SHOUTcast radio.', 5.99, 3, 25, 0, 0, 0, 0, 25, 96, 2, 2, 1, 1, 2),
('shoutcast', 'SC Advanced', 'Advanced SHOUTcast broadcasting.', 9.99, 5, 50, 0, 0, 0, 0, 50, 128, 5, 3, 1, 1, 3),
('shoutcast', 'SC Pro', 'Professional SHOUTcast station.', 14.99, 10, 100, 0, 0, 0, 0, 100, 192, 10, 5, 1, 1, 4),
('shoutcast', 'SC Enterprise', 'Enterprise SHOUTcast streaming.', 24.99, 20, 200, 0, 0, 0, 0, 250, 320, 25, 10, 1, 1, 5);

-- ========================
-- SHOUTcast Reseller (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, databases, subdomains, listener_limit, bitrate, storage_limit, dj_accounts, shoutcast_enabled, is_active, sort_order) VALUES
('shoutcast_reseller', 'SC Reseller Mini', 'Entry SC reseller.', 9.99, 5, 50, 5, 2, 2, 5, 25, 64, 2, 3, 1, 1, 1),
('shoutcast_reseller', 'SC Reseller Standard', 'Standard SC reseller.', 19.99, 10, 100, 10, 5, 5, 10, 50, 96, 5, 5, 1, 1, 2),
('shoutcast_reseller', 'SC Reseller Advanced', 'Advanced SC reseller.', 34.99, 20, 200, 20, 10, 10, 20, 100, 128, 10, 10, 1, 1, 3),
('shoutcast_reseller', 'SC Reseller Pro', 'Professional SC reseller.', 59.99, 40, 400, 40, 20, 20, 40, 200, 192, 20, 20, 1, 1, 4),
('shoutcast_reseller', 'SC Reseller Ultimate', 'Ultimate SC reseller.', 99.99, 75, 750, 75, 40, 40, 75, 500, 320, 40, 40, 1, 1, 5);

-- ========================
-- Icecast (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, databases, subdomains, listener_limit, bitrate, storage_limit, dj_accounts, icecast_enabled, is_active, sort_order) VALUES
('icecast', 'Icecast Starter', 'Start your Icecast radio.', 2.99, 1, 10, 0, 0, 0, 0, 10, 64, 1, 1, 1, 1, 1),
('icecast', 'Icecast Standard', 'Standard Icecast streaming.', 5.99, 3, 25, 0, 0, 0, 0, 25, 96, 2, 2, 1, 1, 2),
('icecast', 'Icecast Advanced', 'Advanced Icecast radio.', 9.99, 5, 50, 0, 0, 0, 0, 50, 128, 5, 3, 1, 1, 3),
('icecast', 'Icecast Pro', 'Professional Icecast station.', 14.99, 10, 100, 0, 0, 0, 0, 100, 256, 10, 5, 1, 1, 4),
('icecast', 'Icecast Enterprise', 'Enterprise Icecast streaming.', 24.99, 20, 200, 0, 0, 0, 0, 500, 320, 25, 10, 1, 1, 5);

-- ========================
-- Icecast Reseller (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, databases, subdomains, listener_limit, bitrate, storage_limit, dj_accounts, icecast_enabled, is_active, sort_order) VALUES
('icecast_reseller', 'IC Reseller Mini', 'Entry Icecast reseller.', 9.99, 5, 50, 5, 2, 2, 5, 25, 64, 2, 3, 1, 1, 1),
('icecast_reseller', 'IC Reseller Standard', 'Standard Icecast reseller.', 19.99, 10, 100, 10, 5, 5, 10, 50, 96, 5, 5, 1, 1, 2),
('icecast_reseller', 'IC Reseller Advanced', 'Advanced Icecast reseller.', 34.99, 20, 200, 20, 10, 10, 20, 100, 128, 10, 10, 1, 1, 3),
('icecast_reseller', 'IC Reseller Pro', 'Professional Icecast reseller.', 59.99, 40, 400, 40, 20, 20, 40, 200, 192, 20, 20, 1, 1, 4),
('icecast_reseller', 'IC Reseller Ultimate', 'Ultimate Icecast reseller.', 99.99, 75, 750, 75, 40, 40, 75, 500, 320, 40, 40, 1, 1, 5);

-- ========================
-- Web Hosting (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, databases, subdomains, parked_domains, addon_domains, live_chat_enabled, is_active, sort_order) VALUES
('web_hosting', 'Web Starter', 'Personal website hosting.', 2.99, 1, 10, 1, 1, 1, 1, 0, 0, 0, 1, 1),
('web_hosting', 'Web Standard', 'Small business hosting.', 5.99, 3, 25, 3, 2, 2, 3, 1, 0, 0, 1, 2),
('web_hosting', 'Web Advanced', 'Growing site hosting.', 9.99, 10, 100, 10, 5, 5, 10, 5, 2, 1, 1, 3),
('web_hosting', 'Web Pro', 'Business hosting.', 14.99, 20, 200, 20, 10, 10, 20, 10, 5, 1, 1, 4),
('web_hosting', 'Web Enterprise', 'Enterprise hosting.', 29.99, 50, 500, 50, 25, 25, 50, 25, 15, 1, 1, 5);

-- ========================
-- Web Reseller (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, databases, subdomains, parked_domains, addon_domains, live_chat_enabled, is_active, sort_order) VALUES
('web_reseller', 'Reseller Mini', 'Start reselling hosting.', 9.99, 5, 50, 5, 2, 2, 5, 2, 1, 0, 1, 1),
('web_reseller', 'Reseller Standard', 'Standard reseller.', 19.99, 10, 100, 10, 5, 5, 10, 5, 2, 0, 1, 2),
('web_reseller', 'Reseller Advanced', 'Advanced reseller.', 34.99, 25, 250, 25, 10, 10, 25, 10, 5, 1, 1, 3),
('web_reseller', 'Reseller Pro', 'Professional reseller.', 59.99, 50, 500, 50, 20, 20, 50, 20, 10, 1, 1, 4),
('web_reseller', 'Reseller Ultimate', 'Ultimate reseller.', 99.99, 100, 1000, 100, 40, 40, 100, 40, 20, 1, 1, 5);

-- ========================
-- Chat Room (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, databases, chatroom_enabled, is_active, sort_order) VALUES
('chat_room', 'Chat Mini', 'Basic chat room.', 1.99, 0, 5, 0, 0, 1, 1, 1),
('chat_room', 'Chat Standard', 'Standard chat room.', 3.99, 0, 10, 0, 0, 1, 1, 2),
('chat_room', 'Chat Advanced', 'Advanced chat features.', 5.99, 0, 25, 0, 0, 1, 1, 3),
('chat_room', 'Chat Pro', 'Professional chat room.', 9.99, 0, 50, 0, 0, 1, 1, 4),
('chat_room', 'Chat Enterprise', 'Enterprise chat.', 14.99, 0, 100, 0, 0, 1, 1, 5);

-- ========================
-- Chat Room Voice (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, databases, chatroom_enabled, chatroom_voice_enabled, is_active, sort_order) VALUES
('chat_room_voice', 'Voice Chat Mini', 'Basic voice chat.', 2.99, 0, 10, 0, 0, 1, 1, 1, 1),
('chat_room_voice', 'Voice Chat Standard', 'Standard voice chat.', 4.99, 0, 25, 0, 0, 1, 1, 1, 2),
('chat_room_voice', 'Voice Chat Advanced', 'Advanced voice chat.', 7.99, 0, 50, 0, 0, 1, 1, 1, 3),
('chat_room_voice', 'Voice Chat Pro', 'Professional voice chat.', 12.99, 0, 100, 0, 0, 1, 1, 1, 4),
('chat_room_voice', 'Voice Chat Enterprise', 'Enterprise voice chat.', 19.99, 0, 200, 0, 0, 1, 1, 1, 5);

-- ========================
-- Game Server (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, databases, game_enabled, is_active, sort_order) VALUES
('game_server', 'Game Mini', 'Basic game server.', 4.99, 5, 50, 0, 0, 1, 1, 1),
('game_server', 'Game Standard', 'Standard game server.', 9.99, 10, 100, 0, 0, 1, 1, 2),
('game_server', 'Game Advanced', 'Advanced game hosting.', 14.99, 20, 200, 0, 0, 1, 1, 3),
('game_server', 'Game Pro', 'Professional game server.', 24.99, 40, 400, 0, 0, 1, 1, 4),
('game_server', 'Game Enterprise', 'Enterprise game hosting.', 39.99, 75, 750, 0, 0, 1, 1, 5);

-- ========================
-- DJ Panel (5 packages)
-- ========================
INSERT IGNORE INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, databases, listener_limit, bitrate, dj_accounts, dj_panel_enabled, is_active, sort_order) VALUES
('dj_panel', 'DJ Mini', 'Basic DJ panel.', 1.99, 0, 5, 0, 0, 5, 64, 1, 1, 1, 1),
('dj_panel', 'DJ Standard', 'Standard DJ panel.', 3.99, 0, 10, 0, 0, 10, 96, 2, 1, 1, 2),
('dj_panel', 'DJ Advanced', 'Advanced DJ tools.', 6.99, 0, 25, 0, 0, 25, 128, 3, 1, 1, 3),
('dj_panel', 'DJ Pro', 'Professional DJ panel.', 11.99, 0, 50, 0, 0, 50, 192, 5, 1, 1, 4),
('dj_panel', 'DJ Enterprise', 'Enterprise DJ system.', 19.99, 0, 100, 0, 0, 100, 320, 10, 1, 1, 5);

-- Summary
SELECT type, COUNT(*) as count FROM hosting_packages WHERE is_active=1 GROUP BY type ORDER BY type;
