<?php
/**
 * Planet Hosts Tweak Settings Registry
 * Central configuration engine catalog — ADD/EXPAND/COMPLETE model.
 *
 * Setting schema:
 *  key, cat, label, type (toggle|number|text|select|password|info|link),
 *  default, recommended, desc, sec (security impact), perf (performance impact),
 *  restart (requires restart), applies, risk (normal|high),
 *  handler (db|php_panel:<ini>|apache_conf|fail2ban|detect:<what>|none),
 *  options (select), link (for link type), detect (live-read expression)
 *
 * Sections whose subsystems already exist link to them (INTEGRATE, don't replace).
 */

function ph_tweak(array $s): array
{
    return $s + [
        'default' => '', 'recommended' => '', 'desc' => '', 'sec' => '', 'perf' => '',
        'restart' => false, 'applies' => 'Panel', 'risk' => 'normal',
        'handler' => 'db', 'options' => [], 'link' => '', 'detect' => '',
    ];
}

$S = [];

/* ================= 1. COMPRESSION ================= */
$c = 'compression';
foreach ([
    ['compression.gzip', 'Enable Gzip', 'toggle', '1', '1', 'Gzip output compression for HTML/text responses (mod_deflate).', '', '+CPU minor, big bandwidth savings', false, 'Apache'],
    ['compression.brotli', 'Enable Brotli', 'toggle', '0', '1', 'Brotli compression (mod_brotli) — better ratio than gzip if module is available.', '', '+CPU minor', false, 'Apache'],
    ['compression.method', 'Default compression method', 'select', 'gzip', 'brotli', 'Preferred method when both are available.', '', '', false, 'Apache', ['gzip' => 'Gzip', 'brotli' => 'Brotli']],
    ['compression.level', 'Compression level', 'select', '6', '6', '1-9. Higher = smaller output, more CPU.', '', '+CPU per level', false, 'Apache', ['1' => '1 (fastest)', '4' => '4', '6' => '6 (balanced)', '9' => '9 (smallest)']],
    ['compression.min_size', 'Minimum compression size (bytes)', 'number', '256', '256', 'Responses smaller than this are not compressed.', '', '', false, 'Apache'],
    ['compression.static', 'Static compression', 'toggle', '0', '0', 'Pre-compress files on disk and serve .gz/.br variants.', '', 'Lowest runtime CPU', false, 'Apache'],
    ['compression.dynamic', 'Dynamic compression', 'toggle', '1', '1', 'Compress responses on the fly.', '', '+CPU per request', false, 'Apache'],
    ['compression.cache', 'Cache compressed files', 'toggle', '0', '0', 'Cache compressed variants for reuse.', '', '', false, 'Apache'],
    ['compression.api', 'Compress API responses', 'toggle', '0', '0', 'Compress JSON/API output.', '', '', false, 'Apache'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'sec' => $r[6] ?? '', 'perf' => $r[7] ?? '', 'restart' => $r[8] ?? false, 'applies' => $r[9] ?? 'Panel'] + (isset($r[10]) ? ['options' => $r[10]] : []));
foreach (['html' => 'HTML', 'css' => 'CSS', 'js' => 'JavaScript', 'json' => 'JSON', 'xml' => 'XML', 'svg' => 'SVG', 'fonts' => 'Fonts (woff2 should NOT be re-compressed)'] as $k => $l) {
    $S[] = ph_tweak(['key' => "compression.{$k}", 'cat' => $c, 'label' => "Compress {$l}", 'type' => 'toggle', 'default' => in_array($k, ['html', 'css', 'js', 'json', 'xml', 'svg']) ? '1' : '0', 'recommended' => in_array($k, ['fonts']) ? '0' : '1', 'desc' => "Include {$l} content type in compression.", 'applies' => 'Apache']);
}
$S[] = ph_tweak(['key' => 'compression.exclusions', 'cat' => $c, 'label' => 'Compression exclusions', 'type' => 'text', 'default' => 'image/, video/, audio/', 'recommended' => 'image/, video/, audio/', 'desc' => 'MIME/path prefixes never compressed (comma separated).', 'applies' => 'Apache']);
$S[] = ph_tweak(['key' => 'compression.cpu_max', 'cat' => $c, 'label' => 'Maximum compression CPU usage', 'type' => 'info', 'desc' => 'Monitored by system status. If load exceeds thresholds, consider lowering level.', 'handler' => 'none']);

/* ================= 2. DEVELOPMENT ================= */
$c = 'development';
foreach ([
    ['development.mode', 'Development mode', 'toggle', '0', '0', 'Master development switch for this panel install.', 'High — verbose output can leak data', '', false],
    ['development.production', 'Production mode', 'toggle', '1', '1', 'Hardened production behavior.', '', '', false],
    ['development.debug', 'Debug mode', 'toggle', '0', '0', 'Global debug output.', 'High — never on production', '', false],
    ['development.php_errors_display', 'PHP error display', 'toggle', '0', '0', 'display_errors on screen.', 'High — leaks paths/stack', '', false],
    ['development.php_errors_log', 'PHP error logging', 'toggle', '1', '1', 'Log errors to file instead of display.', '', '', false],
    ['development.api_debug', 'API debug logging', 'toggle', '0', '0', 'Log API requests/responses.', 'Medium — may contain tokens', '+I/O', false],
    ['development.db_query_log', 'Database query logging', 'toggle', '0', '0', 'Log SQL queries (slow log separate).', '', '+I/O heavy', false],
    ['development.hook_debug', 'Hook debugging', 'toggle', '0', '0', 'Log plugin hook execution.', '', '', false],
    ['development.event_debug', 'Event debugging', 'toggle', '0', '0', 'Log internal events.', '', '', false],
    ['development.cron_debug', 'Cron debugging', 'toggle', '0', '0', 'Log cron job output verbosely.', '', '', false],
    ['development.plugin_debug', 'Plugin debugging', 'toggle', '0', '0', 'Verbose plugin loader logs.', '', '', false],
    ['development.theme_debug', 'Theme debugging', 'toggle', '0', '0', 'Verbose theme/view logs.', '', '', false],
    ['development.toolbar', 'Development toolbar', 'toggle', '0', '0', 'On-screen dev toolbar (timings, queries).', 'High if exposed', '', false],
    ['development.dev_api', 'Developer API access', 'toggle', '0', '0', 'Allow developer-scope API keys.', 'High', '', false],
    ['development.test_mode', 'Test mode', 'toggle', '0', '0', 'Sandbox external calls (billing etc).', 'High — no real transactions', '', false],
    ['development.maintenance', 'Maintenance mode', 'toggle', '0', '0', 'Show maintenance page to non-admins.', '', '', false],
    ['development.stack_trace', 'Stack trace display', 'toggle', '0', '0', 'Show stack traces on errors.', 'High — leaks internals', '', false],
    ['development.detailed_errors', 'Detailed error messages', 'toggle', '0', '0', 'Full error details in responses. Never enable on production without admin confirmation.', 'CRITICAL — information disclosure', '', false],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => 'toggle', 'default' => $r[2], 'recommended' => $r[3], 'desc' => $r[4] ?? '', 'sec' => $r[5] ?? '', 'perf' => $r[6] ?? '', 'restart' => $r[7] ?? false, 'risk' => in_array($r[0], ['development.detailed_errors', 'development.debug', 'development.php_errors_display']) ? 'high' : 'normal']);

/* ================= 3. DISPLAY ================= */
$c = 'display';
$S[] = ph_tweak(['key' => 'display.admin_theme', 'cat' => $c, 'label' => 'Default admin theme', 'type' => 'select', 'options' => ['dark' => 'Dark', 'light' => 'Light'], 'default' => 'dark', 'recommended' => 'dark', 'desc' => 'Theme applied to admin accounts by default.']);
$S[] = ph_tweak(['key' => 'display.client_theme', 'cat' => $c, 'label' => 'Default client theme', 'type' => 'select', 'options' => ['dark' => 'Dark', 'light' => 'Light'], 'default' => 'dark', 'recommended' => 'dark', 'desc' => 'Theme applied to client accounts by default.']);
$S[] = ph_tweak(['key' => 'display.reseller_theme', 'cat' => $c, 'label' => 'Default reseller theme', 'type' => 'select', 'options' => ['dark' => 'Dark', 'light' => 'Light'], 'default' => 'dark', 'recommended' => 'dark', 'desc' => 'Theme applied to resellers by default.']);
foreach ([
    ['display.lang', 'Default language', 'select', ['en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'de' => 'German'], 'en'],
    ['display.tz', 'Default timezone', 'text', 'UTC', 'UTC'],
    ['display.date_format', 'Date format', 'text', 'Y-m-d', 'Y-m-d'],
    ['display.time_format', 'Time format', 'text', 'H:i:s', 'H:i:s'],
    ['display.clock', '12/24 hour format', 'select', ['12' => '12-hour', '24' => '24-hour'], '24'],
] as $i => $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2] === 'select' ? 'select' : 'text'] + ($r[2] === 'select' ? ['options' => $r[3]] : []) + ['default' => $r[3 - 1 + 1], 'desc' => 'Panel-wide default.']);
foreach ([
    ['display.show_server_version', 'Show server version', 'toggle', '1', 'Admin only.'],
    ['display.show_php_version', 'Show PHP version', 'toggle', '1', 'Admin only.'],
    ['display.show_os_version', 'Show OS version', 'toggle', '0', 'Admin only.'],
    ['display.show_service_versions', 'Show service versions', 'toggle', '0', 'Admin only.'],
    ['display.show_system_info', 'Show system information', 'toggle', '0', 'Admin only.'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => 'toggle', 'default' => $r[2], 'recommended' => $r[2], 'desc' => $r[3] . ' Hide from clients to reduce information exposure.', 'sec' => 'Low — information disclosure surface']);
$S[] = ph_tweak(['key' => 'display.dashboard_refresh', 'cat' => $c, 'label' => 'Dashboard refresh interval (sec)', 'type' => 'number', 'default' => '60', 'recommended' => '60', 'desc' => 'How often dashboards auto-refresh.', 'perf' => '+polling load when low']);
foreach ([
    ['branding.logo', 'Planet Hosts logo', 'text', '', 'Logo URL or /uploads path.'],
    ['branding.favicon', 'Favicon', 'text', '', 'Favicon URL/path.'],
    ['branding.login_bg', 'Login background', 'text', '', 'Login page background URL.'],
    ['branding.footer', 'Footer branding', 'text', '', 'Footer text — e.g. company name.'],
    ['branding.custom_css', 'Custom CSS', 'textarea', '', 'Injected into panel pages.'],
    ['branding.custom_js', 'Custom JavaScript', 'textarea', '', 'Injected into panel pages. Review carefully — can break the panel.'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2] === 'textarea' ? 'textarea' : 'text', 'default' => '', 'desc' => $r[3], 'applies' => 'Panel', 'risk' => $r[0] === 'branding.custom_js' ? 'high' : 'normal']);

/* ================= 4. DOMAINS ================= */
$c = 'domains';
foreach ([
    ['domains.addon', 'Allow addon domains', 'toggle', '1', '1'],
    ['domains.aliases', 'Allow aliases', 'toggle', '1', '1'],
    ['domains.parked', 'Allow parked domains', 'toggle', '1', '1'],
    ['domains.subdomains', 'Allow subdomains', 'toggle', '1', '1'],
    ['domains.max_subdomains', 'Maximum subdomains', 'number', '50', '50'],
    ['domains.service_subdomains', 'Service subdomains', 'toggle', '1', '1'],
    ['domains.autocreate_service', 'Auto-created service subdomains', 'toggle', '1', '1'],
    ['domains.autodiscover', 'Autodiscover', 'toggle', '1', '1'],
    ['domains.autoconfig', 'Autoconfig', 'toggle', '1', '1'],
    ['domains.validation', 'Domain validation', 'toggle', '1', '1'],
    ['domains.verification', 'Domain verification (DNS ownership)', 'toggle', '0', '0'],
    ['domains.auto_dns', 'Automatic DNS records', 'toggle', '1', '1'],
    ['domains.auto_ssl', 'Automatic SSL', 'toggle', '1', '1'],
    ['domains.autossl_renew', 'AutoSSL renewal', 'toggle', '1', '1'],
    ['domains.suspension_behavior', 'Domain suspension behavior', 'select', ['park' => 'Park (show suspend page)', 'unassign' => 'Unassign from account'], 'park', 'park'],
    ['domains.expiry_warn_days', 'Domain expiration warnings (days before)', 'number', '30', '30'],
    ['domains.www_behavior', 'WWW behavior', 'select', ['both' => 'Serve both', 'redirect' => 'Redirect to canonical'], 'both', 'both'],
    ['domains.docroot', 'Default domain document root', 'text', '/home/{user}/public_html', '/home/{user}/public_html'],
    ['domains.creation_restrict', 'Domain creation restrictions', 'text', '', 'Comma list of reserved/blocked domain strings.'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['desc' => 'Domain provisioning behavior.', 'applies' => 'Panel / DNS']);

/* ================= 5. LOGGING ================= */
$c = 'logging';
$S[] = ph_tweak(['key' => 'logging.audit', 'cat' => $c, 'label' => 'Enable audit logging', 'type' => 'toggle', 'default' => '1', 'recommended' => '1', 'desc' => 'Record administrative configuration changes (feeds Configuration History).']);
foreach ([
    ['logging.admin_logins', 'Admin login logging', '1'],
    ['logging.client_logins', 'Client login logging', '1'],
    ['logging.reseller_logins', 'Reseller login logging', '1'],
    ['logging.api', 'API logging', '1'],
    ['logging.database', 'Database logging', '0'],
    ['logging.services', 'Service logging', '1'],
    ['logging.security', 'Security logging', '1'],
    ['logging.firewall', 'Firewall logging', '1'],
    ['logging.dns', 'DNS logging', '0'],
    ['logging.mail', 'Mail logging', '1'],
    ['logging.billing', 'Billing logging', '1'],
    ['logging.provisioning', 'Provisioning logging', '1'],
    ['logging.radio', 'Radio logging', '1'],
    ['logging.game_servers', 'Game server logging', '1'],
    ['logging.node_agents', 'Node agent logging', '1'],
    ['logging.failed_logins', 'Failed login logging', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => 'toggle', 'default' => $r[2], 'recommended' => $r[2], 'desc' => 'Write this event stream to the audit/log store.']);
foreach ([
    ['logging.retention_days', 'Log retention (days)', 'number', '90', '90', 'Older entries are pruned.'],
    ['logging.max_size', 'Maximum log size (MB)', 'number', '500', '500', 'Watchdog truncation threshold reference.'],
    ['logging.rotation', 'Automatic log rotation', 'toggle', '1', '1'],
    ['logging.compression', 'Log compression', 'toggle', '1', '1'],
    ['logging.archive_dir', 'Log archive location', 'text', '/var/log/planethosts', '/var/log/planethosts'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '']);

/* ================= 6. MAIL ================= */
$c = 'mail';
foreach ([
    ['mail.enabled', 'Enable email', 'toggle', '1', '1'],
    ['mail.smtp_enabled', 'SMTP enabled', 'toggle', '1', '1'],
    ['mail.smtp_auth', 'SMTP authentication', 'toggle', '1', '1'],
    ['mail.smtp_port', 'SMTP port', 'number', '587', '587'],
    ['mail.imap', 'IMAP enabled', 'toggle', '1', '1'],
    ['mail.pop3', 'POP3 enabled', 'toggle', '1', '1'],
    ['mail.mailbox_quota_mb', 'Mailbox quota (MB)', 'number', '1024', '1024'],
    ['mail.mailbox_max_mb', 'Maximum mailbox size (MB)', 'number', '2048', '2048'],
    ['mail.mailboxes_per_account', 'Maximum mailboxes per account', 'number', '10', '10'],
    ['mail.outbound_max', 'Maximum outbound emails (per hour per account)', 'number', '200', '200'],
    ['mail.hourly_limit', 'Per-hour outbound limit (server)', 'number', '500', '500'],
    ['mail.daily_limit', 'Per-day outbound limit (server)', 'number', '5000', '5000'],
    ['mail.conn_limit', 'SMTP connection limit', 'number', '20', '20'],
    ['mail.queue_limit', 'Mail queue limit', 'number', '1000', '1000'],
    ['mail.spam_filter', 'Spam filtering', 'toggle', '1', '1'],
    ['mail.spam_score', 'Anti-spam scoring threshold', 'number', '5', '5'],
    ['mail.greylist', 'Greylisting', 'toggle', '0', '0'],
    ['mail.dkim', 'DKIM', 'info', '', 'Managed by OpenDKIM — currently ACTIVE and signing (see SMTP settings page).', 'none'],
    ['mail.spf', 'SPF', 'info', '', 'Published in DNS: v=spf1 a mx ip4:15.204.114.226 ~all', 'none'],
    ['mail.dmarc', 'DMARC', 'info', '', 'Published in DNS: p=none with reports to admin@', 'none'],
    ['mail.forwarding', 'Mail forwarding', 'toggle', '1', '1'],
    ['mail.catchall', 'Catch-all email', 'toggle', '0', '0'],
    ['mail.autoresponder', 'Auto responders', 'toggle', '1', '1'],
    ['mail.aliases', 'Email aliases', 'toggle', '1', '1'],
    ['mail.lists', 'Mailing lists', 'toggle', '0', '0'],
    ['mail.suspended_behavior', 'Suspended account mail behavior', 'select', ['reject' => 'Reject inbound', 'queue' => 'Queue briefly', 'bounce' => 'Bounce'], 'reject', 'reject'],
    ['mail.outbound_suspension', 'Outbound mail suspension', 'toggle', '0', '0', 'Block outbound mail for suspended accounts.'],
    ['mail.abuse_protection', 'Email abuse protection', 'toggle', '1', '1', 'Rate limits + Fail2Ban postfix/dovecot jails.'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? 'Mail subsystem setting.', 'applies' => 'Postfix / Dovecot']);

/* ================= 7. NOTIFICATIONS ================= */
$c = 'notifications';
foreach ([
    ['notify.admin', 'Administrator notifications', '1'],
    ['notify.security', 'Security notifications', '1'],
    ['notify.backup', 'Backup notifications', '1'],
    ['notify.disk', 'Disk space notifications', '1'],
    ['notify.cpu', 'CPU notifications', '1'],
    ['notify.ram', 'RAM notifications', '1'],
    ['notify.service_down', 'Service-down notifications', '1'],
    ['notify.ssl_expiry', 'SSL expiration notifications', '1'],
    ['notify.domain_expiry', 'Domain expiration notifications', '1'],
    ['notify.failed_login', 'Failed login notifications', '1'],
    ['notify.billing', 'Billing notifications', '1'],
    ['notify.provisioning', 'Provisioning notifications', '1'],
    ['notify.radio', 'Radio notifications', '0'],
    ['notify.game_servers', 'Game server notifications', '0'],
    ['notify.node_offline', 'Node offline notifications', '1'],
    ['notify.license', 'License notifications', '1'],
    ['notify.updates', 'Update notifications', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => 'toggle', 'default' => $r[2], 'recommended' => $r[2], 'desc' => 'Include this event class in notification emails.']);
foreach ([
    ['notify.cpu_warn', 'CPU warning %', 'number', '80'],
    ['notify.cpu_crit', 'CPU critical %', 'number', '95'],
    ['notify.ram_warn', 'RAM warning %', 'number', '80'],
    ['notify.ram_crit', 'RAM critical %', 'number', '95'],
    ['notify.disk_warn', 'Disk warning %', 'number', '80'],
    ['notify.disk_crit', 'Disk critical %', 'number', '90'],
    ['notify.load_warn', 'Load warning', 'number', '4'],
    ['notify.load_crit', 'Load critical', 'number', '8'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => 'number', 'default' => $r[3], 'recommended' => $r[3], 'desc' => 'Threshold that triggers warning/critical notifications.', 'applies' => 'Monitoring']);

/* ================= 8. PACKAGES ================= */
$c = 'packages';
foreach ([
    ['pkg.disk_mb', 'Default disk quota (MB)', '1024'],
    ['pkg.bandwidth_mb', 'Default bandwidth (MB)', '10240'],
    ['pkg.email_accounts', 'Default email accounts', '10'],
    ['pkg.databases', 'Default databases', '5'],
    ['pkg.ftp_accounts', 'Default FTP accounts', '5'],
    ['pkg.domains', 'Default domains', '5'],
    ['pkg.subdomains', 'Default subdomains', '10'],
    ['pkg.aliases', 'Default aliases', '5'],
    ['pkg.ssl', 'Default SSL', 'toggle', '1'],
    ['pkg.cpu_limit', 'Default CPU limit %', '100'],
    ['pkg.ram_limit_mb', 'Default RAM limit (MB)', '512'],
    ['pkg.process_limit', 'Default process limit', '50'],
    ['pkg.inode_limit', 'Default inode limit', '100000'],
    ['pkg.cron_jobs', 'Default cron jobs', '5'],
    ['pkg.backups', 'Default backups', 'toggle', '1'],
    ['pkg.db_size_mb', 'Default database size (MB)', '512'],
    ['pkg.mailbox_size_mb', 'Default mailbox size (MB)', '1024'],
    ['pkg.php_version', 'Default PHP version', 'text', '8.2'],
    ['pkg.features', 'Default website features', 'text', 'ssl,ftp,databases,email', 'Comma list of enabled feature flags for new packages.'],
] as $r) {
    $isToggle = ($r[2] ?? '') === 'toggle';
    $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $isToggle ? 'toggle' : ($r[0] === 'pkg.features' ? 'text' : ($r[0] === 'pkg.php_version' ? 'text' : 'number'))] + ($isToggle ? ['default' => $r[3], 'recommended' => $r[3]] : ['default' => $r[2], 'recommended' => $r[2]]) + ['desc' => $r[5] ?? 'Default applied when creating new hosting packages (integrates with the existing package system).', 'applies' => 'Packages']);
}

/* ================= 9. PHP ================= */
$c = 'php';
$S[] = ph_tweak(['key' => 'php.panel.version', 'cat' => $c, 'label' => 'PANEL PHP version', 'type' => 'info', 'desc' => 'The PHP build serving this panel (read-only; set by server provisioning).', 'handler' => 'detect:php_version']);
foreach ([
    ['php.panel.memory_limit', 'PANEL memory_limit', 'text', '256M', '256M', 'Max memory per panel PHP request.'],
    ['php.panel.upload_max_filesize', 'PANEL upload_max_filesize', 'text', '128M', '256M', 'Max single upload size (panel vhost).'],
    ['php.panel.post_max_size', 'PANEL post_max_size', 'text', '128M', '256M', 'Max POST body (must be >= upload size).'],
    ['php.panel.max_execution_time', 'PANEL max_execution_time', 'number', '300', '300'],
    ['php.panel.max_input_time', 'PANEL max_input_time', 'number', '300', '300'],
    ['php.panel.max_input_vars', 'PANEL max_input_vars', 'number', '3000', '5000'],
    ['php.panel.max_file_uploads', 'PANEL max_file_uploads', 'number', '20', '20'],
    ['php.panel.session_lifetime', 'PANEL session lifetime (sec)', 'number', '1440', '1440'],
    ['php.panel.opcache', 'PANEL OPcache', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2] === 'toggle' ? 'toggle' : ($r[2] === 'number' ? 'number' : 'text'), 'default' => $r[3], 'recommended' => $r[4], 'desc' => ($r[5] ?? ''), 'applies' => 'Panel PHP (apache vhost php_value)', 'risk' => 'high', 'restart' => true, 'sec' => 'High risk — wrong values can break the panel']);
foreach ([
    ['php.customer.memory_limit', 'WEBSITE memory_limit', 'text', '128M', '256M'],
    ['php.customer.upload_max_filesize', 'WEBSITE upload_max_filesize', 'text', '64M', '128M'],
    ['php.customer.post_max_size', 'WEBSITE post_max_size', 'text', '64M', '128M'],
    ['php.customer.max_execution_time', 'WEBSITE max_execution_time', 'number', '60', '120'],
    ['php.customer.disable_functions', 'WEBSITE disabled PHP functions', 'text', 'exec,passthru,shell_exec,system,proc_open', 'exec,passthru,shell_exec,system,proc_open'],
    ['php.customer.open_basedir', 'WEBSITE open_basedir', 'toggle', '1', '1'],
    ['php.customer.timezone', 'WEBSITE PHP timezone', 'text', 'UTC', 'UTC'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2] === 'toggle' ? 'toggle' : ($r[2] === 'number' ? 'number' : 'text'), 'default' => $r[3], 'recommended' => $r[4], 'desc' => 'Applies to customer website PHP (per-vhost defaults).', 'applies' => 'Customer PHP', 'risk' => 'high', 'restart' => true]);
$S[] = ph_tweak(['key' => 'php.customer.cli_version', 'cat' => $c, 'label' => 'PHP CLI version (customer cron)', 'type' => 'info', 'desc' => 'Read from /usr/bin/php on this server.', 'handler' => 'detect:php_cli_version']);
$S[] = ph_tweak(['key' => 'php.fpm', 'cat' => $c, 'label' => 'PHP-FPM', 'type' => 'info', 'desc' => 'This server runs mod_php (no PHP-FPM installed) — nginx proxy is not used for the panel.', 'handler' => 'detect:php_fpm']);
$S[] = ph_tweak(['key' => 'php.error_log', 'cat' => $c, 'label' => 'PHP error logging (panel)', 'type' => 'toggle', 'default' => '1', 'recommended' => '1', 'desc' => 'Log PHP errors for the panel PHP build.']);
$S[] = ph_tweak(['key' => 'php.extensions', 'cat' => $c, 'label' => 'PHP extensions', 'type' => 'info', 'desc' => 'Installed extensions (read-only).', 'handler' => 'detect:php_extensions']);
$S[] = ph_tweak(['key' => 'php.loaders', 'cat' => $c, 'label' => 'PHP loaders', 'type' => 'info', 'desc' => 'ionCube/Zend loaders if installed.', 'handler' => 'detect:php_loaders']);

/* ================= 10. REDIRECTION ================= */
$c = 'redirection';
foreach ([
    ['redirect.force_https', 'Force HTTPS (panel)', 'toggle', '1', '1', 'Redirect panel HTTP → HTTPS.', '', '', true],
    ['redirect.www', 'WWW redirect', 'select', ['none' => 'No redirect', 'to_www' => 'To www', 'to_nonwww' => 'To non-www'], 'none', 'none'],
    ['redirect.service', 'Service redirects', 'toggle', '1', '1', 'cPanel-style service ports (2082/2086/2087/2096) active.'],
    ['redirect.panel', 'Panel redirects', 'text', '/:2087 → /admin/login', 'IP:2087 → /admin/login', 'Custom panel redirect rules.'],
    ['redirect.default_page', 'Default redirect page', 'text', '/', '/'],
    ['redirect.loop_protect', 'Redirect loop protection', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'applies' => 'Apache', 'risk' => $r[0] === 'redirect.force_https' ? 'high' : 'normal', 'restart' => $r[7] ?? false]);

/* ================= 11. SECURITY ================= */
$c = 'security';
foreach ([
    ['sec.require_https', 'Require HTTPS', 'toggle', '1', '1', '', '', true],
    ['sec.secure_cookies', 'Secure cookies', 'toggle', '1', '1'],
    ['sec.cookie_httponly', 'HTTP-only cookies', 'toggle', '1', '1'],
    ['sec.cookie_samesite', 'SameSite cookies', 'select', ['Lax' => 'Lax', 'Strict' => 'Strict', 'None' => 'None'], 'Lax', 'Strict'],
    ['sec.csrf', 'CSRF protection', 'toggle', '1', '1'],
    ['sec.session_timeout', 'Session timeout (minutes)', 'number', '30', '30'],
    ['sec.login_attempts', 'Login attempt limit', 'number', '5', '5'],
    ['sec.login_lockout', 'Login lockout duration (minutes)', 'number', '15', '30'],
    ['sec.password_min', 'Password minimum length', 'number', '8', '10'],
    ['sec.password_complexity', 'Password complexity', 'toggle', '1', '1', 'Require upper+lower+digit.'],
    ['sec.password_expiry_days', 'Password expiration (days, 0=never)', 'number', '0', '0'],
    ['sec.twofactor_admin', 'Admin 2FA requirement', 'toggle', '0', '0'],
    ['sec.twofactor_reseller', 'Reseller 2FA requirement', 'toggle', '0', '0'],
    ['sec.twofactor_client', 'Client 2FA', 'toggle', '0', '0'],
    ['sec.api_auth', 'API authentication', 'toggle', '1', '1'],
    ['sec.api_rate_limit', 'API rate limiting (req/min)', 'number', '60', '60'],
    ['sec.login_rate_limit', 'Login rate limiting', 'toggle', '1', '1'],
    ['sec.headers', 'Security headers', 'toggle', '1', '1', 'Send standard security headers from panel.'],
    ['sec.hsts', 'HSTS', 'toggle', '1', '1', '', '', true],
    ['sec.csp', 'CSP', 'toggle', '0', '0', 'Content-Security-Policy — may break embedded widgets if too strict.'],
    ['sec.xframe', 'X-Frame-Options', 'select', ['SAMEORIGIN' => 'SAMEORIGIN', 'DENY' => 'DENY', 'off' => 'Off'], 'SAMEORIGIN', 'SAMEORIGIN'],
    ['sec.xcontent', 'X-Content-Type-Options', 'toggle', '1', '1'],
    ['sec.referrer_policy', 'Referrer-Policy', 'select', ['strict-origin-when-cross-origin' => 'strict-origin-when-cross-origin', 'no-referrer' => 'no-referrer', 'off' => 'Off'], 'strict-origin-when-cross-origin', 'strict-origin-when-cross-origin'],
    ['sec.permissions_policy', 'Permissions-Policy', 'text', 'camera=(), microphone=(), geolocation=()', 'camera=(), microphone=(), geolocation=()'],
    ['sec.disable_php_functions', 'Disable dangerous PHP functions', 'text', 'exec,passthru,shell_exec,system,proc_open', 'exec,passthru,shell_exec,system,proc_open', '', '', true],
    ['sec.cgi', 'CGI security', 'toggle', '0', '0'],
    ['sec.app_exec_restrict', 'Application execution restrictions', 'toggle', '0', '0'],
    ['sec.file_perm_check', 'File permission checking', 'toggle', '1', '1', 'Weekly scan for world-writable sensitive files.'],
    ['sec.symlink_protect', 'Symlink protection', 'toggle', '1', '1'],
    ['sec.open_dir_protect', 'Open directory protection', 'toggle', '1', '1', 'Disable directory listing where not needed.'],
    ['sec.rfi_protect', 'Remote file inclusion protection', 'toggle', '1', '1', 'allow_url_include=Off.'],
    ['sec.upload_security', 'Upload security', 'toggle', '1', '1', 'Content-type validation on uploads (already enforced for music).'],
    ['sec.exec_upload_protect', 'Executable upload protection', 'toggle', '1', '1', 'Reject uploads whose content is PHP/script.'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'sec' => $r[6] ?? '', 'restart' => $r[7] ?? false, 'risk' => in_array($r[0], ['sec.disable_php_functions', 'sec.require_https']) ? 'high' : 'normal', 'applies' => 'Panel']);

/* ================= 12. SOFTWARE ================= */
$c = 'software';
foreach ([
    ['sw.auto_updates', 'Automatic updates', 'toggle', '0', '0'],
    ['sw.security_updates', 'Security updates (unattended)', 'toggle', '1', '1'],
    ['sw.os_updates', 'OS updates', 'toggle', '0', '0'],
    ['sw.panel_updates', 'Panel updates', 'toggle', '0', '0'],
    ['sw.php_updates', 'PHP updates', 'toggle', '0', '0'],
    ['sw.pkg_updates', 'Package updates', 'toggle', '0', '0'],
    ['sw.dependency_updates', 'Dependency updates', 'toggle', '0', '0'],
    ['sw.maintenance_window', 'Update maintenance window', 'text', '03:00-05:00', '03:00-05:00'],
    ['sw.integrity_check', 'Package integrity checking (debsums/aide)', 'toggle', '1', '1'],
    ['sw.repo_status', 'Repository status', 'info', '', 'Live apt repository health.', 'none'],
    ['sw.inventory', 'Installed software inventory', 'info', '', 'Live package list.', 'none'],
    ['sw.version_check', 'Software version checking', 'toggle', '1', '1'],
    ['sw.phpmyadmin_behavior', 'phpMyAdmin behavior', 'select', ['public' => 'Public URL (/phpmyadmin)', 'restricted' => 'Restricted (deny by default, allow by IP)'], 'public', 'public'],
    ['sw.dormant_services', 'Dormant service detection', 'toggle', '1', '1', 'Flag services running with no traffic/usage.'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'applies' => 'apt / unattended-upgrades']);

/* ================= 13. SQL / DATABASE ================= */
$c = 'sql';
foreach ([
    ['sql.enabled', 'MySQL/MariaDB enabled', 'info', '', 'Live service status.', 'none'],
    ['sql.port', 'Database port', 'number', '3306', '3306'],
    ['sql.max_connections', 'Maximum connections', 'number', '151', '300', '', '', true],
    ['sql.conn_timeout', 'Connection timeout (sec)', 'number', '10', '10'],
    ['sql.query_timeout', 'Query timeout (sec)', 'number', '0', '60'],
    ['sql.max_packet', 'Maximum packet size', 'text', '16M', '64M', '', '', true],
    ['sql.slow_query_log', 'Slow query logging', 'toggle', '0', '1', '', '', true],
    ['sql.slow_query_time', 'Slow query threshold (sec)', 'number', '2', '2'],
    ['sql.general_log', 'General query logging', 'toggle', '0', '0', 'VERY verbose — never on production load.', 'High', '+I/O heavy', true],
    ['sql.usage_calc', 'Database usage calculation', 'toggle', '1', '1', 'Include DB size in account disk usage.'],
    ['sql.remote_access', 'Remote database access', 'toggle', '0', '0', 'Allow external hosts to connect to MySQL.', 'CRITICAL — public DB exposure', '', true],
    ['sql.creation_limits', 'Database creation limits per account', 'number', '5', '5'],
    ['sql.size_limits_mb', 'Database size limits (MB, 0=unlimited)', 'number', '0', '0'],
    ['sql.user_conn_limits', 'User connection limits', 'number', '30', '30'],
    ['sql.backup_behavior', 'Database backup behavior', 'select', ['dump' => 'Full dump per deploy', 'snapshot' => 'Snapshot'], 'dump', 'dump'],
    ['sql.legacy_compat', 'Legacy SQL compatibility mode', 'toggle', '0', '0'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'sec' => $r[6] ?? '', 'perf' => $r[7] ?? '', 'restart' => $r[8] ?? false, 'risk' => in_array($r[0], ['sql.remote_access', 'sql.general_log']) ? 'high' : 'normal', 'applies' => 'MariaDB']);

/* ================= 14. STATS AND LOGS ================= */
$c = 'stats';
foreach ([
    ['stats.enabled', 'Enable statistics', 'toggle', '1', '1'],
    ['stats.interval', 'Statistics processing interval', 'select', ['5min' => 'Every 5 min', 'hourly' => 'Hourly', 'daily' => 'Daily'], '5min', '5min'],
    ['stats.bandwidth', 'Bandwidth statistics', 'toggle', '1', '1'],
    ['stats.disk', 'Disk statistics', 'toggle', '1', '1'],
    ['stats.cpu', 'CPU statistics', 'toggle', '1', '1'],
    ['stats.ram', 'RAM statistics', 'toggle', '1', '1'],
    ['stats.websites', 'Website statistics', 'toggle', '1', '1'],
    ['stats.databases', 'Database statistics', 'toggle', '1', '1'],
    ['stats.accounts', 'Account statistics', 'toggle', '1', '1'],
    ['stats.resellers', 'Reseller statistics', 'toggle', '1', '1'],
    ['stats.radio', 'Radio statistics', 'toggle', '1', '1'],
    ['stats.game_servers', 'Game server statistics', 'toggle', '1', '1'],
    ['stats.retention_days', 'Statistics retention (days)', 'number', '90', '90'],
    ['stats.archive', 'Log archive', 'text', '/var/log/planethosts', '/var/log/planethosts'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => 'Statistics collection behavior.']);

/* ================= 15. STATS PROGRAMS ================= */
$c = 'statsprograms';
$S[] = ph_tweak(['key' => 'statsprog.detected', 'cat' => $c, 'label' => 'Installed statistics programs', 'type' => 'info', 'desc' => 'Detected on this server (only listed software can be enabled).', 'handler' => 'detect:stats_programs']);
foreach (['awstats' => 'AWStats', 'webalizer' => 'Webalizer', 'analog' => 'Analog'] as $k => $l) {
    $S[] = ph_tweak(['key' => "statsprog.{$k}", 'cat' => $c, 'label' => $l, 'type' => 'toggle', 'default' => '0', 'recommended' => '0', 'desc' => "Enable {$l} processing (only if installed).", 'handler' => "detect:statsprog:{$k}"]);
}
foreach ([
    ['statsprog.schedule', 'Processing schedule', 'select', ['hourly' => 'Hourly', 'daily' => 'Daily', 'weekly' => 'Weekly'], 'daily', 'daily'],
    ['statsprog.retention', 'Retention (days)', 'number', '60', '60'],
    ['statsprog.storage', 'Statistics storage location', 'text', '/var/lib/planet-hosts/stats', '/var/lib/planet-hosts/stats'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => '']);

/* ================= 16. STATUS ================= */
$c = 'status';
foreach ([
    ['status.server_monitor', 'Server monitoring', 'toggle', '1', '1'],
    ['status.service_monitor', 'Service monitoring', 'toggle', '1', '1'],
    ['status.cpu', 'CPU monitoring', 'toggle', '1', '1'],
    ['status.ram', 'RAM monitoring', 'toggle', '1', '1'],
    ['status.disk', 'Disk monitoring', 'toggle', '1', '1'],
    ['status.load', 'Load monitoring', 'toggle', '1', '1'],
    ['status.network', 'Network monitoring', 'toggle', '1', '1'],
    ['status.processes', 'Process monitoring', 'toggle', '1', '1'],
    ['status.database', 'Database monitoring', 'toggle', '1', '1'],
    ['status.web_server', 'Web server monitoring', 'toggle', '1', '1'],
    ['status.dns', 'DNS monitoring', 'toggle', '1', '1'],
    ['status.mail', 'Mail monitoring', 'toggle', '1', '1'],
    ['status.radio', 'Radio monitoring', 'toggle', '1', '1'],
    ['status.game_servers', 'Game server monitoring', 'toggle', '1', '1'],
    ['status.nodes', 'Node monitoring', 'toggle', '1', '1'],
    ['status.cpu_warn', 'CPU warning %', 'number', '80', '80'],
    ['status.cpu_crit', 'CPU critical %', 'number', '95', '95'],
    ['status.ram_warn', 'RAM warning %', 'number', '80', '80'],
    ['status.ram_crit', 'RAM critical %', 'number', '95', '95'],
    ['status.disk_warn', 'Disk warning %', 'number', '80', '80'],
    ['status.disk_crit', 'Disk critical %', 'number', '90', '90'],
    ['status.load_warn', 'Load warning', 'number', '4', '4'],
    ['status.load_crit', 'Load critical', 'number', '8', '8'],
    ['status.net_warn_mbps', 'Network warning (Mbps)', 'number', '100', '100'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => 'Monitoring switch / threshold (feeds system status page + alerts).', 'applies' => 'Monitoring']);

/* ================= 17. SUPPORT ================= */
$c = 'support';
foreach ([
    ['support.email', 'Support email', 'text', 'admin@planet-hosts.com', 'admin@planet-hosts.com'],
    ['support.ticket_notify', 'Ticket notifications', 'toggle', '1', '1'],
    ['support.client_notify', 'Client notifications', 'toggle', '1', '1'],
    ['support.admin_notify', 'Admin notifications', 'toggle', '1', '1'],
    ['support.kb', 'Knowledge base', 'toggle', '1', '1'],
    ['support.docs_links', 'Documentation links', 'text', '', 'URLs shown in help widget.'],
    ['support.error_reporting', 'Error reporting', 'toggle', '1', '1'],
    ['support.diagnostic_mode', 'Diagnostic mode', 'toggle', '0', '0'],
    ['support.remote', 'Remote support', 'info', '', 'Managed in the Remote Support module (existing subsystem).', 'none'],
    ['support.remote_otp', 'Remote support OTP', 'info', '', 'Managed in the Remote Support module.', 'none'],
    ['support.logging', 'Support logging', 'toggle', '1', '1'],
    ['support.analysis_retention', 'Update analysis retention (days)', 'number', '30', '30'],
    ['support.maintenance_mode', 'Support maintenance mode', 'toggle', '0', '0'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? 'Support subsystem setting (integrates with existing Planet Hosts Support).']);

/* ================= 18. SYSTEM ================= */
$c = 'system';
foreach ([
    ['sys.timezone', 'Server timezone', 'text', 'UTC', 'UTC', 'Applies to cron + services.'],
    ['sys.locale', 'Server locale', 'text', 'en_US.UTF-8', 'en_US.UTF-8'],
    ['sys.tmp_dir', 'Temporary directory', 'text', '/tmp', '/tmp'],
    ['sys.backup_dir', 'Backup directory', 'text', '/var/backups/planet-hosts', '/var/backups/planet-hosts'],
    ['sys.default_shell', 'Default shell', 'text', '/bin/bash', '/bin/bash', 'High risk if wrong — can break logins.'],
    ['sys.cron_settings', 'Cron settings', 'text', 'systemd cron.service', 'systemd cron.service'],
    ['sys.max_processes', 'Maximum processes (per user)', 'number', '100', '100'],
    ['sys.max_open_files', 'Maximum open files', 'number', '65535', '65535'],
    ['sys.maintenance', 'System maintenance mode', 'toggle', '0', '0', 'Blocks non-admin panel access.'],
    ['sys.shutdown_protection', 'System shutdown protection', 'toggle', '1', '1', 'Require confirmation for shutdown actions.'],
    ['sys.reboot_protection', 'Reboot protection', 'toggle', '1', '1'],
    ['sys.service_recovery', 'Automatic service recovery', 'toggle', '1', '1', 'Restart failed radio/stream services automatically.'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'risk' => $r[0] === 'sys.default_shell' ? 'high' : 'normal', 'applies' => 'Server']);

/* ================= 19. WEB SERVER ================= */
$c = 'webserver';
$S[] = ph_tweak(['key' => 'ws.detected', 'cat' => $c, 'label' => 'Detected web server', 'type' => 'info', 'desc' => 'Live detection (Apache + nginx presence).', 'handler' => 'detect:web_servers']);
foreach ([
    ['ws.http_port', 'HTTP port', 'number', '80', '80', '', '', true],
    ['ws.https_port', 'HTTPS port', 'number', '443', '443', '', '', true],
    ['ws.keepalive', 'KeepAlive', 'toggle', '1', '1', '', '', true],
    ['ws.max_workers', 'Worker limits (MaxRequestWorkers)', 'number', '150', '300', '', '', true],
    ['ws.max_conn', 'Connection limits', 'number', '256', '512', '', '', true],
    ['ws.req_timeout', 'Request timeout (sec)', 'number', '60', '60', '', '', true],
    ['ws.client_timeout', 'Client timeout (sec)', 'number', '300', '300', '', '', true],
    ['ws.upload_limit', 'Upload limit (LimitRequestBody MB)', 'number', '128', '256', '', '', true],
    ['ws.browser_cache', 'Browser caching (expires headers)', 'toggle', '1', '1', '', '', true],
    ['ws.http2', 'HTTP/2', 'toggle', '1', '1', '', '', true],
    ['ws.http3', 'HTTP/3', 'info', '', 'Requires QUIC support — not available in stock Debian Apache.', 'none'],
    ['ws.ssl_tls', 'SSL/TLS', 'toggle', '1', '1', '', '', true],
    ['ws.mpm', 'MPM', 'info', '', 'Detected MPM module (event/prefork/worker).', 'none'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'restart' => true, 'risk' => 'high', 'applies' => 'Apache']);
$S[] = ph_tweak(['key' => 'ws.modules', 'cat' => $c, 'label' => 'Modules', 'type' => 'info', 'desc' => 'Enabled Apache modules (read-only list).', 'handler' => 'detect:apache_modules']);
$S[] = ph_tweak(['key' => 'ws.vhosts', 'cat' => $c, 'label' => 'Virtual hosts', 'type' => 'info', 'desc' => 'Enabled Apache vhosts (read-only list).', 'handler' => 'detect:apache_vhosts']);

/* ================= 20. DNS ================= */
$c = 'dns';
foreach ([
    ['dns.server', 'DNS server', 'info', '', 'Bind9 (integrated with the existing Planet Hosts DNS system).', 'none'],
    ['dns.ns1', 'Nameserver 1', 'text', 'ns1.planet-hosts.com', 'ns1.planet-hosts.com'],
    ['dns.ns2', 'Nameserver 2', 'text', 'ns2.planet-hosts.com', 'ns2.planet-hosts.com'],
    ['dns.default_ttl', 'Default TTL', 'number', '3600', '3600', '', '', true],
    ['dns.allow_a', 'A records', 'toggle', '1', '1'],
    ['dns.allow_aaaa', 'AAAA records', 'toggle', '1', '1'],
    ['dns.allow_cname', 'CNAME', 'toggle', '1', '1'],
    ['dns.allow_mx', 'MX', 'toggle', '1', '1'],
    ['dns.allow_txt', 'TXT', 'toggle', '1', '1'],
    ['dns.allow_srv', 'SRV', 'toggle', '1', '1'],
    ['dns.allow_caa', 'CAA', 'toggle', '1', '1'],
    ['dns.spf', 'SPF', 'info', '', 'SPF records auto-included in mail zones.', 'none'],
    ['dns.dkim', 'DKIM', 'info', '', 'DKIM selector records auto-published (OpenDKIM).', 'none'],
    ['dns.dmarc', 'DMARC', 'info', '', '_dmarc record published.', 'none'],
    ['dns.dnssec', 'DNSSEC', 'toggle', '0', '0', '', '', true],
    ['dns.zone_transfer', 'Zone transfer restrictions', 'text', 'none', 'none', '', '', true],
    ['dns.recursive', 'Recursive DNS', 'select', ['local' => 'Localhost only', 'open' => 'Open recursion (DANGEROUS)'], 'local', 'local', 'Open recursion enables amplification attacks.', 'CRITICAL', '', true],
    ['dns.cache', 'DNS cache', 'toggle', '1', '1'],
    ['dns.logging', 'DNS logging', 'toggle', '0', '0'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'sec' => $r[6] ?? '', 'restart' => $r[7] ?? false, 'risk' => $r[0] === 'dns.recursive' ? 'high' : 'normal']);

/* ================= 21. BACKUPS ================= */
$c = 'backups';
foreach ([
    ['backup.enabled', 'Enable backups', 'toggle', '1', '1'],
    ['backup.schedule', 'Backup schedule', 'select', ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'], 'daily', 'daily'],
    ['backup.full', 'Full backups', 'toggle', '1', '1'],
    ['backup.incremental', 'Incremental backups', 'toggle', '0', '0'],
    ['backup.databases', 'Database backups', 'toggle', '1', '1'],
    ['backup.configs', 'Configuration backups', 'toggle', '1', '1'],
    ['backup.accounts', 'Account backups', 'toggle', '1', '1'],
    ['backup.server', 'Server backups', 'toggle', '0', '0'],
    ['backup.dest_local', 'Local backup destination', 'text', '/var/backups/planet-hosts', '/var/backups/planet-hosts'],
    ['backup.dest_sftp', 'Remote SFTP destination', 'text', '', 'user@host:/path'],
    ['backup.dest_ftp', 'Remote FTP destination', 'text', '', 'ftp://user:pass@host/path (avoid plaintext — use SFTP)'],
    ['backup.dest_s3', 'S3-compatible storage', 'text', '', 'endpoint + bucket'],
    ['backup.retention', 'Retention (copies)', 'number', '7', '7'],
    ['backup.compression', 'Compression', 'toggle', '1', '1'],
    ['backup.encryption', 'Backup encryption', 'toggle', '0', '1', 'Encrypt archives at rest (GPG/openssl).'],
    ['backup.verify', 'Backup verification', 'toggle', '1', '1'],
    ['backup.auto_test', 'Automatic backup testing', 'toggle', '0', '0'],
    ['backup.failure_alerts', 'Backup failure alerts', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'applies' => 'Backup Manager (integrates with existing backup system)']);

/* ================= 22. FIREWALL (integrates) ================= */
$c = 'firewall';
$S[] = ph_tweak(['key' => 'fw.enabled', 'cat' => $c, 'label' => 'Firewall enabled', 'type' => 'info', 'desc' => 'firewalld is the active firewall engine (existing Security Center manages rules).', 'handler' => 'detect:firewalld']);
$S[] = ph_tweak(['key' => 'fw.manage', 'cat' => $c, 'label' => 'Firewall & Security Center', 'type' => 'link', 'link' => '/admin/security', 'desc' => 'Ports, services, blocks, trusted IPs and rate limits are managed there.', 'handler' => 'none']);
foreach ([
    ['fw.default_policy', 'Default policy', 'select', ['drop' => 'Drop', 'allow' => 'Allow'], 'drop', 'drop', '', '', true],
    ['fw.rate_limit', 'Rate limiting (SSH)', 'toggle', '1', '1'],
    ['fw.conn_limit', 'Connection limits', 'number', '200', '200'],
    ['fw.logging', 'Logging', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'restart' => $r[7] ?? false]);

/* ================= 23. FAIL2BAN ================= */
$c = 'fail2ban';
foreach ([
    ['f2b.enabled', 'Fail2Ban enabled', 'info', '', 'Service status (live).', 'none'],
    ['f2b.bantime', 'Ban duration (sec, -1 = permanent)', 'number', '-1', '-1', 'Permanent bans are current best practice here.', '', false, 'high'],
    ['f2b.findtime', 'Find time (sec)', 'number', '600', '600'],
    ['f2b.maxretry', 'Maximum retries', 'number', '3', '3'],
    ['f2b.dbpurgeage', 'Ban DB retention (sec)', 'number', '2592000', '2592000'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'risk' => $r[8] ?? 'normal', 'handler' => $r[0] === 'f2b.enabled' ? 'detect:fail2ban' : 'fail2ban', 'applies' => 'Fail2Ban']);
foreach (['ssh' => 'SSH protection', 'ftp' => 'FTP protection', 'mail' => 'Mail protection', 'web' => 'Web protection', 'panel' => 'Panel protection', 'api' => 'API protection'] as $k => $l) {
    $S[] = ph_tweak(['key' => "f2b.{$k}", 'cat' => $c, 'label' => $l, 'type' => 'toggle', 'default' => '1', 'recommended' => '1', 'desc' => "Keep the corresponding jail active.", 'handler' => "detect:f2b_jail:{$k}"]);
}
$S[] = ph_tweak(['key' => 'f2b.custom_jails', 'cat' => $c, 'label' => 'Custom jails', 'type' => 'info', 'desc' => 'Custom jails defined in /etc/fail2ban/jail.local (planet-* jails).', 'handler' => 'none']);

/* ================= 24. MODSECURITY ================= */
$c = 'modsecurity';
foreach ([
    ['modsec.enabled', 'ModSecurity enabled', 'info', '', 'Detected module state.', 'none'],
    ['modsec.mode', 'Mode', 'select', ['detection' => 'Detection only', 'blocking' => 'Blocking'], 'detection', 'blocking', '', '', true],
    ['modsec.owasp', 'OWASP CRS', 'toggle', '0', '0', '', '', true],
    ['modsec.rule_logging', 'Rule logging', 'toggle', '1', '1'],
    ['modsec.audit_logging', 'Audit logging', 'toggle', '1', '1'],
    ['modsec.exclusions', 'Exclusions', 'textarea', '', '', 'Per-rule or per-domain exclusions.'],
    ['modsec.per_domain', 'Per-domain rules', 'info', '', 'Managed via vhost includes.', 'none'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'restart' => $r[7] ?? false]);

/* ================= 25. SSL/TLS ================= */
$c = 'ssl';
foreach ([
    ['ssl.enabled', 'SSL enabled', 'toggle', '1', '1'],
    ['ssl.autossl', 'AutoSSL (Let\u2019s Encrypt)', 'toggle', '1', '1'],
    ['ssl.auto_renew', 'Automatic renewal', 'toggle', '1', '1'],
    ['ssl.cert_monitor', 'Certificate monitoring', 'toggle', '1', '1'],
    ['ssl.tls_min', 'TLS minimum version', 'select', ['1.2' => 'TLS 1.2', '1.3' => 'TLS 1.3'], '1.2', '1.2', '', '', true],
    ['ssl.protocols', 'SSL protocols', 'text', 'TLSv1.2 TLSv1.3', 'TLSv1.2 TLSv1.3', '', '', true],
    ['ssl.ciphers', 'Cipher configuration', 'text', 'HIGH:!aNULL:!MD5', 'HIGH:!aNULL:!MD5', '', '', true],
    ['ssl.hsts', 'HSTS', 'toggle', '1', '1', '', '', true],
    ['ssl.expiry_alert_days', 'Certificate expiration alerts (days before)', 'number', '14', '14'],
    ['ssl.force_https', 'Force HTTPS', 'toggle', '1', '1', '', '', true],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'restart' => $r[7] ?? false, 'applies' => 'Apache SSL']);

/* ================= 26. RESOURCE LIMITS ================= */
$c = 'resources';
foreach ([
    ['res.cpu_per_account', 'CPU limits per account %', 'number', '100', '100'],
    ['res.ram_per_account_mb', 'RAM limits per account (MB)', 'number', '512', '512'],
    ['res.processes_per_account', 'Process limits per account', 'number', '50', '50'],
    ['res.disk_per_account_mb', 'Disk limits per account (MB)', 'number', '1024', '1024'],
    ['res.inodes_per_account', 'Inode limits per account', 'number', '100000', '100000'],
    ['res.bandwidth_mb', 'Network bandwidth per account (MB/mo)', 'number', '10240', '10240'],
    ['res.connections', 'Network connections (per IP)', 'number', '200', '200'],
    ['res.php_processes', 'PHP processes per account', 'number', '10', '10'],
    ['res.db_connections', 'Database connections per account', 'number', '30', '30'],
    ['res.email_hourly', 'Email limits (per hour)', 'number', '200', '200'],
    ['res.ftp_connections', 'FTP connections', 'number', '10', '10'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => 'number', 'default' => $r[3], 'recommended' => $r[4], 'desc' => 'Default applied to new accounts; integrates with hosting packages (per-package overrides win).', 'applies' => 'Packages / quota']);

/* ================= 27. RADIO / STREAMING ================= */
$c = 'radio';
foreach ([
    ['radio.shoutcast_v1', 'SHOUTcast v1 enabled', 'info', '', 'Global DNAS at /opt/planethosts/shoutcast1 (PortBase 11000).', 'none'],
    ['radio.shoutcast_v2', 'SHOUTcast v2 enabled', 'info', '', 'Global DNAS at /opt/planethosts/shoutcast (port 8000).', 'none'],
    ['radio.icecast', 'Icecast enabled', 'info', '', 'Global Icecast2 on port 8002.', 'none'],
    ['radio.default_port', 'Default streaming port', 'number', '8000', '8000'],
    ['radio.port_range', 'Port range', 'text', '8000-9999', '8000-9999', 'Pool used by PortManager.'],
    ['radio.max_stations', 'Maximum stations', 'number', '100', '100'],
    ['radio.max_listeners', 'Maximum listeners (per station default)', 'number', '100', '100'],
    ['radio.max_djs', 'Maximum DJs per station', 'number', '10', '10'],
    ['radio.autodj', 'AutoDJ', 'toggle', '1', '1'],
    ['radio.relay', 'Relay', 'toggle', '0', '0'],
    ['radio.storage', 'Station storage', 'text', '/home/{user}/radio', '/home/{user}/radio'],
    ['radio.bitrate', 'Stream bitrate (default kbps)', 'number', '128', '128'],
    ['radio.stream_monitor', 'Stream monitoring', 'toggle', '1', '1', 'Live probes via radio_helper.'],
    ['radio.listener_monitor', 'Listener monitoring', 'toggle', '1', '1'],
    ['radio.dj_timeout', 'DJ connection timeout (sec)', 'number', '60', '60'],
    ['radio.restart_behavior', 'Station restart behavior', 'select', ['auto' => 'Auto-restart on failure', 'manual' => 'Manual only'], 'auto', 'auto'],
    ['radio.logging', 'Stream logging', 'toggle', '1', '1'],
    ['radio.backup', 'Radio backup', 'toggle', '1', '1', 'Playlist/music backups with account backups.'],
    ['radio.bandwidth_limit', 'Radio bandwidth limits (Mbps, 0=unlimited)', 'number', '0', '0'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'applies' => 'Radio subsystem (integrates; does not modify radio behavior)']);

/* ================= 28. GAME SERVERS ================= */
$c = 'gameservers';
foreach ([
    ['gs.enabled', 'Game server enabled', 'toggle', '1', '1'],
    ['gs.node_agents', 'Node agents', 'info', '', 'Managed in Game Servers → Nodes (existing system).', 'none'],
    ['gs.node_heartbeat', 'Node heartbeat interval (sec)', 'number', '30', '30'],
    ['gs.node_poll', 'Agent polling interval (sec)', 'number', '10', '10'],
    ['gs.steamcmd', 'SteamCMD', 'info', '', 'Detected installation state.', 'none'],
    ['gs.install_path', 'Game installation path', 'text', '/home/{user}/gameservers', '/home/{user}/gameservers'],
    ['gs.default_dir', 'Default game directory', 'text', '/home/{user}/gameservers/{game}', '/home/{user}/gameservers/{game}'],
    ['gs.cpu_limit', 'CPU limits per server %', 'number', '100', '100'],
    ['gs.ram_limit_mb', 'RAM limits per server (MB)', 'number', '2048', '2048'],
    ['gs.disk_limit_mb', 'Disk limits per server (MB)', 'number', '10240', '10240'],
    ['gs.network_limit', 'Network limits (Mbps, 0=unlimited)', 'number', '0', '0'],
    ['gs.port_alloc', 'Port allocation range', 'text', '27000-28000', '27000-28000'],
    ['gs.auto_restart', 'Automatic restart', 'toggle', '1', '1'],
    ['gs.crash_detect', 'Crash detection', 'toggle', '1', '1'],
    ['gs.node_maintenance', 'Node maintenance', 'info', '', 'Managed per node in Game Servers.', 'none'],
    ['gs.node_offline_detect', 'Node offline detection', 'toggle', '1', '1'],
    ['gs.logging', 'Game server logging', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'applies' => 'Game Servers subsystem']);

/* ================= 29. RESELLERS ================= */
$c = 'resellers';
foreach ([
    ['reseller.max_accounts', 'Maximum reseller accounts', 'number', '50', '50'],
    ['reseller.max_hosting', 'Maximum hosting accounts per reseller', 'number', '25', '25'],
    ['reseller.max_packages', 'Maximum packages per reseller', 'number', '10', '10'],
    ['reseller.max_domains', 'Maximum domains per account', 'number', '5', '5'],
    ['reseller.max_subdomains', 'Maximum subdomains per account', 'number', '10', '10'],
    ['reseller.max_bandwidth_mb', 'Maximum bandwidth (MB/mo)', 'number', '10240', '10240'],
    ['reseller.max_disk_mb', 'Maximum disk (MB)', 'number', '2048', '2048'],
    ['reseller.whitelabel', 'White-label branding', 'toggle', '1', '1'],
    ['reseller.ns1', 'Reseller nameserver 1', 'text', '', 'Blank = use server defaults.'],
    ['reseller.ns2', 'Reseller nameserver 2', 'text', ''],
    ['reseller.ssl', 'Reseller SSL', 'toggle', '1', '1'],
    ['reseller.dns', 'Reseller DNS control', 'toggle', '1', '1'],
    ['reseller.email_hourly', 'Reseller email limits (per hour)', 'number', '200', '200'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => ($r[4] ?? $r[3]), 'desc' => $r[5] ?? '', 'applies' => 'Reseller system']);

/* ================= 30. BILLING (integrates) ================= */
$c = 'billing';
$S[] = ph_tweak(['key' => 'billing.manage', 'cat' => $c, 'label' => 'Billing & Invoicing System', 'type' => 'link', 'link' => '/admin/billing', 'desc' => 'Products, invoices, gateways and provisioning are managed in the existing Billing system.', 'handler' => 'none']);
foreach ([
    ['billing.auto_provision', 'Automatic provisioning', 'toggle', '1', '1'],
    ['billing.provision_after_payment', 'Provision after payment', 'toggle', '1', '1'],
    ['billing.provision_manual', 'Provision on manual approval', 'toggle', '0', '0'],
    ['billing.suspend_failed', 'Suspension after failed payment (days)', 'number', '3', '3'],
    ['billing.grace_days', 'Grace period (days)', 'number', '5', '5'],
    ['billing.cancel_behavior', 'Cancellation behavior', 'select', ['immediate' => 'Immediate', 'end_of_period' => 'End of period'], 'end_of_period', 'end_of_period'],
    ['billing.auto_suspend', 'Auto suspension', 'toggle', '1', '1'],
    ['billing.auto_unsuspend', 'Auto unsuspension', 'toggle', '1', '1'],
    ['billing.invoice_notify', 'Invoice notifications', 'toggle', '1', '1'],
    ['billing.payment_notify', 'Payment notifications', 'toggle', '1', '1'],
    ['billing.provision_logs', 'Provisioning logs', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2]] + ($r[2] === 'select' ? ['options' => $r[3 + 1]] : []) + ['default' => $r[3], 'recommended' => $r[4], 'desc' => '', 'applies' => 'Billing (existing system)']);

/* ================= 31. NODE / AGENT ================= */
$c = 'node';
foreach ([
    ['node.heartbeat', 'Node heartbeat interval (sec)', 'number', '30', '30'],
    ['node.poll', 'Agent polling interval (sec)', 'number', '10', '10'],
    ['node.auth', 'Agent authentication', 'toggle', '1', '1', 'API keys per node.'],
    ['node.timeout', 'Node timeout (sec)', 'number', '90', '90'],
    ['node.offline_threshold', 'Node offline threshold (missed heartbeats)', 'number', '3', '3'],
    ['node.maintenance', 'Node maintenance mode', 'toggle', '0', '0'],
    ['node.health_checks', 'Node health checks', 'toggle', '1', '1'],
    ['node.auto_recovery', 'Automatic node recovery', 'toggle', '0', '0'],
    ['node.update_notify', 'Agent update notifications', 'toggle', '1', '1'],
    ['node.logging', 'Node logging', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '', 'applies' => 'Node / Agent system']);

/* ================= 32. LICENSING ================= */
$c = 'licensing';
foreach ([
    ['license.interval', 'License validation interval (hours)', 'number', '24', '24'],
    ['license.status_check', 'License status checking', 'toggle', '1', '1'],
    ['license.grace_days', 'Grace period (days)', 'number', '3', '3'],
    ['license.channel', 'Update channel', 'select', ['stable' => 'Stable', 'beta' => 'Beta'], 'stable', 'stable'],
    ['license.failure_notify', 'License failure notifications', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => 'License validation handled by the existing Core\License engine; secrets are never exposed.', 'applies' => 'Licensing']);

/* ================= 33. API (integrates) ================= */
$c = 'api';
$S[] = ph_tweak(['key' => 'api.manage', 'cat' => $c, 'label' => 'API Keys & Access', 'type' => 'link', 'link' => '/admin/settings/api', 'desc' => 'Key creation and management lives in the API tab. Secrets are never re-displayed after creation.', 'handler' => 'none']);
foreach ([
    ['api.enabled', 'API enabled', 'toggle', '1', '1'],
    ['api.request_logging', 'API request logging', 'toggle', '1', '1'],
    ['api.timeout', 'API timeout (sec)', 'number', '30', '30'],
    ['api.token_expiry_days', 'API token expiration (days, 0=never)', 'number', '0', '365'],
    ['api.ip_restrict', 'IP restrictions', 'text', '', 'Comma-separated allowed IPs/CIDRs (blank = all).'],
    ['api.cors', 'CORS', 'text', '*', 'Allowed origins (comma separated).'],
    ['api.webhook_retries', 'Webhook retries', 'number', '3', '3'],
    ['api.webhook_logging', 'Webhook logging', 'toggle', '1', '1'],
] as $r) $S[] = ph_tweak(['key' => $r[0], 'cat' => $c, 'label' => $r[1], 'type' => $r[2], 'default' => $r[3], 'recommended' => $r[4], 'desc' => $r[5] ?? '']);

/* marker for part 3 (features) */
$S;

$categories = [
    'compression' => ['name' => 'Compression', 'icon' => '📦'],
    'development' => ['name' => 'Development', 'icon' => '🐞'],
    'display' => ['name' => 'Display & Branding', 'icon' => '🎨'],
    'domains' => ['name' => 'Domains', 'icon' => '🌍'],
    'logging' => ['name' => 'Logging', 'icon' => '📝'],
    'mail' => ['name' => 'Mail', 'icon' => '📧'],
    'notifications' => ['name' => 'Notifications', 'icon' => '🔔'],
    'packages' => ['name' => 'Packages', 'icon' => '📦'],
    'php' => ['name' => 'PHP', 'icon' => '🐘'],
    'redirection' => ['name' => 'Redirection', 'icon' => '↪️'],
    'security' => ['name' => 'Security', 'icon' => '🔒'],
    'software' => ['name' => 'Software', 'icon' => '💿'],
    'sql' => ['name' => 'SQL / Database', 'icon' => '🗄️'],
    'stats' => ['name' => 'Stats and Logs', 'icon' => '📊'],
    'statsprograms' => ['name' => 'Stats Programs', 'icon' => '🧮'],
    'status' => ['name' => 'Status', 'icon' => '🩺'],
    'support' => ['name' => 'Support', 'icon' => '🛟'],
    'system' => ['name' => 'System', 'icon' => '🖥️'],
    'webserver' => ['name' => 'Web Server', 'icon' => '🌐'],
    'dns' => ['name' => 'DNS', 'icon' => '🧭'],
    'backups' => ['name' => 'Backups', 'icon' => '💾'],
    'firewall' => ['name' => 'Firewall', 'icon' => '🧱'],
    'fail2ban' => ['name' => 'Fail2Ban', 'icon' => '🚫'],
    'modsecurity' => ['name' => 'ModSecurity', 'icon' => '🛡️'],
    'ssl' => ['name' => 'SSL/TLS', 'icon' => '🔐'],
    'resources' => ['name' => 'Resource Limits', 'icon' => '📐'],
    'radio' => ['name' => 'Radio / Streaming', 'icon' => '📻'],
    'gameservers' => ['name' => 'Game Servers', 'icon' => '🎮'],
    'resellers' => ['name' => 'Resellers', 'icon' => '👥'],
    'billing' => ['name' => 'Billing', 'icon' => '💳'],
    'node' => ['name' => 'Node / Agent', 'icon' => '🕸️'],
    'licensing' => ['name' => 'Licensing', 'icon' => '🧾'],
    'api' => ['name' => 'API', 'icon' => '🔌'],
];

/* Normalize select rows: options/default may have been built swapped */
foreach ($S as $i => $s) {
    if (($s['type'] ?? '') === 'select') {
        if (is_array($s['default'] ?? null)) {
            $oldOptions = $s['options'] ?? '';
            $S[$i]['options'] = $s['default'];
            $S[$i]['default'] = is_string($oldOptions) && $oldOptions !== '' ? $oldOptions : '';
            if (($S[$i]['recommended'] ?? '') === '') $S[$i]['recommended'] = $S[$i]['default'];
        }
        if (!is_array($S[$i]['options'] ?? null)) $S[$i]['options'] = [];
    } else {
        if (isset($s['options']) && is_array($s['options'])) $S[$i]['options'] = [];
        if (is_array($s['default'] ?? null)) $S[$i]['default'] = '';
    }
}

return ['categories' => $categories, 'settings' => $S];

