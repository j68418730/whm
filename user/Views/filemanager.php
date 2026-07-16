<style>
.fm-wrapper{display:flex;height:calc(100vh - 180px);min-height:400px;border:1px solid rgba(255,255,255,.06);border-radius:10px;overflow:hidden}
.fm-tree{width:220px;background:rgba(0,0,0,.2);overflow-y:auto;border-right:1px solid rgba(255,255,255,.06);flex-shrink:0;padding:6px 0;font-size:13px}
.fm-tree .folder{padding:4px 10px;cursor:pointer;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fm-tree .folder:hover{background:rgba(255,255,255,.04);color:#e0e0e0}
.fm-tree .folder.active{background:rgba(0,140,255,.1);color:#0A84FF}
.fm-tree .folder .arrow{display:inline-block;width:16px;text-align:center;font-size:10px;color:#64748b}
.fm-tree .folder .arrow.open{transform:rotate(90deg)}
.fm-tree .folder .icon{margin-right:4px}
.fm-tree .children{padding-left:16px;display:none}
.fm-tree .children.open{display:block}
.fm-main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.fm-toolbar{display:flex;gap:4px;padding:8px;border-bottom:1px solid rgba(255,255,255,.06);flex-wrap:wrap;background:rgba(0,0,0,.15)}
.fm-toolbar button,.fm-toolbar .btn{padding:5px 10px;border-radius:5px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.2);color:#94a3b8;font-size:11px;cursor:pointer;white-space:nowrap}
.fm-toolbar button:hover{background:rgba(0,140,255,.1);color:#fff;border-color:rgba(0,140,255,.2)}
.fm-toolbar .sep{width:1px;background:rgba(255,255,255,.06);margin:0 4px}
.fm-path{display:flex;align-items:center;gap:4px;padding:4px 8px;font-size:11px;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.1);overflow-x:auto;white-space:nowrap}
.fm-path a{color:#94a3b8;text-decoration:none;padding:1px 4px;border-radius:3px}
.fm-path a:hover{color:#0A84FF;background:rgba(0,140,255,.06)}
.fm-path .sep{color:#64748b;margin:0 2px}
.fm-list{flex:1;overflow-y:auto;padding:4px}
.fm-list .file{padding:6px 10px;display:flex;gap:10px;align-items:center;cursor:default;border-radius:4px;font-size:13px;color:#e0e0e0}
.fm-list .file:hover{background:rgba(255,255,255,.03)}
.fm-list .file.selected{background:rgba(0,140,255,.08)}
.fm-list .file .icon{font-size:18px;width:24px;text-align:center;flex-shrink:0}
.fm-list .file .name{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.fm-list .file .size{width:80px;text-align:right;color:#64748b;font-size:11px}
.fm-list .file .type{width:60px;color:#64748b;font-size:11px}
.fm-list .file .perms{width:50px;color:#64748b;font-size:10px;font-family:monospace}
.fm-list .file .date{width:140px;color:#64748b;font-size:11px}
.fm-list .file .actions{display:flex;gap:4px}
.fm-list .file:hover .actions{display:flex}
.fm-list .file .actions a{padding:2px 6px;border-radius:3px;font-size:10px;text-decoration:none;color:#94a3b8}
.fm-list .file .actions a:hover{background:rgba(0,140,255,.1);color:#0A84FF}
.fm-status{display:flex;justify-content:space-between;padding:4px 10px;font-size:11px;color:#64748b;border-top:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.1)}
.context-menu{position:fixed;background:rgba(8,16,28,.98);border:1px solid rgba(0,191,255,.08);border-radius:8px;padding:4px 0;min-width:160px;z-index:9999;display:none;box-shadow:0 8px 24px rgba(0,0,0,.4)}
.context-menu .item{padding:6px 14px;font-size:12px;cursor:pointer;color:#e0e0e0;display:flex;gap:8px;align-items:center}
.context-menu .item:hover{background:rgba(0,140,255,.1);color:#0A84FF}
.context-menu .sep{height:1px;background:rgba(255,255,255,.06);margin:4px 8px}
#uploadZone{display:none;position:fixed;inset:0;background:rgba(0,140,255,.1);border:3px dashed #0A84FF;z-index:9998;justify-content:center;align-items:center;font-size:24px;color:#0A84FF;font-weight:700}
#uploadZone.show{display:flex}
.editor-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:9997;justify-content:center;align-items:center}
.editor-overlay.show{display:flex}
.editor-box{background:rgba(8,16,28,.98);border:1px solid rgba(0,191,255,.12);border-radius:12px;width:90%;max-width:800px;height:80%;display:flex;flex-direction:column}
.editor-box .header{display:flex;justify-content:space-between;align-items:center;padding:10px 16px;border-bottom:1px solid rgba(255,255,255,.06)}
.editor-box .header h3{margin:0;font-size:14px}
.editor-box .header button{background:none;border:none;color:#94a3b8;font-size:20px;cursor:pointer}
.editor-box textarea{flex:1;padding:12px;background:#0a0e1a;color:#4ade80;font-family:monospace;font-size:13px;border:none;outline:none;resize:none;tab-size:4}
.editor-box .footer{display:flex;justify-content:flex-end;gap:8px;padding:8px 12px;border-top:1px solid rgba(255,255,255,.06)}
.prop-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9996;justify-content:center;align-items:center}
.prop-overlay.show{display:flex}
.prop-box{background:rgba(8,16,28,.98);border:1px solid rgba(0,191,255,.12);border-radius:12px;padding:24px;min-width:360px}
.prop-box h3{margin:0 0 14px;font-size:14px}
.prop-box .row{display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid rgba(255,255,255,.04)}
.prop-box .row .lbl{color:#64748b}
.perms-grid{display:flex;gap:20px;margin:10px 0;flex-wrap:wrap}
.perms-col{text-align:center}
.perms-col label{display:block;font-size:10px;color:#64748b;margin-bottom:4px}
.perms-col select{padding:4px;border-radius:4px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px}
#toast{position:fixed;bottom:20px;right:20px;padding:10px 16px;border-radius:8px;font-size:12px;z-index:9999;display:none}
#toast.success{background:rgba(74,222,128,.15);border:1px solid rgba(74,222,128,.2);color:#4ade80}
#toast.error{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.2);color:#ef4444}
.ve-btn{padding:4px 10px;border-radius:4px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.2);color:#94a3b8;font-size:12px;cursor:pointer}
.ve-btn:hover{background:rgba(0,140,255,.1);color:#fff}
.ve-btn.active{background:rgba(0,140,255,.2);color:#0A84FF}
</style>

<div id="toast"></div>
<div id="uploadZone">📁 Drop files anywhere to upload</div>
<div class="context-menu" id="ctxMenu">
<div class="item" onclick="fmOpen()">📂 Open</div>
<div class="item" onclick="fmDownload()">⬇ Download</div>
<div class="item" onclick="fmRename()">✏ Rename</div>
<div class="item" onclick="fmCopy()">📋 Copy</div>
<div class="item" onclick="fmMove()">✂ Move</div>
<div class="item" onclick="fmDelete()">🗑 Delete</div>
<div class="sep"></div>
<div class="item" onclick="fmArchive()">📦 Compress</div>
<div class="item" onclick="fmExtract()">📂 Extract</div>
<div class="sep"></div>
<div class="item" onclick="fmPerms()">🔒 Permissions</div>
<div class="item" onclick="fmProps()">ℹ Properties</div>
</div>
<div class="editor-overlay" id="editorOverlay" onclick="if(event.target===this)fmCloseEditor()">
<div class="editor-box">
<div class="header"><h3 id="editorTitle">Edit File</h3><button onclick="fmCloseEditor()">✕</button></div>
<div style="display:flex;gap:4px;padding:4px 8px;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.1)">
<button class="ve-btn" data-view="code" onclick="veSetView('code')">Code</button>
<button class="ve-btn" data-view="split" onclick="veSetView('split')">Split</button>
<button class="ve-btn" data-view="design" onclick="veSetView('design')">Design</button>
<div style="flex:1"></div>
<button class="ve-btn" onclick="veBold()" title="Bold"><b>B</b></button>
<button class="ve-btn" onclick="veItalic()" title="Italic"><i>I</i></button>
<button class="ve-btn" onclick="veUnderline()" title="Underline"><u>U</u></button>
<button class="ve-btn" onclick="veHeading()" title="Heading">H</button>
<button class="ve-btn" onclick="veLink()" title="Link">🔗</button>
<button class="ve-btn" onclick="veImage()" title="Image">🖼</button>
</div>
<div id="veContainer" style="flex:1;display:flex;flex-direction:column;overflow:hidden">
<iframe id="vePreview" style="flex:1;border:none;background:#fff;display:none;min-height:0"></iframe>
<div id="veDesign" contenteditable="true" style="flex:1;padding:12px;background:#fff;color:#000;font-size:14px;overflow-y:auto;display:none;outline:none;min-height:0"></div>
<textarea id="editorContent" spellcheck="false" style="flex:1;padding:12px;background:#0a0e1a;color:#4ade80;font-family:monospace;font-size:13px;border:none;outline:none;resize:none;tab-size:4;min-height:0"></textarea>
</div>
<div class="footer"><button onclick="fmCloseEditor()" style="padding:6px 14px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.2);color:#94a3b8;cursor:pointer">Cancel</button><button onclick="fmSaveEditor()" style="padding:6px 14px;border-radius:6px;border:none;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;cursor:pointer">💾 Save</button></div>
</div>
</div>
<div class="prop-overlay" id="propOverlay" onclick="if(event.target===this)fmCloseProps()">
<div class="prop-box"><h3>ℹ Properties</h3><div id="propContent"></div>
<div style="margin-top:14px;text-align:right"><button onclick="fmCloseProps()" style="padding:6px 14px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.2);color:#94a3b8;cursor:pointer">Close</button></div></div>
</div>

<h2>📁 File Manager</h2>
<p style="color:#64748b;margin-bottom:12px;font-size:12px">Manage your files and folders from the browser. <a href="/user/files/list?dir=" target="_blank" style="color:#0A84FF;font-size:11px">Debug: view raw JSON</a></p>

<div class="fm-wrapper">
<div class="fm-tree" id="folderTree"></div>
<div class="fm-main">
<div class="fm-toolbar">
<button onclick="fmCreateFolder()">📁 +Folder</button>
<button onclick="fmCreateFile()">📄 +File</button>
<button onclick="document.getElementById('uploadInput').click()">📤 Upload</button>
<input type="file" id="uploadInput" multiple style="display:none" onchange="fmUpload(this.files)">
<button onclick="fmDownload()">⬇ Download</button>
<button onclick="fmEditSelected()">✏ Edit</button>
<div class="sep"></div>
<button onclick="fmArchive()">📦 Compress</button>
<button onclick="document.getElementById('extractInput').click()">📂 Extract</button>
<input type="file" id="extractInput" accept=".zip,.tar,.gz,.tgz" style="display:none" onchange="fmExtractUpload(this.files)">
<div class="sep"></div>
<button onclick="fmRename()">✏ Rename</button>
<button onclick="fmCopy()">📋 Copy</button>
<button onclick="fmMove()">✂ Move</button>
<button onclick="fmDelete()">🗑 Delete</button>
<div class="sep"></div>
<button onclick="fmPerms()">🔒 Perms</button>
<button onclick="fmRefresh()">🔄 Refresh</button>
<input type="text" id="searchInput" placeholder="Search files..." style="width:150px;padding:4px 8px;border-radius:5px;border:1px solid rgba(255,255,255,.06);background:rgba(0,0,0,.2);color:#e0e0e0;font-size:11px;outline:none" oninput="fmSearch(this.value)">
</div>
<div class="fm-path" id="fmPath"></div>
<div class="fm-list" id="fileList"></div>
<div class="fm-status" id="fmStatus"></div>
</div>
</div>

<script>
var currentDir = "", selectedFile = null, clipboard = null, contextTarget = null;

function fmRefresh(dir) {
    if (dir !== undefined) currentDir = dir;
    var url = "/user/files/list?dir=" + encodeURIComponent(currentDir);
    fetch(url).then(function(r){return r.json()}).then(function(d){
        console.log('FM data:', d);
        fmRenderTree(d.tree);
        fmRenderPath(d.dir);
        fmRenderFiles(d.items);
        fmUpdateStatus(d.items.length, d.home);
    }).catch(function(e){console.error('FM error:', e);document.getElementById("fileList").innerHTML='<div style="padding:30px;text-align:center;color:#f87171">Error: '+e.message+'</div>';});
}
fmRefresh("");

function fmRenderTree(tree) {
    var html = "";
    html += '<div class="folder active" onclick="fmRefresh(\'\')">🏠 Home</div>';
    tree.forEach(function(f){ html += fmRenderNode(f, 0); });
    document.getElementById("folderTree").innerHTML = html;
}

function fmRenderNode(f, depth) {
    var hasChildren = f.children && f.children.length > 0;
    var html = '<div class="folder" onclick="fmRefresh(\'' + f.path + '\')">';
    html += '<span class="arrow">' + (hasChildren ? "&#9654;" : "") + '</span>';
    html += '<span class="icon">📁</span>' + f.name + '</div>';
    if (hasChildren) {
        html += '<div class="children">';
        f.children.forEach(function(c){ html += fmRenderNode(c, depth + 1); });
        html += "</div>";
    }
    return html;
}

function fmRenderPath(path) {
    var parts = path ? path.split("/") : [];
    var html = '<a href="#" onclick="fmRefresh(\'\');return false">Home</a>';
    var running = "";
    parts.forEach(function(p){
        if (!p) return;
        running += "/" + p;
        html += '<span class="sep">/</span><a href="#" onclick="fmRefresh(\'' + running + '\');return false">' + p + '</a>';
    });
    document.getElementById("fmPath").innerHTML = html;
}

function fmRenderFiles(items) {
    console.log('fmRenderFiles called with', items.length, 'items');
    var html = "";
    items.forEach(function(f){
        var icon = f.is_dir ? "📁" : fmIcon(f.ext);
        html += '<div class="file" data-path="' + f.path + '" onclick="fmSelect(this,event)" ondblclick="fmOpenItem(\'' + f.path + '\',' + f.is_dir + ')">';
        html += '<span class="icon">' + icon + '</span>';
        html += '<span class="name">' + f.name + '</span>';
        html += '<span class="size">' + (f.is_dir ? "-" : fmSize(f.size)) + '</span>';
        html += '<span class="type">' + (f.is_dir ? "folder" : f.ext.toUpperCase()) + '</span>';
        html += '<span class="perms">' + f.perms + '</span>';
        html += '<span class="date">' + f.modified + '</span>';
        html += '<span class="actions">';
        html += '<a href="#" onclick="event.stopPropagation();fmOpenItem(\'' + f.path + '\',' + f.is_dir + ');return false">Open</a>';
        if (!f.is_dir) html += '<a href="#" onclick="event.stopPropagation();fmEditFile(\'' + f.path + '\');return false">Edit</a>';
        html += '<a href="#" onclick="event.stopPropagation();fmDeleteItem(\'' + f.path + '\');return false">Del</a>';
        html += '</span></div>';
    });
    document.getElementById("fileList").innerHTML = html;
}

function fmIcon(ext) {
    var icons = {folder: "📁", mp3: "🎵", aac: "🎵", ogg: "🎵", flac: "🎵", wav: "🎵",
        jpg:"🖼", jpeg:"🖼", png:"🖼", gif:"🖼", svg:"🖼", webp:"🖼",
        pdf:"📄", zip:"📦", tar:"📦", gz:"📦", tgz:"📦", rar:"📦",
        php:"🐘", html:"🌐", css:"🎨", js:"⚡", json:"📋", xml:"📋", txt:"📄",
        doc:"📝", docx:"📝", xls:"📊", xlsx:"📊"};
    return icons[ext] || "📄";
}

function fmSize(s) {
    if (!s) return "0 B";
    var u = ["B","KB","MB","GB"], i = 0;
    while (s >= 1024 && i < 3) { s /= 1024; i++; }
    return (i < 2 ? Math.round(s) : s.toFixed(1)) + " " + u[i];
}

function fmUpdateStatus(count, home) {
    document.getElementById("fmStatus").innerHTML = "<span>" + count + " items</span><span>" + home + "</span>";
}

function fmSelect(el, e) {
    document.querySelectorAll(".file.selected").forEach(function(f){f.classList.remove("selected")});
    el.classList.add("selected");
    selectedFile = el.dataset.path;
}

function fmOpenItem(path, isDir) {
    if (isDir) fmRefresh(path);
    else fmDownloadItem(path);
}

function fmOpen() {
    if (!selectedFile) return;
    var isDir = document.querySelector(".file.selected .size").textContent === "-";
    fmOpenItem(selectedFile, isDir);
    fmHideCtx();
}

function fmDownloadItem(path) {
    window.location.href = "/user/files/download?file=" + encodeURIComponent(path);
}

function fmDownload() {
    if (!selectedFile) return;
    fmDownloadItem(selectedFile);
    fmHideCtx();
}

function fmEditSelected() {
    if (!selectedFile) return;
    var isDir = document.querySelector(".file.selected .size").textContent === "-";
    if (!isDir) fmEditFile(selectedFile);
    else fmToast("Cannot edit a folder", "error");
}

function fmEditFile(path) {
    fetch("/user/files/read?file=" + encodeURIComponent(path)).then(function(r){return r.json()}).then(function(d){
        document.getElementById("editorTitle").textContent = "📝 " + d.name;
        document.getElementById("editorContent").value = d.content;
        document.getElementById("editorContent").dataset.file = path;
        document.getElementById("editorOverlay").classList.add("show");
        veSetView(d.ext === "html" || d.ext === "htm" ? "split" : "code");
    });
    fmHideCtx();
}

function fmSaveEditor() {
    veSyncDesignToCode();
    var path = document.getElementById("editorContent").dataset.file;
    var content = document.getElementById("editorContent").value;
    var fd = new FormData();
    fd.append("file", path);
    fd.append("content", content);
    fetch("/user/files/save", {method:"POST",body:fd}).then(function(r){return r.json()}).then(function(d){
        fmToast("💾 Saved", "success");
        fmCloseEditor();
    });
}

function fmCloseEditor() {
    document.getElementById("editorOverlay").classList.remove("show");
}

var veCurrentView = "code";
function veSetView(view) {
    veCurrentView = view;
    var code = document.getElementById("editorContent");
    var prev = document.getElementById("vePreview");
    var design = document.getElementById("veDesign");
    document.querySelectorAll(".ve-btn[data-view]").forEach(function(b){b.classList.toggle("active",b.dataset.view===view)});
    if (view === "code") { code.style.display = "block"; prev.style.display = "none"; design.style.display = "none"; }
    else if (view === "design") {
        code.style.display = "none"; prev.style.display = "none"; design.style.display = "block";
        design.innerHTML = code.value;
        design.focus();
    } else {
        code.style.display = "block"; prev.style.display = "block"; design.style.display = "none";
        veUpdatePreview();
    }
}
function veUpdatePreview() {
    var prev = document.getElementById("vePreview");
    var html = document.getElementById("editorContent").value;
    prev.src = "data:text/html;charset=utf-8," + encodeURIComponent(html);
}
document.getElementById("editorContent").addEventListener("input", function() {
    if (veCurrentView === "split") veUpdatePreview();
});
function veSyncDesignToCode() {
    if (veCurrentView === "design")
        document.getElementById("editorContent").value = document.getElementById("veDesign").innerHTML;
}
function veExec(cmd, val) {
    if (veCurrentView === "design") {
        document.getElementById("veDesign").focus();
        document.execCommand(cmd, false, val || null);
    } else if (veCurrentView === "split") {
        document.getElementById("editorContent").focus();
    }
}
function veBold() { veExec("bold"); veSyncDesignToCode(); }
function veItalic() { veExec("italic"); veSyncDesignToCode(); }
function veUnderline() { veExec("underline"); veSyncDesignToCode(); }
function veHeading() {
    var h = prompt("Heading level (1-6):", "2");
    if (h) { veExec("formatBlock", "h" + h); veSyncDesignToCode(); }
}
function veLink() {
    var url = prompt("URL:", "https://");
    if (url) { veExec("createLink", url); veSyncDesignToCode(); }
}
function veImage() {
    var url = prompt("Image URL:", "https://");
    if (url) { veExec("insertImage", url); veSyncDesignToCode(); }
}
document.getElementById("veDesign").addEventListener("input", function() {
    document.getElementById("editorContent").value = this.innerHTML;
});
document.getElementById("veDesign").addEventListener("keydown", function(e) {
    if (e.key === "Tab") { e.preventDefault(); document.execCommand("insertHTML", false, "&nbsp;&nbsp;&nbsp;&nbsp"); }
});

function fmCreateFolder() {
    var name = prompt("Folder name:");
    if (!name) return;
    var fd = new FormData();
    fd.append("dir", currentDir);
    fd.append("name", name);
    fetch("/user/files/mkdir", {method:"POST",body:fd}).then(function(r){return r.json()}).then(function(d){
        fmToast("📁 Folder created", "success");
        fmRefresh(currentDir);
    });
}

function fmCreateFile() {
    var name = prompt("File name (e.g. index.html):");
    if (!name) return;
    var fd = new FormData();
    fd.append("dir", currentDir);
    fd.append("name", name);
    fetch("/user/files/create", {method:"POST",body:fd}).then(function(r){return r.json()}).then(function(d){
        fmToast("📄 File created", "success");
        fmRefresh(currentDir);
    });
}

function fmUpload(files) {
    var fd = new FormData();
    fd.append("dir", currentDir);
    for (var i = 0; i < files.length; i++) fd.append("files[]", files[i]);
    fetch("/user/files/upload", {method:"POST",body:fd}).then(function(r){return r.json()}).then(function(d){
        fmToast("📤 " + d.uploaded + " files uploaded", "success");
        fmRefresh(currentDir);
    });
}

function fmRename() {
    if (!selectedFile) return;
    var name = prompt("New name:");
    if (!name) return;
    var fd = new FormData();
    fd.append("old", selectedFile);
    fd.append("new_name", name);
    fetch("/user/files/rename", {method:"POST",body:fd}).then(function(r){return r.json()}).then(function(d){
        fmToast("✏ Renamed", "success");
        fmRefresh(currentDir);
    });
    fmHideCtx();
}

function fmCopy() {
    if (!selectedFile) return;
    clipboard = {action:"copy", path:selectedFile};
    fmToast("📋 Copied: " + selectedFile.split("/").pop(), "success");
    fmHideCtx();
}

function fmMove() {
    if (!selectedFile) return;
    clipboard = {action:"move", path:selectedFile};
    fmToast("✂ Cut: " + selectedFile.split("/").pop(), "success");
    fmHideCtx();
}

function fmPaste() {
    if (!clipboard) return;
    var dst = currentDir + "/" + clipboard.path.split("/").pop();
    var fd = new FormData();
    if (clipboard.action === "copy") {
        fd.append("src", clipboard.path);
        fd.append("dst", dst);
        fetch("/user/files/copy", {method:"POST",body:fd}).then(function(r){return r.json()}).then(function(d){
            fmToast("📋 Pasted", "success");
            fmRefresh(currentDir);
        });
    } else {
        fd.append("src", clipboard.path);
        fd.append("dst", dst);
        fetch("/user/files/move", {method:"POST",body:fd}).then(function(r){return r.json()}).then(function(d){
            fmToast("✂ Moved", "success");
            fmRefresh(currentDir);
        });
    }
    clipboard = null;
}

function fmDelete() {
    if (!selectedFile) return;
    if (!confirm("Delete " + selectedFile.split("/").pop() + "?")) return;
    fetch("/user/files/delete?file=" + encodeURIComponent(selectedFile)).then(function(r){return r.json()}).then(function(d){
        fmToast("🗑 Deleted", "success");
        selectedFile = null;
        fmRefresh(currentDir);
    });
    fmHideCtx();
}

function fmDeleteItem(path) {
    if (!confirm("Delete?")) return;
    fetch("/user/files/delete?file=" + encodeURIComponent(path)).then(function(r){return r.json()}).then(function(d){
        fmRefresh(currentDir);
    });
}

function fmArchive() {
    if (!selectedFile) return;
    fetch("/user/files/archive?dir=" + encodeURIComponent(selectedFile)).then(function(r){return r.json()}).then(function(d){
        fmToast("📦 Compressed: " + d.file, "success");
        fmRefresh(currentDir);
    });
    fmHideCtx();
}

function fmExtractUpload(files) {
    if (!files.length) return;
    var path = currentDir + "/" + files[0].name;
    var fd = new FormData();
    fd.append("dir", currentDir);
    fd.append("files[]", files[0]);
    var uploadFd = new FormData();
    uploadFd.append("dir", currentDir);
    uploadFd.append("files[]", files[0]);
    fetch("/user/files/upload", {method:"POST",body:uploadFd}).then(function(r){return r.json()}).then(function(d){
        fetch("/user/files/extract?file=" + encodeURIComponent(path)).then(function(r){return r.json()}).then(function(d){
            fmToast("📂 Extracted", "success");
            fmRefresh(currentDir);
        });
    });
}

function fmExtract() {
    if (!selectedFile) return;
    fetch("/user/files/extract?file=" + encodeURIComponent(selectedFile)).then(function(r){return r.json()}).then(function(d){
        fmToast("📂 Extracted", "success");
        fmRefresh(currentDir);
    });
    fmHideCtx();
}

function fmPerms() {
    if (!selectedFile) return;
    fetch("/user/files/properties?file=" + encodeURIComponent(selectedFile)).then(function(r){return r.json()}).then(function(d){
        var perms = d.perms || "755";
        var owner = perms[0]||"7", group = perms[1]||"5", pub = perms[2]||"5";
        var html = "<div class=\"row\"><span class=\"lbl\">Name</span><span>" + d.name + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Path</span><span>" + d.path + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Type</span><span>" + d.type + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Size</span><span>" + fmSize(d.size) + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Owner</span><span>" + d.owner + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Modified</span><span>" + d.modified + "</span></div>";
        html += "<h4 style=\"margin:12px 0 6px;font-size:13px\">Permissions</h4>";
        html += "<div class=\"perms-grid\">";
        html += "<div class=\"perms-col\"><label>Owner</label><select id=\"permOwner\" onchange=\"fmCalcPerms()\">" + fmPermOpts(owner) + "</select></div>";
        html += "<div class=\"perms-col\"><label>Group</label><select id=\"permGroup\" onchange=\"fmCalcPerms()\">" + fmPermOpts(group) + "</select></div>";
        html += "<div class=\"perms-col\"><label>Public</label><select id=\"permPublic\" onchange=\"fmCalcPerms()\">" + fmPermOpts(pub) + "</select></div>";
        html += "</div>";
        html += "<div style=\"font-size:12px;margin:8px 0;color:#4ade80;font-family:monospace\">chmod <span id=\"permCode\">" + perms + "</span></div>";
        html += '<button onclick="fmSavePerms(\'' + selectedFile + '\')" style="padding:6px 14px;border-radius:6px;border:none;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;cursor:pointer">💾 Apply</button>';
        document.getElementById("propContent").innerHTML = html;
        document.getElementById("propOverlay").classList.add("show");
    });
    fmHideCtx();
}

function fmPermOpts(v) {
    var opts = ["0 ---","1 --x","2 -w-","3 -wx","4 r--","5 r-x","6 rw-","7 rwx"];
    var html = "";
    for (var i = 0; i < 8; i++) html += "<option value=\"" + i + "\"" + (parseInt(v)===i?" selected":"") + ">" + opts[i] + "</option>";
    return html;
}

function fmCalcPerms() {
    var o = document.getElementById("permOwner").value;
    var g = document.getElementById("permGroup").value;
    var p = document.getElementById("permPublic").value;
    document.getElementById("permCode").textContent = o + g + p;
}

function fmSavePerms(path) {
    var perms = parseInt(document.getElementById("permCode").textContent);
    var fd = new FormData();
    fd.append("file", path);
    fd.append("perms", perms);
    fetch("/user/files/chmod", {method:"POST",body:fd}).then(function(r){return r.json()}).then(function(d){
        fmToast("🔒 Permissions updated", "success");
        fmCloseProps();
        fmRefresh(currentDir);
    });
}

function fmProps() {
    if (!selectedFile) return;
    fetch("/user/files/properties?file=" + encodeURIComponent(selectedFile)).then(function(r){return r.json()}).then(function(d){
        var html = "<div class=\"row\"><span class=\"lbl\">Name</span><span>" + d.name + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Path</span><span>" + d.path + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Type</span><span>" + d.type + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Size</span><span>" + fmSize(d.size) + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Permissions</span><span>" + d.perms + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Owner</span><span>" + d.owner + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Group</span><span>" + d.group + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Modified</span><span>" + d.modified + "</span></div>";
        html += "<div class=\"row\"><span class=\"lbl\">Created</span><span>" + d.created + "</span></div>";
        document.getElementById("propContent").innerHTML = html;
        document.getElementById("propOverlay").classList.add("show");
    });
    fmHideCtx();
}

function fmCloseProps() {
    document.getElementById("propOverlay").classList.remove("show");
}

function fmSearch(q) {
    if (!q) { fmRefresh(currentDir); return; }
    fetch("/user/files/search?q=" + encodeURIComponent(q) + "&dir=" + encodeURIComponent(currentDir)).then(function(r){return r.json()}).then(function(d){
        var items = d.map(function(f){ return {name:f.name, path:f.path, is_dir:f.is_dir, ext:f.is_dir?"folder":f.name.split(".").pop(), size:0, perms:"", modified:"", owner:""}; });
        fmRenderFiles(items);
    });
}

function fmToast(msg, type) {
    var t = document.getElementById("toast");
    t.textContent = msg; t.className = type; t.style.display = "block";
    setTimeout(function(){ t.style.display = "none"; }, 3000);
}

function fmHideCtx() {
    document.getElementById("ctxMenu").style.display = "none";
}

// Right-click context menu
document.addEventListener("contextmenu", function(e){
    var file = e.target.closest(".file");
    if (file) {
        e.preventDefault();
        fmSelect(file, e);
        contextTarget = file.dataset.path;
        var m = document.getElementById("ctxMenu");
        m.style.left = e.clientX + "px";
        m.style.top = e.clientY + "px";
        m.style.display = "block";
        fmUpdateCtxMenu(file.querySelector(".size").textContent === "-");
    }
});

function fmUpdateCtxMenu(isDir) {
    document.getElementById("ctxMenu").querySelectorAll(".item").forEach(function(el){
        var t = el.textContent.trim();
        if (t === "📂 Extract") el.style.display = isDir ? "none" : "";
        if (t === "📦 Compress") el.style.display = isDir ? "" : "none";
    });
}

document.addEventListener("click", function(e){
    if (!e.target.closest(".context-menu")) fmHideCtx();
});

// Drag-drop upload
document.addEventListener("dragover", function(e){e.preventDefault();document.getElementById("uploadZone").classList.add("show")});
document.addEventListener("dragleave", function(e){
    if (!e.relatedTarget || e.relatedTarget.id !== "uploadZone")
        document.getElementById("uploadZone").classList.remove("show");
});
document.addEventListener("drop", function(e){
    e.preventDefault();
    document.getElementById("uploadZone").classList.remove("show");
    if (e.dataTransfer.files.length) fmUpload(e.dataTransfer.files);
});

// Paste handler
document.addEventListener("keydown", function(e){
    if ((e.ctrlKey || e.metaKey) && e.key === "v") { e.preventDefault(); fmPaste(); }
});
</script>
