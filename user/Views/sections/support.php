<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Support</h2>
<p style="color:#64748b;margin-bottom:20px">Get help, submit tickets, and access resources.</p>
<div class="section-grid">
<a href="/user/tickets" class="section-card"><span class="icon">🎫</span><div class="name">Create Ticket</div><div class="desc">Submit a support request</div></a>
<a href="/user/tickets" class="section-card"><span class="icon">📋</span><div class="name">My Tickets</div><div class="desc">View existing tickets</div></a>
<a href="/user/support" class="section-card"><span class="icon">📚</span><div class="name">Knowledge Base</div><div class="desc">Guides and articles</div></a>
<a href="/livechat" target="_blank" class="section-card"><span class="icon">💬</span><div class="name">Live Chat</div><div class="desc">Chat with support</div></a>
</div>
