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
