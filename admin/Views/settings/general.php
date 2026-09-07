<?php $currentTab = 'general'; require __DIR__ . '/_tabs.php'; ?>
<div class="set-wrap">
<div class="set-head">
  <h2>⚙️ General Settings</h2>
  <p>Server identity and global panel preferences.</p>
</div>

<div class="set-grid-3">
  <div>
    <div class="set-card">
      <h4><i class="bi bi-sliders"></i> Server &amp; Defaults</h4>
      <form class="set-form" method="POST" action="/admin/settings/general/save">
        <div class="form-group">
          <label>Hostname</label>
          <input name="hostname" value="<?php echo htmlspecialchars($hostname); ?>" placeholder="server.example.com">
          <small>Fully qualified hostname of this server.</small>
        </div>
        <div class="set-2col">
          <div class="form-group">
            <label>Timezone</label>
            <select name="timezone">
              <?php foreach (timezone_identifiers_list() as $tz): ?>
              <option value="<?php echo $tz; ?>" <?php echo $tz===$timezone?'selected':''; ?>><?php echo $tz; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Default Language</label>
            <select name="language">
              <option value="en" <?php echo $language==='en'?'selected':''; ?>>🇺🇸 English</option>
              <option value="es" <?php echo $language==='es'?'selected':''; ?>>🇪🇸 Spanish</option>
              <option value="fr" <?php echo $language==='fr'?'selected':''; ?>>🇫🇷 French</option>
              <option value="de" <?php echo $language==='de'?'selected':''; ?>>🇩🇪 German</option>
            </select>
          </div>
        </div>
        <div class="set-actions">
          <button type="submit" class="btn set-btn-save">💾 Save Changes</button>
          <a href="/admin/settings" class="btn set-btn-ghost">Back</a>
        </div>
      </form>
    </div>

    <div class="set-card">
      <h4><i class="bi bi-hdd-network"></i> Server IPs</h4>
      <?php
      $ipList = [];
      try {
          $pdo = \Core\Application::getInstance()->get('db')->pdo();
          $q = $pdo->query("SELECT * FROM server_ips ORDER BY server, ip");
          if ($q) $ipList = $q->fetchAll(PDO::FETCH_OBJ);
      } catch (\Exception $e) {}
      $totalIps = count($ipList);
      $usedIps = count(array_filter($ipList, fn($i) => !empty($i->assigned_to)));
      ?>
      <div class="stats-grid" style="margin-bottom:14px">
        <div class="stat-card"><h3>Total</h3><div class="value"><?php echo $totalIps; ?></div></div>
        <div class="stat-card"><h3>In Use</h3><div class="value" style="color:#facc15"><?php echo $usedIps; ?></div></div>
        <div class="stat-card"><h3>Available</h3><div class="value" style="color:#4ade80"><?php echo $totalIps - $usedIps; ?></div></div>
      </div>
      <form class="set-form" method="POST" action="/admin/ip/store" style="background:rgba(0,0,0,.2);padding:12px;border-radius:8px">
        <div class="set-2col">
          <div class="form-group" style="margin-bottom:8px"><label>IP Address</label><input name="ip" placeholder="192.168.1.100" required></div>
          <div class="form-group" style="margin-bottom:8px"><label>Server</label><input name="server" value="main" placeholder="main"></div>
        </div>
        <button type="submit" class="btn set-btn-save" style="padding:7px 14px;font-size:12px">+ Add IP</button>
      </form>
      <table style="margin-top:14px">
        <thead><tr><th>IP</th><th>Server</th><th>Assigned To</th><th>NS</th><th></th></tr></thead>
        <tbody>
        <?php if (!empty($ipList)): foreach ($ipList as $ip): ?>
        <tr>
          <td style="font-family:ui-monospace,monospace;font-size:12px"><?php echo htmlspecialchars($ip->ip); ?></td>
          <td><?php echo htmlspecialchars($ip->server ?? "main"); ?></td>
          <td><?php echo $ip->assigned_to ? htmlspecialchars($ip->assigned_to) : '<span style="color:#4ade80">Available</span>'; ?></td>
          <td style="font-size:11px;color:#64748b"><?php echo $ip->ns1 ? htmlspecialchars($ip->ns1) : "—"; ?></td>
          <td><a href="/admin/ip/delete/<?php echo $ip->id; ?>" style="color:#f87171;font-size:12px" onclick="return confirm('Delete this IP?')">✕</a></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="5" style="text-align:center;padding:16px;color:#64748b">No IPs added yet.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <div class="set-card">
      <h4><i class="bi bi-activity"></i> Server Snapshot</h4>
      <div class="set-kv">
        <span class="k">Hostname</span><span class="v"><?php echo htmlspecialchars(gethostname() ?: '—'); ?></span>
        <span class="k">OS</span><span class="v"><?php echo htmlspecialchars(PHP_OS); ?></span>
        <span class="k">PHP</span><span class="v"><?php echo htmlspecialchars(PHP_VERSION); ?></span>
        <span class="k">Panel Time</span><span class="v"><?php echo htmlspecialchars(date('Y-m-d H:i:s T')); ?></span>
        <span class="k">Uptime</span><span class="v"><?php
          $up = @shell_exec("uptime -p 2>/dev/null");
          echo htmlspecialchars(trim((string)$up) ?: '—');
        ?></span>
        <span class="k">Load</span><span class="v"><?php echo htmlspecialchars(implode(' ', sys_getloadavg() ? array_map(fn($l) => round($l, 2), sys_getloadavg()) : [])); ?></span>
      </div>
    </div>
    <div class="set-card">
      <h4><i class="bi bi-lightbulb"></i> Tips</h4>
      <div class="set-note">
        <b>Timezone</b> affects all timestamps shown in the panel and emails.<br>
        <b>Language</b> sets the default for new users.<br>
        <b>Server IPs</b> are offered to accounts and streaming stations for dedicated-IP provisioning.
      </div>
    </div>
  </div>
</div>
</div>
