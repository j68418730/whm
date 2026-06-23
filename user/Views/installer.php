<style>
.app-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-bottom:20px}
.app-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.06);border-radius:12px;padding:16px;text-align:center;cursor:pointer;transition:.2s;text-decoration:none;color:#e0e0e0}
.app-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 25px rgba(0,140,255,.08)}
.app-card .icon{font-size:36px;margin-bottom:6px}
.app-card .name{font-size:12px;font-weight:600}
.app-card .desc{font-size:10px;color:#64748b;margin-top:2px}
.app-card .badge-install{position:absolute;top:6px;right:6px;background:rgba(74,222,128,.15);color:#4ade80;font-size:9px;padding:2px 6px;border-radius:4px}
</style>

<h2>📦 Quick Install</h2>
<p style="color:#64748b;margin-bottom:16px">Install popular applications on your website with one click.</p>

<div class="app-grid" id="appGrid"></div>

<div id="installModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.8);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
<div class="card" style="max-width:420px;width:92%;padding:24px">
<h3 id="modalTitle" style="color:var(--accent);margin-bottom:14px">Install App</h3>
<p style="font-size:13px;color:#64748b;margin-bottom:14px">The app will be installed to your <code>public_html</code> directory.</p>
<form id="installForm" method="POST" action="/user/installer/install">
<input type="hidden" name="app_name" id="installAppName">
<div class="form-group"><label style="font-size:12px;color:#64748b">Install Directory</label>
<input name="directory" value="public_html" placeholder="public_html" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none"></div>
<button type="submit" class="btn btn-primary" style="width:100%;padding:10px;margin-top:8px;justify-content:center">📥 Install Now</button>
<button type="button" class="btn btn-secondary" style="width:100%;padding:8px;margin-top:6px" onclick="closeModal()">Cancel</button>
</form>
</div></div>

<script>
var apps = [
    {name:'WordPress', icon:'📝', desc:'Blog & CMS'},
    {name:'Joomla', icon:'🌐', desc:'Portal & CMS'},
    {name:'Drupal', icon:'📰', desc:'Enterprise CMS'},
    {name:'phpMyAdmin', icon:'🐘', desc:'Database Manager'},
    {name:'Nextcloud', icon:'☁️', desc:'Cloud Storage'},
    {name:'MediaWiki', icon:'📚', desc:'Wiki Platform'},
    {name:'Moodle', icon:'🎓', desc:'LMS Platform'},
    {name:'PrestaShop', icon:'🛒', desc:'E-Commerce'},
    {name:'phpBB', icon:'🗣️', desc:'Forum Software'},
    {name:'Laravel', icon:'⚡', desc:'PHP Framework'},
    {name:'Matomo', icon:'📊', desc:'Analytics'},
    {name:'osTicket', icon:'🎫', desc:'Support System'},
    {name:'SuiteCRM', icon:'🤝', desc:'CRM Platform'},
    {name:'Dolibarr', icon:'💼', desc:'ERP System'},
    {name:'Elgg', icon:'👥', desc:'Social Network'},
    {name:'Kanboard', icon:'📋', desc:'Project Mgmt'},
    {name:'Piwigo', icon:'🖼️', desc:'Gallery'},
    {name:'Ampache', icon:'🎵', desc:'Music Stream'},
    {name:'ClipBucket', icon:'🎬', desc:'Video Sharing'},
    {name:'FreshRSS', icon:'📡', desc:'RSS Reader'},
    {name:'HumHub', icon:'🏢', desc:'Social Network'},
    {name:'Lychee', icon:'📸', desc:'Photo Gallery'},
    {name:'FreeScout', icon:'💬', desc:'Help Desk'},
    {name:'EasyAppointments', icon:'📅', desc:'Booking System'},
    {name:'Castopod', icon:'🎙️', desc:'Podcasting'},
    {name:'eXtplorer', icon:'📁', desc:'File Manager'},
    {name:'EspoCRM', icon:'📇', desc:'CRM'},
    {name:'Vtiger', icon:'🔔', desc:'CRM'},
    {name:'FrontAccounting', icon:'💰', desc:'Accounting'},
    {name:'Collabtive', icon:'👨‍💻', desc:'Team Work'},
    {name:'MonstaFTP', icon:'📤', desc:'Web FTP'},
];

var grid = document.getElementById('appGrid');
apps.forEach(function(a) {
    var card = document.createElement('a');
    card.href = '#';
    card.className = 'app-card';
    card.onclick = function(e) { e.preventDefault(); installApp(a.name); };
    card.innerHTML = '<div class="icon">' + a.icon + '</div><div class="name">' + a.name + '</div><div class="desc">' + a.desc + '</div>';
    grid.appendChild(card);
});

function installApp(name) {
    document.getElementById('installAppName').value = name;
    document.getElementById('modalTitle').textContent = '📥 Install ' + name;
    document.getElementById('installModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('installModal').style.display = 'none';
}

document.getElementById('installModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
