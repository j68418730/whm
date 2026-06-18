-- Deactivate VPS and Dedicated (hide from pricing)
UPDATE hosting_packages SET is_active=0 WHERE type IN ('vps','dedicated');

-- ─── WEB HOSTING (10 packages) ───
INSERT INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, `databases`, subdomains, parked_domains, addon_domains, live_chat_enabled, is_active, sort_order) VALUES
('web_hosting', 'Starter', 'Perfect for personal websites and small blogs.', 2.99, 1, 10, 1, 1, 1, 1, 0, 0, 0, 1, 1),
('web_hosting', 'Basic', 'Great for small business websites.', 4.99, 3, 25, 3, 2, 2, 3, 1, 0, 0, 1, 2),
('web_hosting', 'Standard', 'Ideal for growing websites with moderate traffic.', 7.99, 5, 50, 5, 3, 3, 5, 2, 1, 0, 1, 3),
('web_hosting', 'Advanced', 'For established sites needing more resources.', 12.99, 10, 100, 10, 5, 5, 10, 5, 2, 0, 1, 4),
('web_hosting', 'Professional', 'For high-traffic business and e-commerce sites.', 19.99, 20, 200, 20, 10, 10, 20, 10, 5, 0, 1, 5),
('web_hosting', 'Starter Plus', 'Entry-level with live chat support included.', 4.99, 2, 20, 2, 1, 2, 2, 1, 0, 1, 1, 6),
('web_hosting', 'Basic Plus', 'Small business with full support features.', 7.99, 5, 50, 5, 3, 3, 5, 2, 1, 1, 1, 7),
('web_hosting', 'Business', 'Everything a growing business needs.', 14.99, 15, 150, 15, 8, 8, 15, 8, 3, 1, 1, 8),
('web_hosting', 'Business Pro', 'Premium hosting for serious businesses.', 24.99, 30, 300, 30, 15, 15, 30, 15, 8, 1, 1, 9),
('web_hosting', 'Enterprise', 'Maximum performance and dedicated support.', 39.99, 50, 500, 50, 25, 25, 50, 25, 15, 1, 1, 10);

-- ─── WEB HOSTING RESELLER (10 packages) ───
INSERT INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, `databases`, subdomains, parked_domains, addon_domains, live_chat_enabled, is_active, sort_order) VALUES
('web_reseller', 'Reseller Mini', 'Start your hosting business small.', 9.99, 5, 50, 10, 5, 5, 10, 5, 2, 0, 1, 1),
('web_reseller', 'Reseller Basic', 'Solid foundation for new resellers.', 14.99, 10, 100, 20, 10, 10, 20, 10, 5, 0, 1, 2),
('web_reseller', 'Reseller Standard', 'For growing reseller operations.', 24.99, 20, 200, 50, 20, 20, 50, 20, 10, 0, 1, 3),
('web_reseller', 'Reseller Advanced', 'Manage more clients with better resources.', 39.99, 40, 400, 100, 40, 40, 100, 40, 20, 0, 1, 4),
('web_reseller', 'Reseller Pro', 'Professional reseller package.', 59.99, 75, 750, 200, 75, 75, 200, 75, 40, 0, 1, 5),
('web_reseller', 'Reseller Mini Plus', 'Entry reseller with live chat.', 12.99, 8, 75, 15, 8, 8, 15, 8, 3, 1, 1, 6),
('web_reseller', 'Reseller Business', 'Full-featured reseller with support.', 34.99, 30, 300, 75, 30, 30, 75, 30, 15, 1, 1, 7),
('web_reseller', 'Reseller Business Pro', 'Premium reseller with all features.', 54.99, 60, 600, 150, 60, 60, 150, 60, 30, 1, 1, 8),
('web_reseller', 'Reseller Enterprise', 'Enterprise-grade reseller hosting.', 89.99, 100, 1000, 300, 100, 100, 300, 100, 50, 1, 1, 9),
('web_reseller', 'Reseller Ultimate', 'The ultimate reseller package.', 149.99, 200, 2000, 500, 200, 200, 500, 200, 100, 1, 1, 10);

-- ─── ICECAST STREAMING (10 packages) ───
INSERT INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, `databases`, subdomains, parked_domains, addon_domains, listener_limit, bitrate, storage_limit, dj_accounts, live_chat_enabled, is_active, sort_order) VALUES
('icecast', 'Radio Mini', 'Start your radio journey.', 3.99, 1, 10, 1, 1, 1, 1, 0, 0, 10, 64, 500, 1, 0, 1, 1),
('icecast', 'Radio Basic', 'For hobby broadcasters.', 6.99, 3, 25, 2, 2, 1, 2, 1, 0, 25, 96, 1, 2, 0, 1, 2),
('icecast', 'Radio Standard', 'Great for community radio stations.', 11.99, 5, 50, 5, 3, 2, 5, 2, 1, 50, 128, 2, 3, 0, 1, 3),
('icecast', 'Radio Advanced', 'For serious broadcasters.', 19.99, 10, 100, 10, 5, 5, 10, 5, 2, 100, 192, 5, 5, 0, 1, 4),
('icecast', 'Radio Professional', 'Professional radio station package.', 29.99, 20, 200, 20, 10, 10, 20, 10, 5, 250, 256, 10, 10, 0, 1, 5),
('icecast', 'Radio Mini Plus', 'Entry streaming with live chat.', 5.99, 2, 15, 1, 1, 1, 1, 0, 0, 15, 80, 750, 1, 1, 1, 6),
('icecast', 'Radio Standard Plus', 'Community radio with chat support.', 14.99, 8, 75, 8, 5, 3, 8, 3, 1, 75, 160, 3, 5, 1, 1, 7),
('icecast', 'Radio Business', 'Business-grade radio streaming.', 24.99, 15, 150, 15, 8, 8, 15, 8, 3, 150, 224, 8, 8, 1, 1, 8),
('icecast', 'Radio Premium', 'Premium streaming with full support.', 39.99, 30, 300, 30, 15, 15, 30, 15, 8, 500, 320, 15, 15, 1, 1, 9),
('icecast', 'Radio Enterprise', 'Maximum broadcast power.', 59.99, 50, 500, 50, 25, 25, 50, 25, 15, 1000, 320, 25, 25, 1, 1, 10);

-- ─── ICECAST RESELLER (10 packages) ───
INSERT INTO hosting_packages (type, name, description, monthly_price, disk_space, bandwidth, email_accounts, ftp_accounts, `databases`, subdomains, parked_domains, addon_domains, listener_limit, bitrate, storage_limit, dj_accounts, live_chat_enabled, is_active, sort_order) VALUES
('icecast_reseller', 'Radio Reseller Mini', 'Start reselling radio hosting.', 14.99, 5, 50, 10, 5, 5, 10, 5, 2, 25, 64, 1, 2, 0, 1, 1),
('icecast_reseller', 'Radio Reseller Basic', 'Foundational reseller package.', 24.99, 10, 100, 20, 10, 10, 20, 10, 5, 50, 96, 2, 5, 0, 1, 2),
('icecast_reseller', 'Radio Reseller Standard', 'Standard radio reseller.', 39.99, 20, 200, 50, 20, 20, 50, 20, 10, 100, 128, 5, 10, 0, 1, 3),
('icecast_reseller', 'Radio Reseller Advanced', 'Advanced reseller capabilities.', 59.99, 40, 400, 100, 40, 40, 100, 40, 20, 200, 192, 10, 20, 0, 1, 4),
('icecast_reseller', 'Radio Reseller Pro', 'Professional radio reseller.', 89.99, 75, 750, 200, 75, 75, 200, 75, 40, 400, 256, 20, 40, 0, 1, 5),
('icecast_reseller', 'Radio Reseller Mini Plus', 'Mini reseller with live chat.', 19.99, 8, 75, 15, 8, 8, 15, 8, 3, 35, 80, 2, 3, 1, 1, 6),
('icecast_reseller', 'Radio Reseller Standard Plus', 'Standard reseller with support.', 49.99, 30, 300, 75, 30, 30, 75, 30, 15, 150, 160, 8, 15, 1, 1, 7),
('icecast_reseller', 'Radio Reseller Business', 'Business radio reseller.', 79.99, 60, 600, 150, 60, 60, 150, 60, 30, 300, 224, 15, 30, 1, 1, 8),
('icecast_reseller', 'Radio Reseller Premium', 'Premium reseller broadcasting.', 129.99, 100, 1000, 300, 100, 100, 300, 100, 50, 600, 320, 25, 50, 1, 1, 9),
('icecast_reseller', 'Radio Reseller Ultimate', 'Ultimate reseller package.', 199.99, 200, 2000, 500, 200, 200, 500, 200, 100, 1000, 320, 40, 100, 1, 1, 10);
