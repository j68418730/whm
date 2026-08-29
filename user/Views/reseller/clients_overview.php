<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>Clients</h2>
<p style="color:#64748b;margin-bottom:20px">Manage your client accounts, staff, and billing. Everything here is scoped to your reseller — you never see other resellers' or admin data.</p>

<div class="section-grid">
<a href="/reseller/clients/create" class="section-card"><div class="icon">➕</div><div class="name">Create Client</div><div class="desc">New client account</div></a>
<a href="/reseller-clients" class="section-card"><div class="icon">👥</div><div class="count"><?php echo $total_clients; ?></div><div class="name">Clients</div><div class="desc">List and manage your clients</div></a>
<?php if (!empty($addons['billing'])): ?>
<a href="/reseller/billing-system" class="section-card"><div class="icon">💰</div><div class="count">$<?php echo number_format($total_outstanding,2); ?></div><div class="name">Billing System</div><div class="desc">Invoices, payments & credits for your clients</div></a>
<?php endif; ?>
<a href="/reseller/provisioning" class="section-card"><div class="icon">⚙️</div><div class="count"><?php echo $pending_clients; ?></div><div class="name">Provisioning</div><div class="desc">Pending activations & orders</div></a>
<?php if (!empty($addons['chat'])): ?>
<a href="/reseller/chat-system" class="section-card"><div class="icon">💬</div><div class="name">Chat System</div><div class="desc">Chat boxes for your clients</div></a>
<?php endif; ?>
<?php if (!empty($addons['support'])): ?>
<a href="/reseller/support-system" class="section-card"><div class="icon">🎧</div><div class="name">Support System</div><div class="desc">Client tickets, escalated to admin</div></a>
<?php endif; ?>
</div>