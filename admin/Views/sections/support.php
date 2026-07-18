<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>Support</h2>
<p style="color:#64748b;margin-bottom:20px">Manage support tickets, live chat, knowledge base, and announcements.</p>

<div class="section-grid">
<a href="/admin/support" class="section-card"><div class="icon">🎯</div><div class="name">Support Center</div><div class="desc">Support dashboard</div></a>
<a href="/admin/support/tickets" class="section-card"><div class="icon">🎫</div><div class="name">Tickets</div><div class="desc">Support ticket system</div></a>
<a href="/admin/livechat" class="section-card"><div class="icon">💬</div><div class="name">Live Chat</div><div class="desc">Real-time chat support</div></a>
<a href="/admin/chat-dashboard" class="section-card"><div class="icon">📈</div><div class="name">Chat Dashboard</div><div class="desc">Chat analytics & logs</div></a>
<a href="/admin/support/kb" class="section-card"><div class="icon">📚</div><div class="name">Knowledge Base</div><div class="desc">Articles & documentation</div></a>
<a href="/admin/support/announcements" class="section-card"><div class="icon">📢</div><div class="name">Announcements</div><div class="desc">Service announcements</div></a>
<a href="/admin/reviews" class="section-card"><div class="icon">⭐</div><div class="name">Reviews</div><div class="desc">Customer reviews & ratings</div></a>
<a href="/admin/support/status" class="section-card"><div class="icon">🟢</div><div class="name">Server Status</div><div class="desc">Server uptime & status</div></a>
</div>

<div class="card" style="margin-top:20px">
<h3 style="margin-bottom:14px">⚙️ Support Settings</h3>
<form method="post" action="/admin/support/settings" style="display:flex;flex-direction:column;gap:12px">
<?php echo $csrfField ?? ''; ?>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
<input type="hidden" name="live_chat_enabled" value="0"><input type="checkbox" name="live_chat_enabled" value="1" <?php echo ($settings['live_chat_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>> 💬 Enable Live Chat
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
<input type="hidden" name="visitor_tracking_enabled" value="0"><input type="checkbox" name="visitor_tracking_enabled" value="1" <?php echo ($settings['visitor_tracking_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>> 📊 Enable Visitor Tracking
</label>
<button class="btn primary" style="align-self:start">Save Settings</button>
</form>
</div>

<div class="card" style="margin-top:12px">
<h3 style="margin-bottom:14px">💬 Live Chat Widget</h3>
<p style="font-size:11px;color:#64748b;margin-bottom:8px">Copy and paste this code into your website's HTML to enable live chat support.</p>
<div style="background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.06);border-radius:6px;padding:10px;position:relative">
<button onclick="var t=this.previousElementSibling;navigator.clipboard.writeText(t.textContent);this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',2000)" style="position:absolute;top:6px;right:6px;padding:2px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#94a3b8;font-size:10px;cursor:pointer">Copy</button>
<pre style="font-size:10px;color:#4ade80;margin:0;white-space:pre-wrap;word-break:break-all;font-family:monospace;line-height:1.6">&lt;!-- Planet Hosts Live Chat --&gt;
&lt;script&gt;
(function() {
    var s = document.createElement('script');
    s.src = 'https://planet-hosts.com/livechat.php?widget=1';
    s.async = true;
    document.body.appendChild(s);
})();
&lt;/script&gt;
&lt;!-- End Live Chat --&gt;</pre>
</div>
</div>

<div class="card" style="margin-top:12px">
<h3 style="margin-bottom:14px">📊 Visitor Tracking Code</h3>
<p style="font-size:11px;color:#64748b;margin-bottom:8px">Copy and paste this code into your website to track visitors and page views.</p>
<div style="background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.06);border-radius:6px;padding:10px;position:relative">
<button onclick="var t=this.previousElementSibling;navigator.clipboard.writeText(t.textContent);this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',2000)" style="position:absolute;top:6px;right:6px;padding:2px 8px;border-radius:4px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#94a3b8;font-size:10px;cursor:pointer">Copy</button>
<pre style="font-size:10px;color:#4ade80;margin:0;white-space:pre-wrap;word-break:break-all;font-family:monospace;line-height:1.6">&lt;!-- Planet Hosts Visitor Tracking --&gt;
&lt;script&gt;
(function() {
    var s = document.createElement('script');
    s.src = 'https://planet-hosts.com/track.php?id=SITE_ID';
    s.async = true;
    document.body.appendChild(s);
})();
&lt;/script&gt;
&lt;!-- End Tracking --&gt;</pre>
</div>
</div>

<div class="card" style="margin-top:12px">
<h3 style="margin-bottom:14px">🖼️ Support Images</h3>
<form method="post" action="/admin/support/upload-image" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap">
<div><label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px">Upload Image</label><input type="file" name="image" accept="image/*" required></div>
<button class="btn primary">Upload</button>
</form>
<?php if (!empty($images)): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-top:12px">
<?php foreach ($images as $img): ?>
<div style="text-align:center;padding:6px;background:rgba(0,0,0,.2);border-radius:6px">
<img src="/uploads/support/<?php echo rawurlencode($img); ?>" style="width:100%;height:80px;object-fit:cover;border-radius:4px">
<div style="font-size:10px;color:#64748b;margin-top:4px;word-break:break-all"><?php echo htmlspecialchars($img); ?></div>
<a href="/admin/support/delete-image/<?php echo rawurlencode($img); ?>" class="btn btn-sm danger" style="padding:2px 6px;font-size:9px;margin-top:4px" onclick="return confirm('Delete?')">Delete</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
