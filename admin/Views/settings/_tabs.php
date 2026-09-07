<style>
.settings-tabs{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px}
.settings-tab{display:flex;align-items:center;gap:6px;padding:10px 16px;border-radius:8px;border:1px solid rgba(255,255,255,.06);background:transparent;color:#94a3b8;text-decoration:none;font-size:13px;font-weight:500;transition:.2s;white-space:nowrap}
.settings-tab:hover{background:rgba(0,140,255,.06);border-color:rgba(0,191,255,.15);color:#e0e0e0}
.settings-tab.active{background:rgba(0,140,255,.1);border-color:rgba(0,140,255,.3);color:#fff}
.settings-tab .sicon{font-size:16px;line-height:1}
.settings-tab .sname{line-height:1}
@media(max-width:768px){.settings-tabs{flex-wrap:nowrap;overflow-x:auto;padding-bottom:8px}.settings-tab{flex-shrink:0}}
/* ---- Settings design system (shared by all tabs) ---- */
.set-wrap{max-width:1100px;margin:0 auto}
.set-head{margin-bottom:18px}
.set-head h2{margin:0;font-size:20px;color:#e0e0e0;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.set-head p{margin:4px 0 0;font-size:12px;color:#64748b}
.set-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start}
.set-grid-3{display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start}
@media(max-width:900px){.set-grid,.set-grid-3{grid-template-columns:1fr}}
.set-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:18px;margin-bottom:16px}
.set-card h4{margin:0 0 14px;font-size:14px;color:#e0e0e0;display:flex;align-items:center;gap:8px}
.set-card h4 .bi,.set-card h4 .sico{color:var(--accent,#008cff)}
.set-form .form-group{margin-bottom:12px}
.set-form label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;font-weight:600;margin-bottom:4px;display:block}
.set-form input,.set-form select,.set-form textarea{width:100%;padding:8px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;box-sizing:border-box}
.set-form input:focus,.set-form select:focus,.set-form textarea:focus{border-color:rgba(0,140,255,.4)}
.set-form select option{background:#0a0e1a;color:#e0e0e0}
.set-form small{display:block;font-size:10px;color:#64748b;margin-top:3px}
.set-form input[readonly]{opacity:.6}
.set-2col{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.set-3col{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
@media(max-width:640px){.set-3col{grid-template-columns:1fr 1fr}}
.set-toggle{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid rgba(255,255,255,.06);border-radius:8px;background:rgba(0,0,0,.2);font-size:12px;color:#cbd5e1;cursor:pointer;margin-bottom:10px;transition:.15s}
.set-toggle:hover{border-color:rgba(0,140,255,.2)}
.set-toggle input{width:auto;accent-color:#008cff;transform:scale(1.15)}
.set-toggle .tdesc{color:#64748b;font-size:11px;display:block;margin-top:2px}
.set-toggle input[type=hidden]+input[type=checkbox]{margin-left:0}
.set-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;padding-top:12px;border-top:1px solid rgba(255,255,255,.05)}
.set-actions .btn{padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;border:none;cursor:pointer}
.set-btn-save{background:linear-gradient(135deg,#008cff,#0066cc);color:#fff}
.set-btn-ghost{background:rgba(255,255,255,.06);color:#94a3b8;border:1px solid rgba(255,255,255,.08)!important}
.set-kv{display:grid;grid-template-columns:auto 1fr;gap:6px 12px;font-size:12px}
.set-kv .k{color:#64748b}
.set-kv .v{color:#cbd5e1;word-break:break-all}
.set-note{background:rgba(0,140,255,.06);border:1px solid rgba(0,140,255,.15);border-radius:8px;padding:10px 12px;font-size:11px;color:#94a3b8;line-height:1.6}
.set-note b{color:#38bdf8}
.set-alert-ok{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);color:#4ade80;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:14px}
.set-alert-err{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);color:#f87171;border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:14px}
</style>
<div class="settings-tabs">
<a href="/admin/settings/general" class="settings-tab<?php echo ($currentTab ?? '') === 'general' ? ' active' : ''; ?>"><span class="sicon">⚙️</span><span class="sname">General</span></a>
<a href="/admin/settings/company" class="settings-tab<?php echo ($currentTab ?? '') === 'company' ? ' active' : ''; ?>"><span class="sicon">🏢</span><span class="sname">Company</span></a>
<a href="/admin/settings/smtp" class="settings-tab<?php echo ($currentTab ?? '') === 'smtp' ? ' active' : ''; ?>"><span class="sicon">📧</span><span class="sname">SMTP</span></a>
<a href="/admin/settings/security" class="settings-tab<?php echo ($currentTab ?? '') === 'security' ? ' active' : ''; ?>"><span class="sicon">🔒</span><span class="sname">Security</span></a>
<a href="/admin/settings/api" class="settings-tab<?php echo ($currentTab ?? '') === 'api' ? ' active' : ''; ?>"><span class="sicon">🔌</span><span class="sname">API</span></a>
<a href="/admin/settings/localization" class="settings-tab<?php echo ($currentTab ?? '') === 'localization' ? ' active' : ''; ?>"><span class="sicon">🌐</span><span class="sname">Localization</span></a>
</div>
<?php if (isset($_SESSION['success_message'])): ?><div class="set-alert-ok"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="set-alert-err"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div><?php endif; ?>
