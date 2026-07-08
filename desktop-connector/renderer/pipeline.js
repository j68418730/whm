// ─── AUDIO PIPELINE VISUALIZATION ───
const pipeline = {
    interval: null,
    
    toggle() {
        const el = document.getElementById('pipelineOverlay');
        const vis = el.style.display === 'flex';
        el.style.display = vis ? 'none' : 'flex';
        if (!vis) this.start();
        else this.stop();
    },
    
    start() {
        if (this.interval) clearInterval(this.interval);
        this.animate();
        this.interval = setInterval(() => this.animate(), 150);
    },
    
    stop() {
        if (this.interval) { clearInterval(this.interval); this.interval = null; }
    },
    
    animate() {
        const isLive = app.isLive;
        const aPlaying = deckA.playing;
        const bPlaying = deckB.playing;
        
        // Simulate levels based on deck activity
        const deckALevel = aPlaying ? 0.3 + Math.random() * 0.5 : 0;
        const deckBLevel = bPlaying ? 0.3 + Math.random() * 0.5 : 0;
        const mixerLevel = Math.min(1, deckALevel + deckBLevel + (Math.random() * 0.1));
        const eqLevel = mixerLevel * (0.7 + Math.random() * 0.3);
        const compLevel = Math.min(1, eqLevel * 1.1);
        const limiterLevel = Math.min(0.95, compLevel);
        const encoderLevel = isLive ? limiterLevel : 0;
        const streamLevel = isLive ? Math.max(0.3, limiterLevel) : 0;
        
        this.setLevel('pipeDeckA', deckALevel);
        this.setLevel('pipeDeckB', deckBLevel);
        this.setLevel('pipeMixer', mixerLevel);
        this.setLevel('pipeEq', eqLevel);
        this.setLevel('pipeComp', compLevel);
        this.setLevel('pipeLimiter', limiterLevel);
        this.setLevel('pipeEncoder', encoderLevel);
        this.setLevel('pipeStream', streamLevel);
        
        // Details
        const details = document.getElementById('pipeDetails');
        if (isLive) {
            const bitrate = appConfig.bitrate || 128;
            details.innerHTML = `
                <span>🎚 ${Math.round(mixerLevel * 100)}% mix</span>
                <span>🎛 EQ: 0/0/0 dB</span>
                <span>📊 ${Math.round(limiterLevel * 100)}% peak</span>
                <span>📡 ${bitrate} kbps</span>
            `;
        } else {
            details.innerHTML = '<span>⏸ Stream offline</span>';
        }
    },
    
    setLevel(id, level) {
        const el = document.getElementById(id);
        if (!el) return;
        const pct = Math.round(level * 100);
        let color = 'green';
        if (pct > 85) color = 'red';
        else if (pct > 65) color = 'yellow';
        el.innerHTML = `<div class="pipe-fill ${color}" style="width:${pct}%"></div>`;
    }
};