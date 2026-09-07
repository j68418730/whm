<?php $currentTab = 'company'; require __DIR__ . '/_tabs.php'; ?>
<div class="set-wrap">
<div class="set-head">
  <h2>🏢 Company Settings</h2>
  <p>Your brand identity — shown across the panel, invoices and client emails.</p>
</div>

<form class="set-form" method="POST" action="/admin/settings/company/save">
<div class="set-grid">
  <div>
    <div class="set-card">
      <h4><i class="bi bi-building"></i> Identity</h4>
      <div class="form-group">
        <label>Company Name</label>
        <input name="company_name" value="<?php echo htmlspecialchars($company_name); ?>" placeholder="Planet-Hosts">
      </div>
      <div class="set-2col">
        <div class="form-group">
          <label>Company Email</label>
          <input name="company_email" type="email" value="<?php echo htmlspecialchars($company_email); ?>" placeholder="admin@planet-hosts.com">
          <small>Attack alerts and system notifications go here.</small>
        </div>
        <div class="form-group">
          <label>Company Phone</label>
          <input name="company_phone" value="<?php echo htmlspecialchars($company_phone); ?>" placeholder="+1 (555) 000-0000">
        </div>
      </div>
      <div class="form-group">
        <label>Company Address</label>
        <textarea name="company_address" rows="3" placeholder="Street, City, State, ZIP, Country"><?php echo htmlspecialchars($company_address); ?></textarea>
      </div>
    </div>
  </div>

  <div>
    <div class="set-card">
      <h4><i class="bi bi-globe2"></i> Domains &amp; Web</h4>
      <div class="form-group">
        <label>Primary Domain</label>
        <input name="primary_domain" placeholder="planet-hosts.com" value="<?php echo htmlspecialchars($primary_domain); ?>">
        <small>Used for widget embed codes, stream URLs and panel links. Also set during the setup wizard.</small>
      </div>
      <div class="form-group">
        <label>Company Website</label>
        <input name="company_website" value="<?php echo htmlspecialchars($company_website); ?>" placeholder="https://planet-hosts.com">
      </div>
    </div>
    <div class="set-card">
      <h4><i class="bi bi-info-circle"></i> Where This Is Used</h4>
      <div class="set-kv">
        <span class="k">Primary Domain</span><span class="v">radio widgets · stream URLs · panel links · DJ panel link</span>
        <span class="k">Company Email</span><span class="v">attack alerts · system notifications</span>
        <span class="k">Company Name</span><span class="v">invoices · client emails · page titles</span>
        <span class="k">Address</span><span class="v">invoice footers · legal pages</span>
      </div>
      <div class="set-actions">
        <button type="submit" class="btn set-btn-save">💾 Save Changes</button>
        <a href="/admin/settings" class="btn set-btn-ghost">Back</a>
      </div>
    </div>
  </div>
</div>
</form>
</div>
