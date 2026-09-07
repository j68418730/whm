<?php $currentTab = 'localization'; require __DIR__ . '/_tabs.php'; ?>
<div class="set-wrap">
<div class="set-head">
  <h2>🌐 Localization</h2>
  <p>Language, currency, date/time formats and timezone.</p>
</div>

<form class="set-form" method="POST" action="/admin/settings/localization/save">
<div class="set-grid">
  <div>
    <div class="set-card">
      <h4><i class="bi bi-translate"></i> Language &amp; Currency</h4>
      <div class="set-2col">
        <div class="form-group">
          <label>Language</label>
          <select name="language">
            <option value="en" <?php echo $language==='en'?'selected':''; ?>>🇺🇸 English</option>
            <option value="es" <?php echo $language==='es'?'selected':''; ?>>🇪🇸 Spanish</option>
            <option value="fr" <?php echo $language==='fr'?'selected':''; ?>>🇫🇷 French</option>
            <option value="de" <?php echo $language==='de'?'selected':''; ?>>🇩🇪 German</option>
          </select>
        </div>
        <div class="form-group">
          <label>Timezone</label>
          <select name="timezone">
            <?php foreach (timezone_identifiers_list() as $tz): ?>
            <option value="<?php echo $tz; ?>" <?php echo $tz===$timezone?'selected':''; ?>><?php echo $tz; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="set-2col">
        <div class="form-group">
          <label>Currency</label>
          <select name="currency">
            <option value="USD" <?php echo $currency==='USD'?'selected':''; ?>>USD — US Dollar</option>
            <option value="EUR" <?php echo $currency==='EUR'?'selected':''; ?>>EUR — Euro</option>
            <option value="GBP" <?php echo $currency==='GBP'?'selected':''; ?>>GBP — British Pound</option>
            <option value="CAD" <?php echo $currency==='CAD'?'selected':''; ?>>CAD — Canadian Dollar</option>
          </select>
        </div>
        <div class="form-group">
          <label>Symbol</label>
          <input name="currency_symbol" value="<?php echo htmlspecialchars($currency_symbol); ?>" maxlength="4">
        </div>
      </div>
    </div>

    <div class="set-card">
      <h4><i class="bi bi-calendar3"></i> Date &amp; Time Format</h4>
      <div class="set-2col">
        <div class="form-group">
          <label>Date Format</label>
          <input name="date_format" id="loc-df" value="<?php echo htmlspecialchars($date_format); ?>" placeholder="Y-m-d">
          <small>PHP date() syntax — e.g. Y-m-d, d/m/Y, M j, Y</small>
        </div>
        <div class="form-group">
          <label>Time Format</label>
          <input name="time_format" id="loc-tf" value="<?php echo htmlspecialchars($time_format); ?>" placeholder="H:i:s">
          <small>e.g. H:i, H:i:s, g:i A</small>
        </div>
      </div>
      <div class="set-note">
        Preview: <b id="loc-preview" style="color:#38bdf8;font-family:ui-monospace,monospace"></b>
      </div>
    </div>

    <div class="set-actions">
      <button type="submit" class="btn set-btn-save">💾 Save Changes</button>
      <a href="/admin/settings" class="btn set-btn-ghost">Back</a>
    </div>
  </div>

  <div>
    <div class="set-card">
      <h4><i class="bi bi-info-circle"></i> Live Preview</h4>
      <div class="set-kv">
        <span class="k">Language</span><span class="v"><?php
          $langs = ['en' => '🇺🇸 English', 'es' => '🇪🇸 Spanish', 'fr' => '🇫🇷 French', 'de' => '🇩🇪 German'];
          echo htmlspecialchars($langs[$language] ?? $language);
        ?></span>
        <span class="k">Currency</span><span class="v"><?php echo htmlspecialchars($currency_symbol . ' 1,234.' . ($currency === 'USD' ? '56' : '00') . ' ' . $currency); ?></span>
        <span class="k">Timezone</span><span class="v"><?php echo htmlspecialchars($timezone); ?></span>
        <span class="k">Now (this TZ)</span><span class="v"><?php try { $dt = new DateTime('now', new DateTimeZone($timezone)); echo htmlspecialchars($dt->format($date_format . ' ' . $time_format)); } catch (\Throwable $e) { echo '—'; } ?></span>
      </div>
    </div>
    <div class="set-card">
      <h4><i class="bi bi-lightbulb"></i> Common Formats</h4>
      <div class="set-note">
        <b>Y-m-d</b> → 2026-09-06 &nbsp;·&nbsp; <b>d/m/Y</b> → 06/09/2026 &nbsp;·&nbsp; <b>M j, Y</b> → Sep 6, 2026<br>
        <b>H:i</b> → 23:50 &nbsp;·&nbsp; <b>g:i A</b> → 11:50 PM
      </div>
    </div>
  </div>
</div>
</form>
<script>
(function(){
  var df=document.getElementById('loc-df'),tf=document.getElementById('loc-tf'),pv=document.getElementById('loc-preview');
  function upd(){
    if(!df||!tf||!pv)return;
    try{pv.textContent=new Intl.DateTimeFormat('en',{timeZone:'UTC'}).format(new Date())+' — see Save result';}catch(e){}
  }
  df&&df.addEventListener('input',upd);tf&&tf.addEventListener('input',upd);upd();
})();
</script>
</div>
