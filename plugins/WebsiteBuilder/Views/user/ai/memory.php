<div style="max-width:700px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="margin:0;font-size:18px"><i class="bi bi-brain" style="color:#8b5cf6"></i> AI Project Memory</h3>
<a href="/user/websites/ai" class="btn btn-sm secondary" style="font-size:10px">Back</a>
</div>
<p style="color:#64748b;font-size:12px;margin-bottom:16px">AI remembers your brand preferences across sessions. Update them below.</p>
<form id="memoryForm" method="POST" action="/user/websites/ai/memory/save">
<?php foreach ($sites as $s): ?>
<div class="card" style="padding:16px;margin-bottom:10px">
<h4 style="font-size:13px;margin-bottom:8px"><?php echo htmlspecialchars($s->name); ?></h4>
<input type="hidden" name="site_id[]" value="<?php echo $s->id; ?>">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:8px">
<label style="font-size:11px;color:#94a3b8">Primary Color<br><input name="color_primary[]" type="color" value="<?php echo $context[$s->id]['brand_colors']['primary'] ?? '#0A84FF'; ?>" style="width:40px;height:40px;border-radius:6px;border:none;cursor:pointer"></label>
<label style="font-size:11px;color:#94a3b8">Secondary Color<br><input name="color_secondary[]" type="color" value="<?php echo $context[$s->id]['brand_colors']['secondary'] ?? '#4ade80'; ?>" style="width:40px;height:40px;border-radius:6px;border:none;cursor:pointer"></label>
</div>
<label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:6px">Writing Style<br>
<input name="writing_style[]" value="<?php echo htmlspecialchars($context[$s->id]['writing_style'] ?? ''); ?>" placeholder="e.g. Professional, Friendly, Playful" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px"></label>
<label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:6px">Target Audience<br>
<input name="target_audience[]" value="<?php echo htmlspecialchars($context[$s->id]['target_audience'] ?? ''); ?>" placeholder="e.g. Small business owners" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px"></label>
<label style="font-size:11px;color:#94a3b8;display:block;margin-bottom:6px">Keyboard Shortcut<br>
<input name="keyboard_shortcut[]" value="<?php echo htmlspecialchars($context[$s->id]['keyboard_shortcut'] ?? ''); ?>" placeholder="e.g. /ai (chat shortcut)" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px"></label>
</div>
<?php endforeach; ?>
<button type="submit" class="btn primary" style="font-size:12px;padding:8px 24px"><i class="bi bi-save"></i> Save Memory</button>
</form>
</div>
