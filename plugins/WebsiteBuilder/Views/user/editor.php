<?php
$engine = new \Services\WebsiteBuilderEngine();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Editor: <?php echo htmlspecialchars($page->title); ?> - <?php echo htmlspecialchars($site->name); ?></title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--wb-primary:#008cff;--wb-secondary:#00e5ff;--wb-bg:#02050e;--wb-card-bg:rgba(8,16,28,.85);--wb-text:#fff;--wb-accent:#38bdf8;}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:var(--wb-bg);color:var(--wb-text);overflow:hidden;height:100vh;display:flex;flex-direction:column}
.editor-toolbar{display:flex;align-items:center;justify-content:space-between;padding:8px 16px;background:rgba(8,16,28,.98);border-bottom:1px solid rgba(0,191,255,.1);flex-shrink:0;z-index:100}
.editor-toolbar .brand{font-weight:700;font-size:16px;color:var(--accent)}
.editor-toolbar .actions{display:flex;gap:6px}
.editor-toolbar button,.editor-toolbar a{padding:6px 14px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;cursor:pointer;font-size:12px;text-decoration:none;transition:.2s}
.editor-toolbar button:hover,.editor-toolbar a:hover{background:rgba(0,191,255,.1)}
.editor-toolbar .btn-save{background:var(--accent);color:#000;font-weight:700;border-color:var(--accent)}
.editor-layout{display:flex;flex:1;overflow:hidden}
.editor-sidebar{width:260px;background:rgba(8,16,28,.95);border-right:1px solid rgba(0,191,255,.1);overflow-y:auto;flex-shrink:0}
.editor-sidebar .block-category{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.06)}
.editor-sidebar .block-category h5{font-size:10px;text-transform:uppercase;color:var(--text_muted);letter-spacing:1px;margin-bottom:6px}
.editor-sidebar .block-item{padding:6px 10px;margin-bottom:2px;border-radius:6px;cursor:grab;font-size:12px;display:flex;align-items:center;gap:8px;transition:.2s;color:#94a3b8}
.editor-sidebar .block-item:hover{background:rgba(0,191,255,.08);color:#fff}
.editor-main{flex:1;overflow-y:auto;padding:20px;background:#0a0e1a}
.editor-canvas{max-width:1000px;margin:0 auto;min-height:600px;background:var(--wb-bg);border-radius:12px;border:1px solid rgba(0,191,255,.1)}
.editor-canvas .wb-zone{padding:8px;position:relative;min-height:40px;transition:.2s}
.editor-canvas .wb-zone:hover{background:rgba(0,191,255,.03)}
.editor-block{position:relative;padding:8px;margin:4px 0;border-radius:8px;border:1px solid transparent;transition:.2s}
.editor-block:hover{border-color:rgba(0,191,255,.2);background:rgba(0,191,255,.03)}
.editor-block.selected{border-color:var(--accent);background:rgba(0,191,255,.08)}
.editor-block .block-controls{position:absolute;top:-12px;right:8px;display:none;gap:4px;z-index:10}
.editor-block:hover .block-controls,.editor-block.selected .block-controls{display:flex}
.editor-block .block-controls button{width:24px;height:24px;border-radius:4px;border:none;background:var(--accent);color:#000;font-size:10px;cursor:pointer}
.editor-block .block-controls .btn-del{background:#ef4444;color:#fff}
.editor-right-panel{width:320px;background:rgba(8,16,28,.95);border-left:1px solid rgba(0,191,255,.1);overflow-y:auto;flex-shrink:0;padding:16px}
.editor-right-panel h5{font-size:11px;text-transform:uppercase;color:var(--text_muted);margin-bottom:12px;letter-spacing:1px}
.editor-right-panel .field{margin-bottom:12px}
.editor-right-panel .field label{display:block;font-size:11px;color:#94a3b8;margin-bottom:4px}
.editor-right-panel .field input,.editor-right-panel .field textarea,.editor-right-panel .field select{width:100%;padding:8px 10px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.1);border-radius:6px;color:#fff;font-size:12px}
.empty-zone{text-align:center;padding:20px;color:#64748b;font-size:12px;border:1px dashed rgba(255,255,255,.06);border-radius:8px}
@media(max-width:1024px){.editor-sidebar,.editor-right-panel{width:200px}}
</style>
</head>
<body>
<div class="editor-toolbar">
<div class="brand"><i class="bi bi-pencil-square"></i> <?php echo htmlspecialchars($page->title); ?></div>
<div class="actions">
<button onclick="savePage()" class="btn-save"><i class="bi bi-check-lg"></i> Save</button>
<a href="/user/websites/<?php echo $site->id; ?>/preview/<?php echo $page->id; ?>" target="_blank"><i class="bi bi-eye"></i> Preview</a>
<a href="/user/websites/<?php echo $site->id; ?>" style="color:#f87171"><i class="bi bi-x-lg"></i> Close</a>
</div>
</div>
<div class="editor-layout">
<div class="editor-sidebar">
<?php $cats = ['structure'=>'Structure','content'=>'Content','media'=>'Media','components'=>'Components','integrations'=>'Integrations','advanced'=>'Advanced'];
foreach ($cats as $catKey => $catName):
if (!isset($categorized[$catKey])) continue;
?>
<div class="block-category">
<h5><?php echo $catName; ?></h5>
<?php foreach ($categorized[$catKey] as $bk => $bv): ?>
<div class="block-item" draggable="true" data-type="<?php echo $bk; ?>" data-fields='<?php echo htmlspecialchars(json_encode($bv['fields'] ?? [])); ?>'>
<i class="<?php echo $bv['icon'] ?? 'fa-solid fa-cube'; ?>" style="width:16px;text-align:center;color:var(--accent)"></i>
<?php echo htmlspecialchars($bv['name'] ?? $bk); ?>
</div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>

<div class="editor-main">
<div class="editor-canvas" id="editorCanvas">
<?php
$zones = ['header', 'content', 'footer'];
foreach ($zones as $zone):
$zoneBlocks = array_filter($page->blocks, fn($b) => ($b->zone ?? 'content') === $zone);
?>
<div class="wb-zone" data-zone="<?php echo $zone; ?>">
<?php if ($zone === 'content' || !empty($zoneBlocks)): ?>
<?php if (empty($zoneBlocks)): ?>
<div class="empty-zone">Drop blocks here</div>
<?php endif; ?>
<?php foreach ($zoneBlocks as $b): ?>
<div class="editor-block" data-id="<?php echo $b->id; ?>" data-type="<?php echo $b->type; ?>" data-content='<?php echo htmlspecialchars(json_encode($b->content)); ?>' data-settings='<?php echo htmlspecialchars(json_encode($b->settings_arr ?? [])); ?>'>
<div class="block-controls">
<button onclick="moveBlock(this, -1)" title="Move up"><i class="bi bi-chevron-up"></i></button>
<button onclick="moveBlock(this, 1)" title="Move down"><i class="bi bi-chevron-down"></i></button>
<button class="btn-del" onclick="deleteBlock(this)" title="Delete"><i class="bi bi-trash"></i></button>
</div>
<?php echo $engine->renderBlock($b); ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="editor-right-panel" id="settingsPanel">
<h5>Block Settings</h5>
<p style="color:#64748b;font-size:12px">Select a block to edit its settings.</p>
</div>
</div>

<script>
let selectedBlock = null;
let pageId = <?php echo $page->id; ?>;
let siteId = <?php echo $site->id; ?>;

// Drag and drop
document.querySelectorAll('.block-item').forEach(item => {
item.addEventListener('dragstart', e => {
e.dataTransfer.setData('text/plain', JSON.stringify({
type: item.dataset.type,
fields: JSON.parse(item.dataset.fields || '[]')
}));
});
});

document.querySelectorAll('.wb-zone').forEach(zone => {
zone.addEventListener('dragover', e => e.preventDefault());
zone.addEventListener('drop', e => {
e.preventDefault();
try {
const data = JSON.parse(e.dataTransfer.getData('text/plain'));
addBlock(zone, data.type, data.fields);
} catch(ex) {}
});
});

// Save
function savePage() {
const blocks = [];
document.querySelectorAll('.editor-block').forEach(el => {
const zone = el.closest('.wb-zone').dataset.zone;
blocks.push({
id: parseInt(el.dataset.id) || 0,
type: el.dataset.type,
content: JSON.parse(el.dataset.content || '{}'),
settings: JSON.parse(el.dataset.settings || '{}'),
zone: zone
});
});
const btn = event.target.closest('button');
if (btn) { btn.innerHTML = '<i class="bi bi-hourglass"></i> Saving...'; btn.disabled = true; }
const formData = new FormData();
formData.append('site_id', siteId);
formData.append('page_id', pageId);
formData.append('blocks', JSON.stringify(blocks));
fetch('/user/websites/' + siteId + '/save-page', { method: 'POST', body: formData })
.then(r => r.json())
.then(d => { if (btn) { btn.innerHTML = '<i class="bi bi-check-lg"></i> Saved!'; setTimeout(() => { btn.innerHTML = '<i class="bi bi-check-lg"></i> Save'; btn.disabled = false; }, 2000); } })
.catch(() => { if (btn) { btn.innerHTML = 'Error'; btn.disabled = false; } });
}

// Add block
function addBlock(zoneEl, type, fields) {
const block = document.createElement('div');
block.className = 'editor-block';
block.dataset.type = type;
block.dataset.content = '{}';
block.dataset.settings = '{}';
block.draggable = true;
block.innerHTML = '<div class="block-controls"><button onclick="moveBlock(this,-1)"><i class="bi bi-chevron-up"></i></button><button onclick="moveBlock(this,1)"><i class="bi bi-chevron-down"></i></button><button class="btn-del" onclick="deleteBlock(this)"><i class="bi bi-trash"></i></button></div><div style="padding:20px;text-align:center;color:var(--text_muted)"><i class="fa-solid fa-cube" style="font-size:24px;margin-bottom:8px;color:var(--accent)"></i><br>' + type + '</div>';
block.onclick = function(e) { if (!e.target.closest('.block-controls')) selectBlock(this); };
zoneEl.appendChild(block);
const empty = zoneEl.querySelector('.empty-zone');
if (empty) empty.remove();
selectBlock(block);
}

// Select block
function selectBlock(el) {
document.querySelectorAll('.editor-block').forEach(b => b.classList.remove('selected'));
if (el) { el.classList.add('selected'); selectedBlock = el; showSettings(el); }
}

// Show settings
function showSettings(el) {
const panel = document.getElementById('settingsPanel');
let html = '<h5>Block Settings</h5>';
html += '<div class="field"><label>Type</label><input value="' + el.dataset.type + '" readonly style="opacity:.6"></div>';
try {
const content = JSON.parse(el.dataset.content || '{}');
for (const [key, val] of Object.entries(content)) {
html += '<div class="field"><label>' + key.charAt(0).toUpperCase() + key.slice(1) + '</label>';
if (typeof val === 'string' && val.length > 80) {
html += '<textarea onchange="updateBlockContent(this,\'' + key + '\')" rows="3">' + htmlspecialchars(val) + '</textarea>';
} else if (Array.isArray(val)) {
html += '<textarea onchange="updateBlockContent(this,\'' + key + '\')" rows="2">' + htmlspecialchars(JSON.stringify(val)) + '</textarea>';
} else {
html += '<input value="' + htmlspecialchars(String(val)) + '" onchange="updateBlockContent(this,\'' + key + '\')">';
}
html += '</div>';
}
} catch(e) {}
html += '<button class="btn btn-sm btn-danger" style="width:100%;margin-top:8px" onclick="deleteBlock(selectedBlock.querySelector(\'.btn-del\'))"><i class="bi bi-trash"></i> Delete Block</button>';
panel.innerHTML = html;
}

function htmlspecialchars(s) {
const d = document.createElement('div');
d.textContent = s;
return d.innerHTML;
}

function updateBlockContent(input, key) {
if (!selectedBlock) return;
try {
const content = JSON.parse(selectedBlock.dataset.content || '{}');
content[key] = input.value;
selectedBlock.dataset.content = JSON.stringify(content);
} catch(e) {}
}

// Move block
function moveBlock(btn, dir) {
const block = btn.closest('.editor-block');
const zone = block.closest('.wb-zone');
const siblings = [...zone.querySelectorAll('.editor-block')];
const idx = siblings.indexOf(block);
const newIdx = idx + dir;
if (newIdx < 0 || newIdx >= siblings.length) return;
if (dir < 0) zone.insertBefore(block, siblings[newIdx]);
else zone.insertBefore(block, siblings[newIdx + 1]);
}

// Delete block
function deleteBlock(btn) {
if (!confirm('Delete this block?')) return;
const block = btn.closest('.editor-block');
const zone = block.closest('.wb-zone');
block.remove();
const remaining = zone.querySelectorAll('.editor-block');
if (remaining.length === 0) {
const empty = document.createElement('div');
empty.className = 'empty-zone';
empty.textContent = 'Drop blocks here';
zone.appendChild(empty);
}
document.getElementById('settingsPanel').innerHTML = '<h5>Block Settings</h5><p style="color:#64748b;font-size:12px">Select a block to edit its settings.</p>';
selectedBlock = null;
}

// Keyboard save
document.addEventListener('keydown', e => {
if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); savePage(); }
});
</script>
</body>
</html>
