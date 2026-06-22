<style>
.settings-tabs{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:20px}
.settings-tab{display:flex;align-items:center;gap:6px;padding:10px 16px;border-radius:8px;border:1px solid rgba(255,255,255,.06);background:transparent;color:#94a3b8;text-decoration:none;font-size:13px;font-weight:500;transition:.2s;white-space:nowrap}
.settings-tab:hover{background:rgba(0,140,255,.06);border-color:rgba(0,191,255,.15);color:#e0e0e0}
.settings-tab.active{background:rgba(0,140,255,.1);border-color:rgba(0,140,255,.3);color:#fff}
.settings-tab .sicon{font-size:16px;line-height:1}
.settings-tab .sname{line-height:1}
@media(max-width:768px){.settings-tabs{flex-wrap:nowrap;overflow-x:auto;padding-bottom:8px}.settings-tab{flex-shrink:0}}
</style>
<div class="settings-tabs">
<a href="/admin/settings/general" class="settings-tab<?php echo ($currentTab ?? '') === 'general' ? ' active' : ''; ?>"><span class="sicon">⚙️</span><span class="sname">General</span></a>
<a href="/admin/settings/company" class="settings-tab<?php echo ($currentTab ?? '') === 'company' ? ' active' : ''; ?>"><span class="sicon">🏢</span><span class="sname">Company</span></a>
<a href="/admin/settings/smtp" class="settings-tab<?php echo ($currentTab ?? '') === 'smtp' ? ' active' : ''; ?>"><span class="sicon">📧</span><span class="sname">SMTP</span></a>
<a href="/admin/settings/security" class="settings-tab<?php echo ($currentTab ?? '') === 'security' ? ' active' : ''; ?>"><span class="sicon">🔒</span><span class="sname">Security</span></a>
<a href="/admin/settings/api" class="settings-tab<?php echo ($currentTab ?? '') === 'api' ? ' active' : ''; ?>"><span class="sicon">🔌</span><span class="sname">API</span></a>
<a href="/admin/settings/localization" class="settings-tab<?php echo ($currentTab ?? '') === 'localization' ? ' active' : ''; ?>"><span class="sicon">🌐</span><span class="sname">Localization</span></a>
</div>
