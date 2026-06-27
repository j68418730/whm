<?php
$all_widgets = $all_widgets ?? [];
$widgets_main = $widgets_main ?? '';
$widgets_side = $widgets_side ?? '';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Layout toolbar -->
<div class="card" style="margin-bottom:12px;padding:8px 14px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px">
<div style="display:flex;align-items:center;gap:8px">
<span style="font-size:12px;color:#64748b">Layout:</span>
<select id="layoutSelect" class="form-control" style="width:auto;display:inline-block;padding:4px 8px;font-size:12px">
<option value="default">Default</option>
</select>
<button class="btn btn-sm btn-secondary" onclick="saveCurrentLayout()" title="Save Layout" style="padding:3px 8px;font-size:11px"><i class="bi bi-save"></i></button>
<button class="btn btn-sm btn-secondary" onclick="loadSelectedLayout()" title="Load Layout" style="padding:3px 8px;font-size:11px"><i class="bi bi-folder2-open"></i></button>
<button class="btn btn-sm btn-secondary" onclick="renameLayout()" title="Rename" style="padding:3px 8px;font-size:11px"><i class="bi bi-pencil"></i></button>
<button class="btn btn-sm btn-secondary" onclick="deleteSelectedLayout()" title="Delete" style="padding:3px 8px;font-size:11px"><i class="bi bi-trash"></i></button>
<button class="btn btn-sm btn-secondary" onclick="exportLayout()" title="Export" style="padding:3px 8px;font-size:11px"><i class="bi bi-download"></i></button>
<button class="btn btn-sm btn-secondary" onclick="document.getElementById('importFile').click()" title="Import" style="padding:3px 8px;font-size:11px"><i class="bi bi-upload"></i></button>
<input type="file" id="importFile" accept=".json" style="display:none" onchange="importLayout(this)">
<button class="btn btn-sm btn-secondary" onclick="resetLayout()" title="Reset" style="padding:3px 8px;font-size:11px"><i class="bi bi-arrow-counterclockwise"></i></button>
</div>
<div>
<button class="btn btn-sm btn-primary" onclick="document.getElementById('widgetPicker').classList.toggle('hidden')" style="padding:3px 12px;font-size:11px"><i class="bi bi-plus-circle"></i> Add Widget</button>
</div>
</div>
</div>

<!-- Widget picker -->
<div id="widgetPicker" class="hidden" style="margin-bottom:16px;padding:12px;background:rgba(255,255,255,.02);border:1px solid var(--border,rgba(0,191,255,.1));border-radius:8px">
<div style="display:flex;gap:6px;flex-wrap:wrap">
<?php foreach ($all_widgets as $key => $w): ?>
<button class="btn btn-sm btn-secondary add-widget-btn" data-key="<?php echo $key; ?>" style="padding:4px 10px;font-size:11px"><i class="bi <?php echo $w->getIcon(); ?>"></i> <?php echo htmlspecialchars($w->getName()); ?></button>
<?php endforeach; ?>
</div>
</div>

<!-- Main zone -->
<div class="widget-zone" id="widget-zone-main" data-zone="main">
<?php echo $widgets_main; ?>
</div>

<!-- Side widgets card -->
<div class="card" style="margin-top:12px">
<div class="widget-zone" id="widget-zone-side" data-zone="side" style="min-height:60px">
<h3 style="color:var(--text_muted,#64748b);font-size:12px;margin:0 0 8px">Side Widgets</h3>
<?php echo $widgets_side; ?>
</div>
</div>

<style>
.widget-item{background:var(--card_bg,rgba(8,16,28,.8));border:1px solid var(--border,rgba(0,191,255,.1));border-radius:10px;margin-bottom:10px;overflow:hidden;transition:box-shadow .2s}
.widget-item.dragging{opacity:.4;border-style:dashed;transform:scale(.98)}
.widget-item.drag-over{border-color:var(--primary,#008cff);box-shadow:0 0 20px rgba(0,140,255,.25)}
.widget-item.widget-w1{}
.widget-item.widget-w2{grid-column:span 2}
.widget-item.widget-w3{grid-column:span 3}
.widget-item.widget-collapsed .widget-body{display:none}
.widget-item[data-pinned="1"] .widget-header{background:rgba(250,204,21,.06)}
.widget-item[data-pinned="1"] .widget-handle{display:none}
.widget-header{display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid var(--border,rgba(0,191,255,.06));cursor:move;background:rgba(0,0,0,.15)}
.widget-header .widget-handle{color:var(--text_muted,#64748b);font-size:14px;cursor:grab}
.widget-header .widget-title{flex:1;font-size:12px;font-weight:600}
.widget-actions{display:flex;gap:3px}
.widget-actions .btn-icon{background:none;border:none;color:var(--text_muted,#64748b);cursor:pointer;padding:2px 5px;border-radius:4px;font-size:11px;transition:.15s}
.widget-actions .btn-icon:hover{background:rgba(255,255,255,.08);color:#fff}
.widget-body{padding:12px;transition:max-height .3s ease}
.widget-body .stats-grid{margin:0}
.widget-body table{font-size:12px;width:100%}
.widget-body td,.widget-body th{padding:4px 6px}
.hidden{display:none!important}
.widget-empty{text-align:center;padding:30px;color:var(--text_muted,#64748b);font-size:13px}
.widget-zone.drag-over-zone{background:rgba(0,140,255,.03);border-radius:8px}
.widget-zone{min-height:40px}
#layoutSelect{background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);color:#e0e0e0;border-radius:5px;outline:none}
.resize-handle{height:4px;cursor:ns-resize;background:transparent;position:relative}
.resize-handle:hover{background:rgba(0,140,255,.15)}
.resize-handle::after{content:'';position:absolute;left:50%;top:1px;width:30px;height:2px;background:rgba(255,255,255,.1);border-radius:2px;transform:translateX(-50%)}
</style>

<script>
var dragSrcId = null;
var currentLayout = 'default';

function initLayoutSelect() {
    fetch('/admin/widgets/layouts').then(function(r){return r.json()}).then(function(d){
        if(!d.ok||!d.layouts)return;
        var sel=document.getElementById('layoutSelect');
        sel.innerHTML='';
        d.layouts.forEach(function(name){
            var o=document.createElement('option');
            o.value=name;
            o.textContent=name;
            if(name===currentLayout)o.selected=true;
            sel.appendChild(o);
        });
    }).catch(function(){});
}
initLayoutSelect();

document.getElementById('layoutSelect').addEventListener('change',function(){
    currentLayout=this.value;
    loadSelectedLayout();
});

function saveCurrentLayout() {
    var name=document.getElementById('layoutSelect').value;
    fetch('/admin/widgets/layouts/save',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({layout_name:name})
    }).then(function(r){return r.json()}).then(function(d){
        if(d.ok) showToast('Layout "'+name+'" saved');
    }).catch(function(){});
}

function loadSelectedLayout() {
    var name=document.getElementById('layoutSelect').value;
    if(!confirm('Load layout "'+name+'"? Current layout will be replaced.'))return;
    fetch('/admin/widgets/layouts/apply',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({layout_name:name})
    }).then(function(r){return r.json()}).then(function(d){
        if(d.ok) location.reload();
    }).catch(function(){});
}

function renameLayout() {
    var oldName=document.getElementById('layoutSelect').value;
    var newName=prompt('New name for "'+oldName+'":');
    if(!newName||newName===oldName)return;
    var fd=new FormData();
    fd.append('old_name',oldName);
    fd.append('new_name',newName);
    fetch('/admin/widgets/layouts/rename',{method:'POST',body:fd})
    .then(function(r){return r.json()}).then(function(d){
        if(d.ok){showToast('Renamed to "'+newName+'"');initLayoutSelect();}
    }).catch(function(){});
}

function deleteSelectedLayout() {
    var name=document.getElementById('layoutSelect').value;
    if(name==='default'){showToast('Cannot delete default layout');return;}
    if(!confirm('Delete layout "'+name+'"? This cannot be undone.'))return;
    var fd=new FormData();
    fd.append('layout_name',name);
    fetch('/admin/widgets/layouts/delete',{method:'POST',body:fd})
    .then(function(r){return r.json()}).then(function(d){
        if(d.ok){showToast('Deleted "'+name+'"');currentLayout='default';initLayoutSelect();}
    }).catch(function(){});
}

function exportLayout() {
    var name=document.getElementById('layoutSelect').value;
    window.open('/admin/widgets/layouts/export?layout_name='+encodeURIComponent(name),'_blank');
}

function importLayout(input) {
    if(!input.files||!input.files[0])return;
    var reader=new FileReader();
    reader.onload=function(e){
        var fd=new FormData();
        fd.append('layout_json',e.target.result);
        fetch('/admin/widgets/layouts/import',{method:'POST',body:fd})
        .then(function(r){return r.json()}).then(function(d){
            if(d.ok){showToast('Imported as "'+d.layout_name+'"');initLayoutSelect();}
            else showToast('Import failed');
        }).catch(function(){});
    };
    reader.readAsText(input.files[0]);
    input.value='';
}

function resetLayout() {
    if(!confirm('Reset layout to defaults? All customizations will be lost.'))return;
    var fd=new FormData();
    fd.append('layout_name',document.getElementById('layoutSelect').value);
    fetch('/admin/widgets/layouts/reset',{method:'POST',body:fd})
    .then(function(r){return r.json()}).then(function(d){
        if(d.ok) location.reload();
    }).catch(function(){});
}

function showToast(msg) {
    var t=document.createElement('div');
    t.style.cssText='position:fixed;bottom:20px;right:20px;background:#008cff;color:#fff;padding:10px 20px;border-radius:8px;font-size:13px;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.3);animation:fadeIn .2s';
    t.textContent=msg;
    document.body.appendChild(t);
    setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .3s';setTimeout(function(){t.remove()},300)},2000);
}

// Drag and drop
document.querySelectorAll('.widget-item[draggable="true"]').forEach(function(el) {
    el.addEventListener('dragstart', function(e) {
        dragSrcId = this.dataset.widgetId;
        e.dataTransfer.setData('text/plain', this.dataset.widgetId);
        this.classList.add('dragging');
    });
    el.addEventListener('dragend', function(e) {
        this.classList.remove('dragging');
        document.querySelectorAll('.widget-item').forEach(function(w){w.style.borderTop='';w.style.borderBottom=''});
        dragSrcId = null;
    });
    el.addEventListener('dragover', function(e) {
        e.preventDefault();
        var rect = this.getBoundingClientRect();
        var y = e.clientY - rect.top;
        this.style.borderTop = y < rect.height / 2 ? '2px solid var(--primary)' : '';
        this.style.borderBottom = y >= rect.height / 2 ? '2px solid var(--primary)' : '';
    });
    el.addEventListener('dragleave', function(e) {
        this.style.borderTop = '';
        this.style.borderBottom = '';
    });
    el.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderTop = '';
        this.style.borderBottom = '';
        var id = e.dataTransfer.getData('text/plain');
        if (!id || id === this.dataset.widgetId) return;
        var widget = document.querySelector('[data-widget-id="' + id + '"]');
        if (!widget) return;
        var rect = this.getBoundingClientRect();
        var y = e.clientY - rect.top;
        if (y < rect.height / 2) this.parentNode.insertBefore(widget, this);
        else this.parentNode.insertBefore(widget, this.nextSibling);
        saveWidgetLayout();
    });
});

document.querySelectorAll('.widget-zone').forEach(function(zone) {
    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        zone.classList.add('drag-over-zone');
    });
    zone.addEventListener('dragleave', function(e) {
        zone.classList.remove('drag-over-zone');
    });
    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('drag-over-zone');
        var id = e.dataTransfer.getData('text/plain');
        if (!id) return;
        var widget = document.querySelector('[data-widget-id="' + id + '"]');
        var target = e.target.closest('.widget-item');
        if (widget) {
            if (target && target.parentElement === zone) {
                var rect = target.getBoundingClientRect();
                var y = e.clientY - rect.top;
                if (y < rect.height / 2) target.parentNode.insertBefore(widget, target);
                else target.parentNode.insertBefore(widget, target.nextSibling);
            } else {
                zone.appendChild(widget);
            }
            saveWidgetLayout();
        }
    });
});

function saveWidgetLayout() {
    var layout = [];
    document.querySelectorAll('.widget-zone').forEach(function(zone) {
        var zoneName = zone.dataset.zone;
        zone.querySelectorAll('.widget-item').forEach(function(w, i) {
            if (w.dataset.widgetId) layout.push({id: parseInt(w.dataset.widgetId), zone: zoneName, sort_order: i, width: 1});
        });
    });
    fetch('/admin/widgets/save-layout', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({layout: layout})
    }).catch(function(e){});
}

// Widget actions
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.widget-collapse-btn');
    if (btn) {
        var item = btn.closest('.widget-item');
        var id = item.dataset.widgetId;
        var fd = new FormData();
        fd.append('id', id);
        fetch('/admin/widgets/collapse', {method: 'POST', body: fd}).then(function(r){return r.json()}).then(function(d){
            if (d.ok && d.result) {
                item.classList.toggle('widget-collapsed');
                var body=item.querySelector('.widget-body');
                if(body) body.style.display=d.result.collapsed?'none':'';
                btn.querySelector('i').className='bi '+(d.result.collapsed?'bi-plus':'bi-dash');
                btn.title=d.result.collapsed?'Expand':'Collapse';
            }
        });
        return;
    }

    var pinBtn = e.target.closest('.widget-pin-btn');
    if (pinBtn) {
        var item = pinBtn.closest('.widget-item');
        var id = item.dataset.widgetId;
        var fd = new FormData();
        fd.append('id', id);
        fetch('/admin/widgets/pin', {method: 'POST', body: fd}).then(function(r){return r.json()}).then(function(d){
            if (d.ok && d.result) {
                if (d.result.pinned) {
                    item.setAttribute('data-pinned','1');
                    item.querySelector('.widget-item[draggable]')&&item.setAttribute('draggable','false');
                    pinBtn.style.color='#facc15';
                    pinBtn.querySelector('i').className='bi bi-pin-fill';
                } else {
                    item.removeAttribute('data-pinned');
                    item.querySelector('.widget-item')&&item.setAttribute('draggable','true');
                    pinBtn.style.color='#64748b';
                    pinBtn.querySelector('i').className='bi bi-pin';
                }
                pinBtn.title=d.result.pinned?'Unpin':'Pin';
            }
        });
        return;
    }

    var hideBtn = e.target.closest('.widget-hide-btn');
    if (hideBtn) {
        if (!confirm('Hide this widget?')) return;
        var item = hideBtn.closest('.widget-item');
        var id = item.dataset.widgetId;
        var fd = new FormData();
        fd.append('id', id);
        fetch('/admin/widgets/hide', {method: 'POST', body: fd}).then(function(r){return r.json()}).then(function(d){
            if (d.ok) item.remove();
        });
        return;
    }

    var removeBtn = e.target.closest('.widget-remove');
    if (removeBtn) {
        if (!confirm('Remove this widget?')) return;
        var key = removeBtn.dataset.key;
        var item = removeBtn.closest('.widget-item');
        var fd = new FormData();
        fd.append('key', key);
        fetch('/admin/widgets/remove', {method: 'POST', body: fd}).then(function(r) {
            item.remove();
        });
        return;
    }
});

// Add widget
document.querySelectorAll('.add-widget-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var key = this.dataset.key;
        var fd = new FormData();
        fd.append('key', key);
        fd.append('zone', 'main');
        fd.append('layout_name', currentLayout);
        fetch('/admin/widgets/add', {method: 'POST', body: fd}).then(function(r) {
            location.reload();
        });
    });
});

// Resize handle toggle on double-click header
document.addEventListener('dblclick', function(e) {
    var h = e.target.closest('.widget-header');
    if (h) {
        var item = h.closest('.widget-item');
        if (!item) return;
        var cur = 1;
        if (item.classList.contains('widget-w2')) cur = 2;
        else if (item.classList.contains('widget-w3')) cur = 3;
        var next = cur >= 3 ? 1 : cur + 1;
        item.className = item.className.replace(/widget-w\d/g, '');
        item.classList.add('widget-w' + next);
        var id = item.dataset.widgetId;
        var fd = new FormData();
        fd.append('id', id);
        fd.append('width', next);
        fetch('/admin/widgets/width', {method: 'POST', body: fd});
    }
});
</script>
