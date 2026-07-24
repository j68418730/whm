<style>
.ai-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px}
.ai-card{background:rgba(8,16,28,.85);border:1px solid rgba(139,92,246,.15);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.ai-card:hover{transform:translateY(-3px);border-color:rgba(139,92,246,.35);box-shadow:0 8px 30px rgba(139,92,246,.1)}
.ai-card .icon{font-size:36px;margin-bottom:8px}
.ai-card .name{font-size:14px;font-weight:600}
.ai-card .desc{font-size:11px;color:#94a3b8;margin-top:4px}
.ai-card.purple{border-color:rgba(139,92,246,.2)}
.ai-card.blue{border-color:rgba(10,132,255,.2)}
.ai-card.green{border-color:rgba(48,209,88,.2)}
.ai-card.orange{border-color:rgba(255,159,10,.2)}
</style>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0;font-size:18px"><i class="bi bi-robot" style="color:#8b5cf6"></i> AI Website Builder</h3>
<p style="color:#64748b;font-size:12px;margin:4px 0 0">Generate, edit, and improve websites with natural language.</p></div>
<a href="/user/websites" class="btn btn-sm secondary" style="font-size:11px"><i class="bi bi-arrow-left"></i> Back to Websites</a>
</div>
<div class="ai-grid">
<a href="/user/websites/ai/wizard" class="ai-card purple"><div class="icon">🎯</div><div class="name">AI Wizard</div><div class="desc">Answer questions & AI builds your site</div></a>
<a href="/user/websites/ai/branding" class="ai-card blue"><div class="icon">🎨</div><div class="name">AI Branding</div><div class="desc">Colors, fonts & logo suggestions</div></a>
<a href="/user/websites/ai/themes" class="ai-card purple"><div class="icon">🌈</div><div class="name">AI Themes</div><div class="desc">Generate themes automatically</div></a>
<a href="/user/websites/ai/images" class="ai-card green"><div class="icon">🖼️</div><div class="name">AI Images</div><div class="desc">Generate images with AI</div></a>
<a href="/user/websites/ai/analyze" class="ai-card orange"><div class="icon">🔍</div><div class="name">Site Analysis</div><div class="desc">Analyze & improve any website</div></a>
<a href="/user/websites/ai/memory" class="ai-card blue"><div class="icon">🧠</div><div class="name">AI Memory</div><div class="desc">Builder remembers your project</div></a>
<a href="/user/websites/ai/build-settings" class="ai-card orange"><div class="icon">⚙️</div><div class="name">Build Settings</div><div class="desc">Directory, subdomain & install options</div></a>
</div>
<?php if (!empty($sites)): ?>
<div class="card" style="margin-top:16px">
<h4 style="margin-bottom:10px">Your Sites <span style="font-size:12px;color:#64748b;font-weight:400">(<?php echo count($sites); ?>)</span></h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px">
<?php foreach ($sites as $s): ?>
<div style="background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;display:flex;justify-content:space-between;align-items:center">
<div><strong style="font-size:13px"><?php echo htmlspecialchars($s->name); ?></strong><br><span style="font-size:10px;color:#64748b"><?php echo $s->status ?? 'draft'; ?></span></div>
<div style="display:flex;gap:4px">
<a href="/user/websites/<?php echo $s->id; ?>" class="btn btn-sm primary" style="font-size:10px;padding:4px 8px">Manage</a>
<a href="/user/websites/ai/edit/<?php echo $s->id; ?>" class="btn btn-sm secondary" style="font-size:10px;padding:4px 8px">AI</a>
</div>
</div>
<?php endforeach; ?>
</div></div>
<?php endif; ?>
