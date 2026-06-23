<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Email</h2>
<p style="color:#64748b;margin-bottom:20px">Manage email accounts, forwarders, and spam settings.</p>
<div class="section-grid">
<a href="/user/email" class="section-card"><span class="icon">📧</span><div class="name">Email Accounts</div><div class="desc">Manage mailboxes</div></a>
<a href="/webmail_autologin.php" target="_blank" class="section-card"><span class="icon">🌐</span><div class="name">Webmail</div><div class="desc">Access webmail</div></a>
<a href="/user/forwarders" class="section-card"><span class="icon">↪️</span><div class="name">Forwarders</div><div class="desc">Email forwarding rules</div></a>
<a href="/user/autoresponders" class="section-card"><span class="icon">🤖</span><div class="name">Autoresponders</div><div class="desc">Auto-reply messages</div></a>
<a href="/user/spamfilters" class="section-card"><span class="icon">🛡️</span><div class="name">Spam Filters</div><div class="desc">Spam protection</div></a>
</div>
