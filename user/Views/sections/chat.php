<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
.section-card .badge{display:inline-block;background:rgba(0,191,255,.15);color:#38bdf8;font-size:9px;padding:1px 6px;border-radius:4px;margin-left:4px;font-weight:600}
</style>
<h2>Messaging</h2>
<p style="color:#64748b;margin-bottom:20px">Send messages, manage your chat widget, and configure chat settings.</p>
<div class="section-grid">
<a href="/user/chatbox" class="section-card" style="border-color:rgba(0,140,255,.2);background:rgba(0,140,255,.05)"><span class="icon">💬</span><div class="name">Messages <span class="badge">New</span></div><div class="desc">Chat with other users in real-time</div></a>
<a href="/user/chat" class="section-card"><span class="icon">⚙️</span><div class="name">Widget Settings</div><div class="desc">Embed codes, rooms, and widget customization</div></a>
<a href="/chatbox/admin.php" target="_blank" class="section-card"><span class="icon">🎛️</span><div class="name">Admin Panel</div><div class="desc">Manage users, rooms, and moderation</div></a>
</div>
