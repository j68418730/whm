// ─── FOLDER BROWSER LIBRARY ───
const lib = {
    roots: [],
    currentFolder: null,
    currentFiles: [],
    contextTarget: null,
    
    async init() {
        const cfg = await api.getConfig();
        this.roots = cfg.musicFolders || [];
        if (this.roots.length === 0) {
            document.getElementById('libBody').innerHTML = `<div class="empty-msg">No music folders added.
                <br><br>
                <button onclick="api.importFolder()" style="padding:8px 20px;border-radius:5px;border:none;background:linear-gradient(135deg,#008cff,#005ec4);color:#fff;font-weight:600;cursor:pointer;font-size:13px">📂 Import Music Folder</button>
                <br><br>
                <span style="font-size:11px;color:#64748b">Or drag a folder here to add it</span>
            </div>`;
            // Also enable drag-and-drop to import
            this.setupDropTarget();
            return;
        }
        this.renderTree();
        if (this.roots[0]) {
            this.openFolder(this.roots[0]);
        }
    },
    
    setupDropTarget() {
        const el = document.getElementById('libBody');
        if (!el) return;
        el.addEventListener('dragover', e => { e.preventDefault(); el.style.border = '2px dashed rgba(0,140,255,.4)'; });
        el.addEventListener('dragleave', () => { el.style.border = 'none'; });
        el.addEventListener('drop', async e => {
            e.preventDefault();
            el.style.border = 'none';
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const path = files[0].path;
                if (path) {
                    // If it's a folder, import it directly
                    // We need to pass the path to the main process
                    // For now, trigger the import dialog
                    api.importFolder();
                }
            }
        });
    },
    
    renderTree() {
        const hdr = document.querySelector('#panelPlaylist .panel-hdr');
        if (!hdr) return;
        // Add import button to the cat row
        const catRow = hdr.querySelector('span[style*="gap:3px"]');
        if (catRow && !document.getElementById('libAddRoot')) {
            const btn = document.createElement('button');
            btn.id = 'libAddRoot';
            btn.textContent = '+Folder';
            btn.style.cssText = 'background:none;border:none;color:#58a6ff;cursor:pointer;font-size:9px';
            btn.onclick = () => api.importFolder();
            catRow.appendChild(btn);
        }
    },
    
    async openFolder(dir) {
        if (!dir) return;
        this.currentFolder = dir;
        const items = await api.listFolder(dir);
        this.currentFiles = items;
        // Filter dirs by category if not 'all'
        let filtered = items;
        if (this.currentCategory !== 'all') {
            const catFolders = items.filter(i => i.isDir && i.name.toLowerCase() === this.currentCategory);
            if (catFolders.length > 0) {
                // Open the matching category folder instead
                this.currentFolder = catFolders[0].path;
                const subItems = await api.listFolder(catFolders[0].path);
                this.currentFiles = subItems;
                this.renderFileList(subItems);
                this.renderBreadcrumb(catFolders[0].path);
                return;
            }
        }
        this.renderFileList(items);
        this.renderBreadcrumb(dir);
    },
    
    renderBreadcrumb(dir) {
        const hdr = document.querySelector('#panelPlaylist .panel-hdr');
        if (!hdr) return;
        let bc = hdr.querySelector('.lib-breadcrumb');
        if (!bc) {
            bc = document.createElement('span');
            bc.className = 'lib-breadcrumb';
            bc.style.cssText = 'font-size:9px;font-weight:400;text-transform:none;letter-spacing:0;margin-left:4px;color:#8b949e';
            hdr.querySelector('span:first-child')?.after(bc) || hdr.appendChild(bc);
        }
        // Show path relative to root
        const parts = dir.split(/[\\/]/);
        const rootParts = this.roots.map(r => r.split(/[\\/]/).pop());
        const idx = parts.findIndex(p => rootParts.includes(p));
        const display = idx >= 0 ? parts.slice(idx).join(' › ') : dir;
        bc.textContent = `📁 ${display}`;
    },
    
    renderFileList(items) {
        const el = document.getElementById('libBody');
        if (items.length === 0) {
            el.innerHTML = '<div class="empty-msg">This folder is empty</div>';
            return;
        }
        
        const dirs = items.filter(i => i.isDir);
        const files = items.filter(i => !i.isDir);
        
        let html = '<table><thead><tr><th style="width:24px"></th><th>Name</th><th style="width:60px">Size</th><th style="width:50px">Time</th></tr></thead><tbody>';
        
        // Parent folder link
        const parent = this.getParentDir(this.currentFolder);
        if (parent && !this.roots.includes(this.currentFolder)) {
            html += `<tr class="dir-row" data-path="${parent}"><td>📁</td><td>.. (Parent)</td><td></td><td></td></tr>`;
        }
        
        dirs.forEach(d => {
            html += `<tr class="dir-row" data-path="${d.path}"><td>📁</td><td>${d.name}</td><td></td><td></td></tr>`;
        });
        
        let fileIdx = 0;
        files.forEach(f => {
            const isAudio = /\.(mp3|wav|flac|ogg|aac|m4a|wma|mp4)$/i.test(f.name);
            if (!isAudio) return;
            const safeName = f.name.replace(/[<>&"']/g,'');
            const safePath = f.path.replace(/[<>&"']/g,'');
            html += `<tr class="file-row" data-path="${safePath}" draggable="true">
                <td>🎵</td>
                <td>${safeName}</td>
                <td>${this.formatSize(f.size)}</td>
                <td class="file-dur">...</td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        el.innerHTML = html;
        
        // Click handlers for dirs
        el.querySelectorAll('.dir-row').forEach(tr => {
            tr.onclick = () => this.openFolder(tr.dataset.path);
            tr.oncontextmenu = (e) => {
                e.preventDefault();
                this.showFolderContext(e, tr.dataset.path);
            };
        });
        
        // Click handlers for files
        const fileRows = el.querySelectorAll('.file-row');
        fileRows.forEach((tr) => {
            const filePath = tr.dataset.path;
            tr.ondblclick = () => {
                const song = { path: filePath, title: tr.cells[1].textContent, artist: 'Unknown' };
                queue.add(song);
            };
            tr.onclick = () => {
                fileRows.forEach(t => t.classList.remove('active'));
                tr.classList.add('active');
                this.contextTarget = filePath;
            };
            tr.oncontextmenu = (e) => {
                e.preventDefault();
                this.contextTarget = filePath;
                this.showFileContext(e, filePath, tr.cells[1].textContent);
            };
            tr.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', filePath);
            });
            
            // Load metadata async
            this.loadMetaByPath(filePath, tr);
        });
    },
    
    async loadMetaByPath(fp, tr) {
        try {
            const meta = await api.getFileMeta(fp);
            const durCell = tr.querySelector('.file-dur');
            if (durCell) durCell.textContent = meta.duration ? this.formatTime(meta.duration) : '?';
            tr.dataset.title = meta.title || tr.cells[1]?.textContent || '';
            tr.dataset.artist = meta.artist || 'Unknown';
            tr.dataset.duration = meta.duration || 0;
        } catch(e) {
            const durCell = tr.querySelector('.file-dur');
            if (durCell) durCell.textContent = '?';
        }
    },
    
    showFolderContext(e, path) {
        const menu = this.createContextMenu([
            { label: '📂 Open', action: () => this.openFolder(path) },
            { label: '📁 New Subfolder', action: () => this.createSubfolder(path) },
            { label: '✏️ Rename', action: () => this.renameFolder(path) },
            { label: '🗑 Delete', action: () => this.deleteFolder(path) }
        ], e.clientX, e.clientY);
    },
    
    showFileContext(e, path, name) {
        const song = {
            path,
            title: name,
            artist: 'Unknown',
            duration: 0
        };
        const menu = this.createContextMenu([
            { label: '▶ Play Next (A)', action: () => {
                queue.playNextFrom(0, 'a');
                queue.add(song);
            }},
            { label: '➕ Add to Queue', action: () => queue.add(song) },
            { label: '📝 Edit Metadata', action: () => this.editMeta(path) },
            { label: '🗑 Delete File', action: () => this.deleteFile(path) },
        ], e.clientX, e.clientY);
    },
    
    createContextMenu(items, x, y) {
        this.removeContextMenu();
        const menu = document.createElement('div');
        menu.className = 'context-menu';
        menu.style.cssText = `position:fixed;left:${x}px;top:${y}px;z-index:1000;background:#1c2128;border:1px solid rgba(48,54,61,.6);border-radius:6px;padding:4px;min-width:180px;box-shadow:0 8px 24px rgba(0,0,0,.4)`;
        items.forEach(item => {
            const btn = document.createElement('div');
            btn.style.cssText = 'padding:6px 10px;cursor:pointer;font-size:13px;border-radius:4px;color:#c9d1d9';
            btn.onmouseover = () => btn.style.background = 'rgba(0,140,255,.1)';
            btn.onmouseout = () => btn.style.background = 'none';
            btn.textContent = item.label;
            btn.onclick = () => { item.action(); this.removeContextMenu(); };
            menu.appendChild(btn);
        });
        document.body.appendChild(menu);
        // Close on click outside
        setTimeout(() => document.addEventListener('click', this.removeContextMenu, { once: true }), 10);
        return menu;
    },
    
    removeContextMenu() {
        document.querySelectorAll('.context-menu').forEach(m => m.remove());
    },
    
    async createNewFolder() {
        if (!this.currentFolder) {
            alert('Open a folder first, then create a subfolder inside it.');
            return;
        }
        const name = prompt('New folder name:', 'New Folder');
        if (name && name.trim()) {
            await api.createFolder(this.currentFolder, name.trim());
            this.openFolder(this.currentFolder);
        }
    },
    
    async createSubfolder(parent) {
        const name = prompt('Folder name:');
        if (name && name.trim()) {
            await api.createFolder(parent, name.trim());
            this.openFolder(this.currentFolder);
        }
    },
    
    async renameFolder(oldPath) {
        const oldName = oldPath.split(/[\\/]/).pop();
        const newName = prompt('New name:', oldName);
        if (newName && newName.trim() && newName !== oldName) {
            await api.renameFolder(oldPath, newName.trim());
            this.openFolder(this.getParentDir(oldPath));
        }
    },
    
    async deleteFolder(dir) {
        if (confirm(`Delete "${dir.split(/[\\/]/).pop()}" and all contents?`)) {
            await api.deleteFolder(dir);
            this.openFolder(this.getParentDir(dir));
            this.init(); // Refresh roots
        }
    },
    
    async editMeta(path) {
        const meta = await api.getFileMeta(path);
        document.getElementById('metaTitle').value = meta.title || '';
        document.getElementById('metaArtist').value = meta.artist || '';
        document.getElementById('metaAlbum').value = meta.album || '';
        document.getElementById('metaGenre').value = meta.genre || '';
        document.getElementById('metaYear').value = meta.year || '';
        document.getElementById('metaDialog').dataset.path = path;
        document.getElementById('metaDialog').style.display = 'flex';
    },
    
    async saveMeta() {
        const fp = document.getElementById('metaDialog').dataset.path;
        const tags = {
            title: document.getElementById('metaTitle').value,
            artist: document.getElementById('metaArtist').value,
            album: document.getElementById('metaAlbum').value,
            genre: document.getElementById('metaGenre').value,
            year: parseInt(document.getElementById('metaYear').value) || 0,
            track: 0
        };
        const ok = await api.writeTags(fp, tags);
        if (ok) {
            document.getElementById('metaDialog').style.display = 'none';
            this.openFolder(this.currentFolder);
        } else {
            alert('Failed to write tags');
        }
    },
    
    async deleteFile(path) {
        if (confirm('Delete this file?')) {
            await api.deleteFile(path);
            this.openFolder(this.currentFolder);
        }
    },
    
    getParentDir(dir) {
        const parts = dir.split(/[\\/]/);
        parts.pop();
        return parts.join('/');
    },
    
    formatSize(bytes) {
        if (!bytes) return '';
        if (bytes < 1024) return bytes + 'B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(0) + 'KB';
        return (bytes / 1048576).toFixed(1) + 'MB';
    },
    
    formatTime(secs) {
        if (!secs || secs <= 0) return '0:00';
        const m = Math.floor(secs / 60);
        const s = Math.floor(secs % 60);
        return m + ':' + String(s).padStart(2, '0');
    },
    
    setCategory(cat) {
        this.currentCategory = cat;
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        const btn = document.querySelector(`.cat-btn[data-cat="${cat}"]`);
        if (btn) btn.classList.add('active');
        if (this.currentFolder) this.openFolder(this.currentFolder);
    }
};

// Search handler (wired after login)
function initLibSearch() {
    const el = document.getElementById('libSearch');
    if (!el) return;
    el.addEventListener('input', e => {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('#libBody .file-row').forEach(tr => {
            const name = tr.cells[1]?.textContent?.toLowerCase() || '';
            tr.style.display = name.includes(q) ? '' : 'none';
        });
        document.querySelectorAll('#libBody .dir-row').forEach(tr => {
            tr.style.display = 'none';
        });
        if (!q) lib.openFolder(lib.currentFolder);
    });
}