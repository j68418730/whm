let selectedBlock = null;
let pageId = parseInt(document.body.dataset.pageId || '0');
let siteId = parseInt(document.body.dataset.siteId || '0');

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
    const btn = document.querySelector('.btn-save');
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
    refreshPreview();
}

function selectBlock(el) {
    document.querySelectorAll('.editor-block').forEach(b => b.classList.remove('selected'));
    if (el) { el.classList.add('selected'); selectedBlock = el; showSettings(el); }
}

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
    refreshPreview();
}

function setDevice(device, btn) {
    const canvas = document.getElementById('editorCanvas');
    canvas.classList.remove('mobile', 'tablet', 'desktop', 'responsive');
    canvas.classList.add(device);
    document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    refreshPreview();
}

function togglePreview(btn) {
    const main = document.getElementById('editorMain');
    const frame = document.getElementById('previewFrame');
    if (main.classList.contains('split-mode')) {
        main.classList.remove('split-mode');
        if (btn) btn.innerHTML = '<i class="bi bi-layout-split"></i> Split';
    } else {
        main.classList.add('split-mode');
        if (btn) btn.innerHTML = '<i class="bi bi-layout-split"></i> Close';
        refreshPreview();
    }
}

function refreshPreview() {
    const frame = document.getElementById('previewFrame');
    if (!frame || frame.style.display === 'none') return;
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
    frame.src = '/user/websites/' + siteId + '/preview/' + pageId + '?_t=' + Date.now();
}

document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); savePage(); }
});
