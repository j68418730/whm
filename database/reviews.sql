CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `title` varchar(255) DEFAULT NULL,
  `text` text NOT NULL,
  `service_rating` tinyint(4) DEFAULT NULL,
  `support_rating` tinyint(4) DEFAULT NULL,
  `recommend` tinyint(1) DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `chat_session_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved` (`approved`),
  KEY `rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reviews` (`name`, `email`, `rating`, `title`, `text`, `service_rating`, `support_rating`, `recommend`, `approved`, `created_at`) VALUES
('This is only for Development - John D.', 'john@example.com', 5, 'Best hosting platform ever!', 'I have tried many hosting panels but Planet Hosts is on another level. The combination of WHM and Icecast streaming is exactly what we needed for our radio station. Setup was incredibly easy and the support team helped us get online within hours.', 5, 5, 1, 1, '2026-06-10 10:00:00'),
('This is only for Development - Sarah M.', 'sarah@example.com', 5, 'Incredible radio streaming', 'We run a 24/7 radio station and this panel handles everything perfectly. AutoDJ is fantastic, our DJs love the dedicated portal, and listener analytics give us great insights. Highly recommend to any broadcaster.', 5, 4, 1, 1, '2026-06-11 14:30:00'),
('This is only for Development - Mike R.', 'mike@example.com', 4, 'Great WHM panel with radio', 'Solid hosting panel with all the features you would expect from a major control panel. The radio streaming integration is what sets it apart. Only gave 4 stars because I would love to see more theme options, but overall very happy.', 4, 5, 1, 1, '2026-06-12 09:15:00'),
('This is only for Development - Lisa K.', 'lisa@example.com', 5, 'Perfect for our needs', 'We migrated from cPanel and the transition was smooth. The billing system is comprehensive, live chat works great for our customers, and the Icecast streaming is rock solid. Support team responded to our queries within minutes.', 5, 5, 1, 1, '2026-06-13 16:45:00'),
('This is only for Development - Tom W.', 'tom@example.com', 5, 'Live chat is a game changer', 'The multi-tenant live chat system is amazing. Our support team can handle all customer queries from one place, and the WPF desktop app makes it even easier. Visitors love the quick response times.', 4, 5, 1, 1, '2026-06-14 11:20:00'),
('This is only for Development - Emily C.', 'emily@example.com', 4, 'Great value for money', 'For the features you get, the pricing is very competitive. WHM panel, radio streaming, billing, support tickets — everything included. The AutoDJ feature saves us thousands in DJ costs for overnight slots.', 5, 4, 1, 1, '2026-06-14 20:00:00'),
('This is only for Development - David P.', 'david@example.com', 5, 'Outstanding support team', 'Had a few questions during setup and the support team was incredibly helpful. They even hopped on a remote support session to help configure our Icecast settings. You don\'t get this level of service anywhere else.', 5, 5, 1, 1, '2026-06-15 08:30:00'),
('This is only for Development - Rachel N.', 'rachel@example.com', 5, 'All-in-one solution', 'Finally a control panel that does everything. Domain management, hosting, radio streaming, billing — it is all here. No more juggling between cPanel, WHMCS, and separate streaming platforms. This is the future.', 5, 5, 1, 1, '2026-06-15 15:10:00'),
('This is only for Development - Chris B.', 'chris@example.com', 3, 'Good but needs improvements', 'Solid foundation with great potential. The WHM features are comprehensive and radio integration is unique. Some areas like the file manager and backup system need more work. Trust the team will keep improving.', 3, 4, 1, 1, '2026-06-16 09:00:00'),
('This is only for Development - Alex H.', 'alex@example.com', 5, 'Recommended for radio hosts', 'If you run an online radio station, stop looking and get this panel. Icecast integration is seamless, DJ management is intuitive, and your listeners get a professional experience. The embeddable player widget is perfect for our website.', 5, 5, 1, 1, '2026-06-16 12:00:00');
