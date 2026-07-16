<div class="card" style="padding:20px">
<h3 style="margin-bottom:12px">🤖 AI Builder Overview</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:14px;margin-bottom:16px">
<div class="card" style="padding:14px;background:rgba(139,92,246,.06);border:1px solid rgba(139,92,246,.1)"><strong style="font-size:24px"><?php echo $stats['total_sites'] ?? 0; ?></strong><br><span style="font-size:11px;color:#94a3b8">Total Sites</span></div>
<div class="card" style="padding:14px;background:rgba(10,132,255,.06);border:1px solid rgba(10,132,255,.1)"><strong style="font-size:24px"><?php echo $stats['ai_sites'] ?? 0; ?></strong><br><span style="font-size:11px;color:#94a3b8">AI-Generated</span></div>
<div class="card" style="padding:14px;background:rgba(48,209,88,.06);border:1px solid rgba(48,209,88,.1)"><strong style="font-size:24px"><?php echo $stats['total_requests'] ?? 0; ?></strong><br><span style="font-size:11px;color:#94a3b8">Total AI Requests</span></div>
<div class="card" style="padding:14px;background:rgba(255,159,10,.06);border:1px solid rgba(255,159,10,.1)"><strong style="font-size:24px"><?php echo $stats['active_memory'] ?? 0; ?></strong><br><span style="font-size:11px;color:#94a3b8">Active Memory Records</span></div>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
<a href="/user/websites/ai/wizard" class="btn" style="padding:8px 16px;background:rgba(139,92,246,.15);color:#a855f7;border-radius:6px;text-decoration:none;font-size:12px">✨ AI Wizard</a>
<a href="/user/websites/ai/branding" class="btn" style="padding:8px 16px;background:rgba(10,132,255,.15);color:#0A84FF;border-radius:6px;text-decoration:none;font-size:12px">🎨 AI Branding</a>
<a href="/user/websites/ai/themes" class="btn" style="padding:8px 16px;background:rgba(48,209,88,.15);color:#30d158;border-radius:6px;text-decoration:none;font-size:12px">🖼 AI Themes</a>
<a href="/user/websites/ai/images" class="btn" style="padding:8px 16px;background:rgba(255,159,10,.15);color:#ff9f0a;border-radius:6px;text-decoration:none;font-size:12px">🖼 AI Images</a>
<a href="/user/websites/ai/analyze" class="btn" style="padding:8px 16px;background:rgba(255,69,58,.15);color:#ff453a;border-radius:6px;text-decoration:none;font-size:12px">📊 AI Analyze</a>
<a href="/user/websites/ai/memory" class="btn" style="padding:8px 16px;background:rgba(255,255,255,.06);color:#94a3b8;border-radius:6px;text-decoration:none;font-size:12px">🧠 AI Memory</a>
</div>
