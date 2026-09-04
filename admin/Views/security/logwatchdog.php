<?php
$hasAlerts = !empty($alerts);
$hasLargeFiles = !empty($largeFiles);
$totalAlerts = count($alerts);
$criticalAlerts = count(array_filter($alerts, fn($a) => ($a['severity'] ?? '') === 'critical'));
?>
<style>
.alert-item {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
}
.alert-item.critical {
    border-color: rgba(248,113,113,.3);
    background: rgba(248,113,113,.05);
}
.alert-header {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 280px;
}
.alert-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.alert-badge.critical { background: rgba(248,113,113,.15); color: #f87171; }
.alert-badge.warning { background: rgba(250,204,21,.15); color: #facc15; }
.alert-badge.info { background: rgba(0,191,255,.15); color: #00bfff; }
.alert-title {
    font-size: 13px;
    font-weight: 600;
    color: #e0e0e0;
    word-break: break-word;
}
.alert-meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 4px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.alert-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}
.alert-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}
.btn-sm {
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: .15s;
}
.btn-danger { background: linear-gradient(135deg,#ef4444,#dc2626); color: #fff; }
.btn-danger:hover { opacity: .9; }
.btn-secondary { background: #333; color: #ccc; }
.btn-secondary:hover { background: #444; }
.log-file-item {
    background: rgba(255,255,255,.02);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}
.log-file-info {
    flex: 1;
    min-width: 280px;
}
.log-file-path {
    font-family: monospace;
    font-size: 12px;
    color: #f87171;
    word-break: break-all;
    margin-bottom: 4px;
}
.log-file-size {
    font-size: 13px;
    font-weight: 600;
    color: #f87171;
}
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #64748b;
}
.empty-state svg {
    width: 48px;
    height: 48px;
    margin-bottom: 12px;
    opacity: .5;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.card-header h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}
.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}
.badge-critical { background: rgba(248,113,113,.15); color: #f87171; }
.badge-ok { background: rgba(74,222,128,.15); color: #4ade80; }
</style>

<div class="card-header">
    <h3>Log Size Watchdog</h3>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="/admin/security/logwatchdog" class="btn btn-sm btn-primary">🔄 Refresh Check</a>
    </div>
</div>

<?php if ($hasAlerts || $hasLargeFiles): ?>
    
    <?php if ($hasAlerts): ?>
    <div style="margin-bottom:24px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <h4 style="margin:0;font-size:14px">Alert History (<span style="color:#f87171"><?php echo $totalAlerts; ?></span> total, <span style="color:#f87171"><?php echo $criticalAlerts; ?></span> critical)</h4>
            <?php if ($totalAlerts > 0): ?>
                <button class="btn btn-sm btn-secondary" onclick="clearAlerts()">🗑️ Clear All Alerts</button>
            <?php endif; ?>
        </div>
        
        <div id="alerts-container">
            <?php foreach ($alerts as $alert): ?>
            <div class="alert-item <?php echo ($alert['severity'] ?? 'info') === 'critical' ? 'critical' : ''; ?>" data-file="<?php echo htmlspecialchars($alert['file'] ?? ''); ?>">
                <div class="alert-header">
                    <span class="alert-badge <?php echo $alert['severity'] ?? 'info'; ?>">
                        <?php echo ucfirst($alert['severity'] ?? 'info'); ?>
                    </span>
                    <span class="alert-title">
                        <?php echo htmlspecialchars($alert['file'] ?? 'Unknown file'); ?>
                    </span>
                </div>
                <div class="alert-meta">
                    <span>💾 <?php echo number_format($alert['size_mb'] ?? 0, 1); ?> MB (<?php echo $alert['size_gb'] ?? '0'; ?> GB)</span>
                    <span>⚠️ Threshold: <?php echo $alert['threshold_gb'] ?? 1; ?> GB</span>
                    <span>🕐 <?php echo $alert['timestamp'] ?? 'Unknown'; ?></span>
                </div>
                <div class="alert-actions">
                    <button class="btn-sm btn-danger dismiss-alert" data-file="<?php echo htmlspecialchars($alert['file'] ?? ''); ?>">Dismiss</button>
                    <button class="btn-sm btn-secondary view-log" data-file="<?php echo htmlspecialchars($alert['file'] ?? ''); ?>">View Log</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($hasLargeFiles): ?>
    <div style="margin-top:24px">
        <h4 style="margin:0 0 12px;font-size:14px">Currently Oversized Log Files (<span style="color:#f87171"><?php echo count($largeFiles); ?></span>)</h4>
        <div>
            <?php foreach ($largeFiles as $lf): ?>
            <div class="log-file-item">
                <div class="log-file-info">
                    <div class="log-file-path"><?php echo htmlspecialchars($lf['file']); ?></div>
                    <div class="log-file-size">
                        <?php echo $lf['size_gb']; ?> GB (<?php echo number_format($lf['size_mb'], 1); ?> MB)
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <a href="/admin/security/logs/<?php echo urlencode(basename($lf['file'])); ?>" class="btn btn-sm btn-secondary" target="_blank">View Log</button>
                    <button class="btn-sm btn-danger" onclick="truncateLog('<?php echo htmlspecialchars($lf['file']); ?>')">🗑️ Truncate</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
<?php else: ?>
    <div class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"/>
        </svg>
        <h3 style="margin:12px 0 6px;color:var(--text)">No Oversized Logs</h3>
        <p style="margin:0;color:#64748b">All log files are within the 1GB threshold.</p>
    </div>
<?php endif; ?>

<script>
function clearAlerts() {
    if (!confirm('Clear all alerts? This cannot be undone.')) return;
    fetch('/admin/security/logwatchdog/clear', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    }).then(() => location.reload());
}

function dismissAlert(file) {
    if (!confirm('Dismiss this alert?')) return;
    fetch('/admin/security/logwatchdog/dismiss', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ file })
    }).then(() => location.reload());
}

function truncateLog(file) {
    if (!confirm('Truncate log file? This will clear the log content but keep the file.')) return;
    fetch('/admin/security/logwatchdog/truncate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ file })
    }).then(r => r.json()).then(res => {
        if (res.success) {
            alert('Log truncated successfully');
            location.reload();
        } else {
            alert('Error: ' + (res.error || 'Unknown error'));
        }
    });
}

function viewLog(file) {
    window.open('/admin/security/logs/' + encodeURIComponent(file), '_blank');
}

// Auto-refresh every 60 seconds
setInterval(() => {
    // Only auto-refresh if not interacting
    if (!document.querySelector(':hover')) {
        // Could add silent refresh here if needed
    }
}, 60000);
</script>