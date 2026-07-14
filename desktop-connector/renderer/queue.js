// ─── QUEUE ───
const queue = {
    items: [],
    currentIndex: -1,
    syncTimer: null,
    
    add(song) {
        this.items.push(song);
        this.render();
        this.syncToServer();
    },
    
    remove(index) {
        this.items.splice(index, 1);
        if (this.currentIndex >= this.items.length) this.currentIndex = this.items.length - 1;
        this.render();
        this.syncToServer();
    },
    
    getNext() {
        if (this.items.length === 0) return null;
        return this.items[0];
    },
    shiftNext() {
        if (this.items.length === 0) return null;
        const song = this.items.shift();
        this.render();
        this.syncToServer();
        return song;
    },
    
    clear() {
        this.items = [];
        this.currentIndex = -1;
        this.render();
        this.syncToServer();
    },
    
    shuffle() {
        for (let i = this.items.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [this.items[i], this.items[j]] = [this.items[j], this.items[i]];
        }
        this.render();
        this.syncToServer();
    },
    
    move(from, to) {
        const item = this.items.splice(from, 1)[0];
        this.items.splice(to, 0, item);
        this.render();
        this.syncToServer();
    },
    
    playNext(which) {
        if (this.items.length === 0) return;
        if (!which) which = this.findEmptyDeck();
        if (!which) which = 'a';
        const song = this.items.shift();
        this.render();
        this.syncToServer();
        window.deck.load(which, song);
        if (!(which === 'a' ? deckA : deckB).playing) {
            setTimeout(() => window.deck.toggle(which), 200);
        }
    },
    findEmptyDeck() {
        if (!deckA.song || !deckA.playing) return 'a';
        if (!deckB.song || !deckB.playing) return 'b';
        return 'a'; // Default to A if both playing
    },
    
    save() {
        const a = document.createElement('a');
        const b = new Blob([JSON.stringify(this.items, null, 2)], {type:'application/json'});
        a.href = URL.createObjectURL(b);
        a.download = 'queue.json';
        a.click();
    },
    
    load() {
        const inp = document.createElement('input');
        inp.type = 'file';
        inp.accept = '.json';
        inp.onchange = async (e) => {
            try {
                const text = await e.target.files[0].text();
                const items = JSON.parse(text);
                items.forEach(item => this.items.push(item));
                this.render();
                this.syncToServer();
            } catch(ex) { alert('Invalid queue file'); }
        };
        inp.click();
    },
    
    startAutoSync() {
        this.syncToServer();
        this.syncTimer = setInterval(() => this.syncToServer(), 10000);
    },
    
    async syncToServer() {
        if (!appConfig || !appConfig.stationId) return;
        try {
            const items = this.items.map(s => ({
                title: s.title || 'Unknown',
                artist: s.artist || '',
                duration: s.duration || 0,
                file_path: s.path || s.file || ''
            }));
            await api.apiRequest({
                method: 'POST',
                path: '/connector/station/' + appConfig.stationId + '/queue',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ items })
            });
        } catch(e) { /* silent */ }
    },
    
    render() {
        const el = document.getElementById('queueBody');
        if (this.items.length === 0) {
            el.innerHTML = '<div style="color:#64748b;padding:8px;text-align:center;font-size:12px">Queue is empty. Double-click a song in the library to add it.</div>';
            return;
        }
        let html = '<table><thead><tr><th style="width:24px">#</th><th>Title</th><th>Artist</th><th style="width:40px">Time</th><th style="width:60px">Actions</th></tr></thead><tbody>';
        this.items.forEach((s, i) => {
            html += `<tr class="${i === this.currentIndex ? 'active' : ''}" draggable="true">
                <td>${i+1}</td>
                <td>${s.title || 'Unknown'}</td>
                <td>${s.artist || ''}</td>
                <td>${lib.formatTime(s.duration)}</td>
                <td>
                    <button onclick="queue.playNextFrom(${i},'a')" style="background:none;border:none;cursor:pointer;font-size:11px;color:#008cff;padding:1px">A</button>
                    <button onclick="queue.playNextFrom(${i},'b')" style="background:none;border:none;cursor:pointer;font-size:11px;color:#a855f7;padding:1px">B</button>
                    <button onclick="queue.remove(${i})" style="background:none;border:none;cursor:pointer;font-size:11px;color:#f85149;padding:1px">✕</button>
                </td>
            </tr>`;
        });
        html += '</tbody></table>';
        el.innerHTML = html;
        
        // Drag reorder
        let dragSrc = null;
        el.querySelectorAll('tr[draggable]').forEach(tr => {
            tr.addEventListener('dragstart', e => {
                dragSrc = Array.from(tr.parentNode.children).indexOf(tr);
                e.dataTransfer.setData('text/plain', '');
            });
            tr.addEventListener('dragover', e => e.preventDefault());
            tr.addEventListener('drop', e => {
                e.preventDefault();
                const target = Array.from(tr.parentNode.children).indexOf(tr);
                if (dragSrc !== null && dragSrc !== target) this.move(dragSrc, target);
                dragSrc = null;
            });
        });
    },
    
    playNextFrom(index, which) {
        if (index < 0 || index >= this.items.length) return;
        const song = this.items.splice(index, 1)[0];
        this.render();
        this.syncToServer();
        deck.load(which, song);
        setTimeout(() => deck.toggle(which), 200);
    }
};