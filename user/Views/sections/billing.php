<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Billing</h2>
<p style="color:#64748b;margin-bottom:20px">Manage billing, invoices, and payment methods.</p>
<div class="section-grid">
<a href="/user/billing" class="section-card"><span class="icon">💰</span><div class="name">Billing Overview</div><div class="desc">Account balance & plans</div></a>
<a href="/user/invoices" class="section-card"><span class="icon">📄</span><div class="name">Invoices</div><div class="desc">View and pay invoices</div></a>
<a href="/user/payment-methods" class="section-card"><span class="icon">💳</span><div class="name">Payment Methods</div><div class="desc">Manage payment options</div></a>
<a href="/user/credits" class="section-card"><span class="icon">🎁</span><div class="name">Credits</div><div class="desc">View available credits</div></a>
</div>
