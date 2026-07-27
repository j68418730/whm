<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Live Chat</h2>
<p style="color:#64748b;margin-bottom:20px">Manage your live chat system and get the embed code for your website.</p>
<div class="section-grid">
<a href="/user/chat" class="section-card"><span class="icon">💬</span><div class="name">Dashboard</div><div class="desc">Chat overview & settings</div></a>
<a href="/chatbox/admin.php?action=operators" target="_blank" class="section-card"><span class="icon">👥</span><div class="name">Operators</div><div class="desc">Manage chat operators</div></a>
<a href="/chatbox/admin.php?action=departments" target="_blank" class="section-card"><span class="icon">🏢</span><div class="name">Departments</div><div class="desc">Chat departments</div></a>
<a href="/chatbox/admin.php?action=history" target="_blank" class="section-card"><span class="icon">📋</span><div class="name">History</div><div class="desc">Chat transcripts</div></a>
<a href="/chatbox/admin.php?action=widget" target="_blank" class="section-card"><span class="icon">🔌</span><div class="name">Widget</div><div class="desc">Embed code & settings</div></a>
</div>
