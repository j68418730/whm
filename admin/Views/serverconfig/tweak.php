<?php
$categories = $categories ?? [];
$settings = $settings ?? [];
$cat = $cat ?? 'all';
$q = $q ?? '';
$filter = $filter ?? 'all';
$score = $score ?? ['score' => 0, 'checks' => []];
$history = $history ?? [];
$thisCat = $thisCat ?? null;
$importPreview = $_SESSION['tweaks_import_preview'] ?? null;
$profilePreview = $_SESSION['tweaks_profile_preview'] ?? null;
$highRiskPending = $_SESSION['tweaks_high_risk'] ?? null;
unset($_SESSION['tweaks_import_preview'], $_SESSION['tweaks_profile_preview'], $_SESSION['tweaks_high_risk']);
?>
<style>
.tw-wrap{max-width:1300px;margin:0 auto}
.tw-top{display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:16px;align-items:stretch}
@media(max-width:900px){.tw-top{grid-template-columns:1fr}}
.tw-searchbar{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:14px 16px}
.tw-searchbar .row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.tw-searchbar input[type=text]{flex:1;min-width:180px;padding:9px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none}
.tw-searchbar select{padding:8px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none}
.tw-searchbar select option{background:#0a0e1a;color:#e0e0e0}
.tw-searchbar .chips{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.tw-chip{display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);color:#94a3b8;text-decoration:none}
.tw-chip:hover{border-color:rgba(0,140,255,.3);color:#38bdf8}
.tw-chip.act{background:rgba(0,140,255,.15);border-color:rgba(0,140,255,.35);color:#0A84FF}
.tw-score{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:14px 16px;display:flex;flex-direction:column}
.tw-score .big{font-size:32px;font-weight:800;color:#4ade80;line-height:1}
.tw-score .lbl{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:1px}
.tw-score .mini{display:flex;flex-wrap:wrap;gap:4px;margin-top:8px}
.tw-score .mini span{font-size:9px;padding:2px 7px;border-radius:99px}
.tw-ok{background:rgba(74,222,128,.12);color:#4ade80}
.tw-bad{background:rgba(248,113,113,.12);color:#f87171}
.tw-cats{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px}
.tw-cat{display:inline-flex;align-items:center;gap:5px;padding:6px 11px;border-radius:99px;font-size:11px;font-weight:600;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);color:#94a3b8;text-decoration:none;transition:.15s}
.tw-cat:hover{border-color:rgba(0,140,255,.3);color:#38bdf8}
.tw-cat.act{background:rgba(0,140,255,.15);border-color:rgba(0,140,255,.35);color:#0A84FF}
.tw-cat .n{opacity:.6;font-weight:400}
.tw-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px}
.tw-head h3{margin:0;font-size:16px;color:#e0e0e0;display:flex;gap:8px;align-items:center}
.tw-tools{display:flex;gap:6px;flex-wrap:wrap}
.tw-tools a,.tw-tools button{padding:7px 12px;border-radius:8px;font-size:11px;font-weight:600;text-decoration:none;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:#94a3b8;cursor:pointer;transition:.15s}
.tw-tools a:hover,.tw-tools button:hover{border-color:rgba(0,140,255,.3);color:#38bdf8}
.tw-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:14px 16px;margin-bottom:10px}
.tw-row{display:grid;grid-template-columns:1fr 260px;gap:14px;align-items:start}
@media(max-width:800px){.tw-row{grid-template-columns:1fr}}
.tw-row .l label{font-size:13px;font-weight:600;color:#e0e0e0;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tw-badge{padding:2px 9px;border-radius:99px;font-size:9px;font-weight:800;letter-spacing:.5px;text-transform:uppercase}
.tw-b-def{background:rgba(148,163,184,.12);color:#94a3b8}
.tw-b-cust{background:rgba(56,189,248,.12);color:#38bdf8}
.tw-b-live{background:rgba(168,85,247,.12);color:#c084fc}
.tw-b-hr{background:rgba(248,113,113,.15);color:#f87171}
.tw-b-res{background:rgba(250,204,21,.12);color:#facc15}
.tw-b-rec{background:rgba(74,222,128,.12);color:#4ade80}
.tw-row .l .desc{font-size:11px;color:#64748b;margin-top:4px;line-height:1.5}
.tw-row .l .meta{display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;font-size:10px;color:#64748b}
.tw-row .l .meta b{color:#94a3b8;font-weight:600}
.tw-ctl{display:flex;flex-direction:column;gap:8px}
.tw-ctl input[type=text],.tw-ctl input[type=number],.tw-ctl select,.tw-ctl textarea{width:100%;padding:7px 10px;border-radius:7px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none;box-sizing:border-box}
.tw-ctl select option{background:#0a0e1a;color:#e0e0e0}
.tw-ctl input[type=checkbox]{transform:scale(1.3);accent-color:#008cff}
.tw-ctl .btns{display:flex;gap:5px}
.tw-ctl .btns button,.tw-ctl .btns a{padding:5px 10px;border-radius:6px;font-size:10px;font-weight:700;cursor:pointer;text-decoration:none;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.05);color:#94a3b8}
.tw-ctl .btns .sv{background:rgba(0,140,255,.15);color:#0A84FF;border-color:rgba(0,140,255,.3)}
.tw-ctl .btns .rs:hover{color:#facc15;border-color:rgba(250,204,21,.3)}
.tw-cmp{font-size:9px;color:#64748b;margin-top:2px}
.tw-toggle-lab{display:flex;align-items:center;gap:10px;cursor:pointer;font-size:12px;color:#cbd5e1}
.tw-hist{font-size:11px}
.tw-hist table{width:100%;border-collapse:collapse}
.tw-hist td,.tw-hist th{padding:5px 8px;border-bottom:1px solid rgba(255,255,255,.04);text-align:left;color:#94a3b8}
.tw-hist .ok{color:#4ade80}.tw-hist .rolled_back{color:#facc15}
.tw-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;display:none;align-items:center;justify-content:center}
.tw-modal{background:#0d1524;border:1px solid rgba(248,113,113,.3);border-radius:12px;max-width:520px;width:92%;padding:22px}
.tw-modal h4{color:#f87171;margin:0 0 10px;font-size:16px}
.tw-modal p{color:#94a3b8;font-size:13px;line-height:1.6}
.tw-modal .btns{display:flex;gap:8px;justify-content:flex-end;margin-top:16px}
.tw-modal .btns button{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer}
.tw-warn{background:rgba(250,204,21,.08);border:1px solid rgba(250,204,21,.2);color:#facc15;border-radius:8px;padding:10px 14px;font-size:12px;margin-bottom:12px}
.tw-prev{background:rgba(0,140,255,.05);border:1px solid rgba(0,140,255,.2);border-radius:10px;padding:14px;margin-bottom:14px}
.tw-prev table{width:100%;border-collapse:collapse;font-size:12px}
.tw-prev td{padding:4px 8px;border-bottom:1px solid rgba(255,255,255,.05);color:#94a3b8}
.tw-prev .o{color:#f87171;text-decoration:line-through}
.tw-prev .n{color:#4ade80}
textarea{font-family:ui-monospace,monospace}
</style>

<div class="tw-wrap">
<div class="tw-top">
  <div class="tw-searchbar">
    <form method="GET" action="/admin/tweak">
      <input type="hidden" name="cat" value="all">
      <div class="row">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="🔍 Search settings — name, description, category, keyword (e.g. upload, gzip, ban)…">
        <select name="filter">
          <option value="all" <?php echo $filter==='all'?'selected':''; ?>>All</option>
          <option value="custom" <?php echo $filter==='custom'?'selected':''; ?>>Enabled/Custom</option>
          <option value="default" <?php echo $filter==='default'?'selected':''; ?>>Default</option>
          <option value="live" <?php echo $filter==='live'?'selected':''; ?>>Live value</option>
          <option value="requires_restart" <?php echo $filter==='requires_restart'?'selected':''; ?>>Requires Restart</option>
          <option value="high_risk" <?php echo $filter==='high_risk'?'selected':''; ?>>High Risk</option>
          <option value="security" <?php echo $filter==='security'?'selected':''; ?>>Security</option>
        </select>
        <button class="btn btn-sm set-btn-save" type="submit" style="padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;border:none;cursor:pointer;background:linear-gradient(135deg,#008cff,#0066cc);color:#fff">Search</button>
        <a href="/admin/tweak?cat=all" class="btn btn-sm" style="padding:8px 12px;border-radius:8px;font-size:12px;background:rgba(255,255,255,.06);color:#94a3b8;text-decoration:none">Clear</a>
      </div>
      <div class="chips">
        <a class="tw-chip <?php echo $cat==='all'?'act':''; ?>" href="/admin/tweak?cat=all<?php echo $q?'&q='.urlencode($q):''; ?>&filter=<?php echo $filter; ?>">🌐 All (<?php echo count(\Core\TweakEngine::settings()); ?>)</a>
        <a class="tw-chip" href="/admin/tweak?cat=all&filter=high_risk">⚠ High risk</a>
        <a class="tw-chip" href="/admin/tweak?cat=all&filter=requires_restart">🔁 Requires restart</a>
        <a class="tw-chip" href="/admin/tweak?cat=all&filter=custom">✏️ Customized</a>
        <a class="tw-chip" href="/admin/tweak?cat=all&filter=security">🔒 Security</a>
      </div>
    </form>
  </div>
  <div class="tw-score">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div><div class="big"><?php echo (int)$score['score']; ?> <span style="font-size:14px;color:#64748b">/100</span></div><div class="lbl">Security Score</div></div>
      <div class="mini">
        <?php foreach ($score['checks'] as $area => $ok): ?>
        <span class="<?php echo $ok ? 'tw-ok' : 'tw-bad'; ?>"><?php echo htmlspecialchars($area); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($importPreview): ?>
<div class="tw-prev">
  <h4 style="margin:0 0 8px;color:#38bdf8">📥 Import preview — <?php echo (int)$importPreview['total']; ?> change(s)</h4>
  <table>
    <tr><th>Setting</th><th>Current</th><th>Imported</th><th>Risk</th></tr>
    <?php foreach ($importPreview['changes'] as $ch): ?>
    <tr><td><?php echo htmlspecialchars($ch['key']); ?></td><td><span class="o"><?php echo htmlspecialchars((string)$ch['old']); ?></span></td><td><span class="n"><?php echo htmlspecialchars((string)$ch['new']); ?></span></td><td><?php echo htmlspecialchars($ch['risk']); ?></td></tr>
    <?php endforeach; ?>
  </table>
  <form method="POST" action="/admin/tweak/import" style="margin-top:10px;display:flex;gap:8px;align-items:center">
    <input type="hidden" name="tweaks_json" value="<?php echo htmlspecialchars($importPreview['json']); ?>">
    <label style="font-size:12px;color:#cbd5e1"><input type="checkbox" name="confirm" value="1" required> I have exported a backup of the current configuration and confirm these changes</label>
    <button type="submit" class="btn set-btn-save" style="padding:8px 16px;border-radius:8px;font-weight:600;border:none;cursor:pointer;background:linear-gradient(135deg,#008cff,#0066cc);color:#fff">Apply Import</button>
  </form>
</div>
<?php endif; ?>

<?php if ($profilePreview): ?>
<div class="tw-prev">
  <h4 style="margin:0 0 8px;color:#38bdf8">🧩 Profile preview — <?php echo htmlspecialchars($profilePreview['name']); ?></h4>
  <table>
    <tr><th>Setting</th><th>Current</th><th>Profile</th><th>Risk</th></tr>
    <?php foreach ($profilePreview['changes'] as $ch): ?>
    <tr><td><?php echo htmlspecialchars($ch['label']); ?></td><td><span class="o"><?php echo htmlspecialchars((string)($ch['old'] ?? '—')); ?></span></td><td><span class="n"><?php echo htmlspecialchars((string)$ch['new']); ?></span></td><td><?php echo htmlspecialchars($ch['risk']); ?></td></tr>
    <?php endforeach; ?>
  </table>
  <div style="margin-top:10px;display:flex;gap:8px">
    <a href="/admin/tweak/profile?id=<?php echo urlencode($profilePreview['id']); ?>&confirm=1" class="btn set-btn-save" style="padding:8px 16px;border-radius:8px;font-weight:600;text-decoration:none;background:linear-gradient(135deg,#008cff,#0066cc);color:#fff">Apply Profile</a>
    <a href="/admin/tweak?cat=all" style="padding:8px 16px;border-radius:8px;font-size:12px;background:rgba(255,255,255,.06);color:#94a3b8;text-decoration:none">Cancel</a>
  </div>
</div>
<?php endif; ?>

<?php if ($highRiskPending): ?>
<div class="tw-warn">⚠ <b>High-risk changes pending confirmation:</b> <?php echo htmlspecialchars(implode(', ', $highRiskPending)); ?> — tick “Confirm high-risk changes” at the bottom and save again. A config backup is taken automatically before applying.</div>
<?php endif; ?>

<div class="tw-cats">
<?php foreach ($categories as $cid => $cinfo): $n = count(array_filter(\Core\TweakEngine::settings(), fn($s) => $s['cat'] === $cid)); ?>
<a class="tw-cat <?php echo $cat === $cid ? 'act' : ''; ?>" href="/admin/tweak?cat=<?php echo $cid; ?>"><?php echo $cinfo['icon']; ?> <?php echo htmlspecialchars($cinfo['name']); ?> <span class="n"><?php echo $n; ?></span></a>
<?php endforeach; ?>
</div>

<?php if ($cat === 'all' && $q === '' && $filter === 'all'): ?>
<div class="tw-card">
  <div class="tw-head"><h3>🧩 Server Profiles</h3></div>
  <p style="font-size:12px;color:#64748b;margin:0 0 10px">Apply a curated configuration. Every profile shows its exact changes before applying.</p>
  <div style="display:flex;gap:6px;flex-wrap:wrap">
    <?php foreach (\Core\TweakEngine::profiles() as $pid => $p): ?>
    <a class="tw-chip" href="/admin/tweak/profile?id=<?php echo $pid; ?>">▸ <?php echo htmlspecialchars($p['name']); ?></a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="tw-card">
  <div class="tw-head">
    <h3><?php echo $thisCat ? ($thisCat['icon'] . ' ' . htmlspecialchars($thisCat['name'])) : ($q ? '🔎 Search: ' . htmlspecialchars($q) : '🌐 All settings'); ?></h3>
    <div class="tw-tools">
      <span style="font-size:11px;color:#64748b;align-self:center"><?php echo count($settings); ?> setting(s)</span>
      <a href="/admin/tweak/export">⬇ Export JSON</a>
      <button type="button" onclick="var el=document.getElementById('tw-import');el.style.display=el.style.display==='none'?'block':'none'">⬆ Import</button>
      <?php if ($cat !== 'all'): ?><a href="/admin/tweak?cat=all">Back to all</a><?php endif; ?>
    </div>
  </div>

  <div id="tw-import" style="display:none;margin-bottom:14px">
    <form method="POST" action="/admin/tweak/import">
      <textarea name="tweaks_json" rows="6" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#cbd5e1;font-size:11px" placeholder="Paste an exported planet-hosts-tweaks JSON here…"></textarea>
      <div style="display:flex;gap:8px;align-items:center;margin-top:8px">
        <label style="font-size:11px;color:#94a3b8"><input type="checkbox" name="confirm" value="1"> Validate + apply (preview is always shown first)</label>
        <button type="submit" class="btn set-btn-save" style="padding:7px 14px;border-radius:8px;font-weight:600;border:none;cursor:pointer;background:linear-gradient(135deg,#008cff,#0066cc);color:#fff">Preview Import</button>
      </div>
    </form>
  </div>

  <?php if (empty($settings)): ?>
  <p style="color:#64748b;font-size:13px;padding:20px;text-align:center">No settings match. Try another category or clear the search.</p>
  <?php endif; ?>

  <form method="POST" action="/admin/tweak" id="tw-form">
    <input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat); ?>">
    <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
    <?php foreach ($settings as $s): $def = $s['definition']; $val = (string)$s['value'];
      $isInfo = $def['type'] === 'info'; $isLink = $def['type'] === 'link';
    ?>
    <div class="tw-card" style="padding:12px 14px">
      <div class="tw-row">
        <div class="l">
          <label>
            <?php if ($def['state'] ?? false): ?>
            <?php else: ?><span class="tw-badge <?php echo $s['state']==='custom'?'tw-b-cust':($s['state']==='live'?'tw-b-live':'tw-b-def'); ?>"><?php echo $s['state']==='custom'?'CUSTOM':($s['state']==='live'?'LIVE':'DEFAULT'); ?></span><?php endif; ?>
            <?php echo htmlspecialchars($def['label']); ?>
            <?php if (($def['risk'] ?? '') === 'high'): ?><span class="tw-badge tw-b-hr">⚠ HIGH RISK</span><?php endif; ?>
            <?php if (!empty($def['restart'])): ?><span class="tw-badge tw-b-res">🔁 RESTART</span><?php endif; ?>
            <?php if (($def['recommended'] ?? '') !== '' && (string)$def['recommended'] === $val && $s['state']==='custom'): ?><span class="tw-badge tw-b-rec">RECOMMENDED</span><?php endif; ?>
          </label>
          <div class="desc"><?php echo htmlspecialchars($def['desc'] ?? ''); ?></div>
          <div class="meta">
            <?php if (!empty($def['applies'])): ?><span>Applies to: <b><?php echo htmlspecialchars($def['applies']); ?></b></span><?php endif; ?>
            <?php if (!empty($def['sec'])): ?><span>Security: <b><?php echo htmlspecialchars($def['sec']); ?></b></span><?php endif; ?>
            <?php if (!empty($def['perf'])): ?><span>Performance: <b><?php echo htmlspecialchars($def['perf']); ?></b></span><?php endif; ?>
            <?php if ($def['key'] !== 'fw.manage'): ?>
            <span>Default: <b><?php echo htmlspecialchars(is_array($def['default'] ?? null) ? '' : (string)($def['default'] ?? '—')); ?></b></span>
            <span>Recommended: <b><?php echo htmlspecialchars(is_array($def['recommended'] ?? null) ? '' : (string)($def['recommended'] ?? '—')); ?></b></span>
            <?php endif; ?>
          </div>
        </div>
        <div class="tw-ctl">
          <?php if ($isInfo): ?>
            <input type="text" value="<?php echo htmlspecialchars($val); ?>" readonly title="Live value — read-only">
          <?php elseif ($isLink): ?>
            <a href="<?php echo htmlspecialchars($def['link']); ?>" class="btn" style="padding:7px 12px;border-radius:7px;font-size:11px;text-decoration:none;background:rgba(0,140,255,.15);color:#0A84FF">Open module →</a>
          <?php elseif ($def['type'] === 'toggle'): ?>
            <label class="tw-toggle-lab"><input type="hidden" name="tweak[<?php echo $def['key']; ?>]" value="0"><input type="checkbox" name="tweak[<?php echo $def['key']; ?>]" value="1" <?php echo $val === '1' ? 'checked' : ''; ?>> <?php echo $val === '1' ? 'Enabled' : 'Disabled'; ?></label>
          <?php elseif ($def['type'] === 'select'): ?>
            <select name="tweak[<?php echo $def['key']; ?>]">
              <?php foreach (($def['options'] ?? []) as $ov => $ol): ?>
              <option value="<?php echo htmlspecialchars((string)$ov); ?>" <?php echo (string)$ov === $val ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$ol); ?></option>
              <?php endforeach; ?>
            </select>
          <?php elseif ($def['type'] === 'textarea'): ?>
            <textarea name="tweak[<?php echo $def['key']; ?>]" rows="3"><?php echo htmlspecialchars($val); ?></textarea>
          <?php else: ?>
            <input type="<?php echo $def['type'] === 'number' ? 'number' : 'text'; ?>" name="tweak[<?php echo $def['key']; ?>]" value="<?php echo htmlspecialchars($val); ?>" <?php echo $def['type'] === 'password' ? 'type="password" autocomplete="new-password"' : ''; ?>>
          <?php endif; ?>
          <?php if (!$isInfo && !$isLink): ?>
          <div class="btns">
            <button type="submit" name="save_one" value="<?php echo $def['key']; ?>" class="sv" onclick="twSaveOne(this)">SAVE</button>
            <a class="rs" href="/admin/tweak/reset?key=<?php echo urlencode($def['key']); ?>&cat=<?php echo urlencode($cat); ?>" onclick="return confirm('Reset “<?php echo htmlspecialchars($def['label']); ?>” to its default value?')">RESET</a>
          </div>
          <div class="tw-cmp">
            <?php $changed = $val !== (string)($def['default'] ?? '') && $s['state'] === 'custom'; ?>
            <?php if ($changed): ?>current <b style="color:#38bdf8"><?php echo htmlspecialchars($val); ?></b> → default <?php echo htmlspecialchars((string)($def['default'] ?? '')); ?>
            <?php else: ?>at default/recommended<?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if (count(array_filter($settings, fn($s) => !in_array($s['definition']['type'], ['info', 'link'])))): ?>
    <div class="tw-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
      <label style="font-size:12px;color:#cbd5e1;display:flex;align-items:center;gap:8px">
        <input type="checkbox" name="confirm_high_risk" value="1" style="accent-color:#f87171;transform:scale(1.2)"> Confirm high-risk changes (a configuration backup is taken automatically)
      </label>
      <div style="display:flex;gap:8px">
        <input name="reason" placeholder="Reason (recorded in history)" style="padding:8px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px">
        <button type="submit" class="btn set-btn-save" style="padding:9px 22px;border-radius:8px;font-weight:700;border:none;cursor:pointer;background:linear-gradient(135deg,#008cff,#0066cc);color:#fff">💾 Apply Changes</button>
      </div>
    </div>
    <?php endif; ?>
  </form>
</div>

<?php if ($cat === 'history' || ($cat === 'all' && $q === '' && $filter === 'all')): ?>
<div class="tw-card">
  <div class="tw-head"><h3>🕓 Configuration History</h3><span style="font-size:11px;color:#64748b">who changed what, when — full audit trail</span></div>
  <?php if (empty($history)): ?>
  <p style="color:#64748b;font-size:12px">No changes recorded yet.</p>
  <?php else: ?>
  <div class="tw-hist">
    <table>
      <tr><th>Time</th><th>Setting</th><th>Old</th><th>New</th><th>By</th><th>IP</th><th>Result</th></tr>
      <?php foreach ($history as $h): ?>
      <tr>
        <td><?php echo htmlspecialchars($h['created_at']); ?></td>
        <td><?php echo htmlspecialchars($h['setting_key']); ?></td>
        <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($h['old_value']); ?></td>
        <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis"><?php echo htmlspecialchars($h['new_value']); ?></td>
        <td><?php echo htmlspecialchars($h['admin_user'] ?: '—'); ?></td>
        <td><?php echo htmlspecialchars($h['ip_address']); ?></td>
        <td class="<?php echo htmlspecialchars($h['result']); ?>"><?php echo htmlspecialchars($h['result']); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
</div>

<div class="tw-modal-bg" id="tw-modal-bg">
  <div class="tw-modal">
    <h4>⚠ HIGH-RISK SETTING</h4>
    <p id="tw-modal-msg">Changing this setting may make the server or the panel inaccessible.</p>
    <div class="btns">
      <button style="background:rgba(255,255,255,.06);color:#94a3b8" onclick="twModalClose()">Cancel</button>
      <button id="tw-modal-continue" style="background:#f87171;color:#fff">Continue</button>
    </div>
  </div>
</div>

<script>
function twSaveOne(btn){
  var f = document.getElementById('tw-form');
  var input = document.createElement('input');
  input.type = 'hidden'; input.name = 'confirm_high_risk';
  var cb = f.querySelector('input[name=confirm_high_risk]');
  input.value = (cb && cb.checked) ? '1' : '';
  f.appendChild(input);
  var r = document.createElement('input');
  r.type = 'hidden'; r.name = 'reason'; r.value = 'Single setting save';
  f.appendChild(r);
  return true;
}
function twModalClose(){ document.getElementById('tw-modal-bg').style.display='none'; }
</script>
