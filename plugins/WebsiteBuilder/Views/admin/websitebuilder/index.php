<h2 style="margin-bottom:16px">🌐 Website Builder</h2>

<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent);margin-bottom:12px">Choose a Template</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:16px">Select a template to generate your website. Customize the name and we'll build it instantly.</p>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px">
<?php foreach ($templates as $key => $tpl): ?>
<form method="POST" action="/admin/websitebuilder/generate" style="background:rgba(8,16,28,.5);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:20px;text-align:center;transition:.3s">
<input type="hidden" name="template" value="<?php echo $key; ?>">
<div style="font-size:36px;margin-bottom:8px"><?php echo $tpl['icon']; ?></div>
<h4 style="margin-bottom:4px"><?php echo htmlspecialchars($tpl['name']); ?></h4>
<p style="color:#64748b;font-size:11px;margin-bottom:12px"><?php echo htmlspecialchars($tpl['desc']); ?></p>
<input name="name" placeholder="Your Site Name" value="<?php echo htmlspecialchars($tpl['name']); ?>" style="width:100%;padding:8px;margin-bottom:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#fff;text-align:center;outline:none">
<button type="submit" class="btn btn-sm primary" style="width:100%">Generate Site</button>
</form>
<?php endforeach; ?>
</div>
</div>

<div class="card" style="border-color:rgba(74,222,128,.15)">
<h3 style="color:#4ade80;margin-bottom:8px">🤖 AI Website Generator</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:12px">Describe the website you want in plain English and AI will generate it instantly.</p>
<form method="POST" action="/admin/websitebuilder/ai-generate">
<textarea name="description" rows="4" placeholder="e.g. A modern landing page for a cloud hosting company with pricing cards, testimonials, and a contact form" style="width:100%;padding:10px 14px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:8px;color:#fff;outline:none;font-family:Inter,sans-serif;font-size:13px;resize:vertical" required></textarea>
<div style="display:flex;gap:8px;margin-top:10px;align-items:center">
<button type="submit" class="btn btn-sm" style="background:linear-gradient(135deg,#4ade80,#22c55e);color:#000;font-weight:700;border:none;padding:10px 24px;border-radius:8px;cursor:pointer">✨ Generate with AI</button>
<span style="font-size:11px;color:#64748b">Requires OpenAI API key in Settings</span>
</div>
</form>
</div>
