<?php
$versions = $phpStats['available_versions'] ?? ['8.2','8.1','8.0','7.4'];
?><div class="stats-grid">
<div class="stat-card"><h3>PHP Version</h3><div class="value" style="font-size:24px"><?php echo PHP_VERSION; ?></div><div class="label">Current SAPI: <?php echo PHP_SAPI; ?></div></div>
<div class="stat-card"><h3>Available Versions</h3><div class="value" style="font-size:24px"><?php echo count($versions); ?></div><div class="label"><?php echo implode(', ', $versions); ?></div></div>
<div class="stat-card"><h3>Extensions</h3><div class="value" style="font-size:24px"><?php echo $phpStats['enabled_extensions'] ?: count(get_loaded_extensions()); ?></div><div class="label">Loaded extensions</div></div>
<div class="stat-card"><h3>PHP-FPM Pools</h3><div class="value" style="font-size:24px"><?php echo $phpStats['php_fpm_pools'] ?: 'N/A'; ?></div><div class="label">Account-specific pools</div></div>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">PHP Versions &amp; Configuration</h3>
<p style="color:var(--text-secondary);margin-bottom:16px">PHP versions can be assigned per account. Each account with a dedicated PHP version gets its own FPM pool, isolating it from other accounts.</p>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px">
<?php foreach (['5.6','7.0','7.1','7.2','7.3','7.4','8.0','8.1','8.2','8.3','8.4'] as $ver): ?>
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(0,191,255,.1);border-radius:10px;padding:16px;text-align:center">
<div style="font-size:28px;font-weight:700;color:<?php echo version_compare($ver, '8.0', '>=') ? '#4ade80' : (version_compare($ver, '7.0', '>=') ? '#facc15' : '#f87171'); ?>"><?php echo $ver; ?></div>
<div style="font-size:12px;color:var(--text-muted);margin:4px 0">PHP <?php echo $ver; ?></div>
<?php $installed = in_array($ver, $versions); ?>
<div style="font-size:12px;padding:4px 10px;border-radius:4px;display:inline-block;margin-top:6px;background:<?php echo $installed ? '#1a3a2a' : '#3a1a1a'; ?>;color:<?php echo $installed ? '#4ade80' : '#f87171'; ?>"><?php echo $installed ? 'Installed' : 'Available'; ?></div>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">PHP INI Settings</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
<div><strong style="color:var(--text-secondary);font-size:12px">upload_max_filesize</strong><br><?php echo ini_get('upload_max_filesize'); ?></div>
<div><strong style="color:var(--text-secondary);font-size:12px">post_max_size</strong><br><?php echo ini_get('post_max_size'); ?></div>
<div><strong style="color:var(--text-secondary);font-size:12px">memory_limit</strong><br><?php echo ini_get('memory_limit'); ?></div>
<div><strong style="color:var(--text-secondary);font-size:12px">max_execution_time</strong><br><?php echo ini_get('max_execution_time'); ?>s</div>
<div><strong style="color:var(--text-secondary);font-size:12px">max_input_vars</strong><br><?php echo ini_get('max_input_vars'); ?></div>
<div><strong style="color:var(--text-secondary);font-size:12px">max_input_time</strong><br><?php echo ini_get('max_input_time'); ?>s</div>
</div>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">Loaded Extensions (<?php echo count(get_loaded_extensions()); ?>)</h3>
<div style="display:flex;flex-wrap:wrap;gap:6px">
<?php $commonExts = ['bcmath','bz2','calendar','Core','ctype','curl','date','dom','exif','fileinfo','filter','ftp','gd','gettext','gmp','hash','iconv','imagick','imap','intl','json','ldap','libxml','mbstring','mysqli','mysqlnd','openssl','pcntl','pcre','PDO','pdo_mysql','pdo_sqlite','pear','phar','posix','pspell','readline','redis','reflection','session','shmop','SimpleXML','soap','sockets','sodium','SPL','sqlite3','standard','sysvmsg','sysvsem','sysvshm','tokenizer','wddx','xml','xmlreader','xmlwriter','xsl','Zend OPcache','zip','zlib'];
$loaded = get_loaded_extensions();
foreach ($commonExts as $ext):
$has = in_array($ext, $loaded);
?>
<span style="padding:4px 10px;border-radius:5px;font-size:12px;border:1px solid <?php echo $has ? 'rgba(74,222,128,.3)' : 'rgba(248,113,113,.3)'; ?>;background:<?php echo $has ? 'rgba(74,222,128,.08)' : 'rgba(248,113,113,.08)'; ?>;color:<?php echo $has ? '#4ade80' : '#f87171'; ?>"><?php echo $ext; ?></span>
<?php endforeach; ?>
</div>
<p style="color:var(--text-muted);font-size:13px;margin-top:12px">Green = installed &middot; Red = missing (install via yum/dnf)</p>
</div>
