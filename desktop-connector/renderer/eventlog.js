// ─── EVENT LOG ───
const eventLog = {
    events: [],
    maxEvents: 200,
    
    log(icon, message) {
        const time = new Date();
        this.events.unshift({ icon, message, time: time.toLocaleTimeString() });
        if (this.events.length > this.maxEvents) this.events.pop();
        this.render();
    },
    
    render() {
        const el = document.getElementById('eventBody');
        if (!el) return;
        if (this.events.length === 0) {
            el.innerHTML = '<div class="empty-msg">No events yet</div>';
            return;
        }
        el.innerHTML = this.events.slice(0, 60).map(e =>
            `<div class="ev"><span class="i">${e.icon||'•'}</span><span class="t">${e.time}</span><span class="m">${e.message}</span></div>`
        ).join('');
    },
    
    clear() {
        this.events = [];
        this.render();
    }
};

// ─── WIRE EVENT LOG TO APP EVENTS ───

// Track deck events
const origToggle = deck.toggle;
deck.toggle = function(which) {
    const d = which === 'a' ? deckA : deckB;
    const wasPlaying = d.playing;
    origToggle.call(this, which);
    if (!wasPlaying && d.playing) {
        eventLog.log('▶', `Track started: ${d.song?.title || 'Unknown'} — ${d.song?.artist || ''}`);
    } else if (wasPlaying && !d.playing) {
        eventLog.log('⏸', `Track paused: ${d.song?.title || 'Unknown'}`);
    }
};

const origStop = deck.stop;
deck.stop = function(which) {
    const d = which === 'a' ? deckA : deckB;
    if (d.song && d.playing) {
        eventLog.log('⏹', `Track ended: ${d.song.title}`);
    }
    origStop.call(this, which);
};

const origStream = app.toggleStream;
app.toggleStream = async function() {
    await origStream.call(this);
    if (this.isLive) {
        eventLog.log('🔴', 'Stream started — Live DJ on air');
    } else {
        eventLog.log('⏹', 'Stream stopped');
    }
};

const origMic = app.toggleMic || function(){};
app.toggleMic = function() {
    if (origMic !== app.toggleMic) origMic.call(this);
    const btn = document.getElementById('micBtn');
    eventLog.log('🎤', btn?.style.color === '#3fb950' ? 'Microphone activated' : 'Microphone deactivated');
};

// Login event
eventLog.log('🔌', 'Session started — Planet Hosts Studio');

// AUX events
const origAux = deck.auxPlay;
deck.auxPlay = async function(type) {
    await origAux.call(this, type);
    eventLog.log('🔔', `AUX triggered: ${type}`);
};

// Stream errors from log
api.onStreamLog(msg => {
    if (msg.toLowerCase().includes('error') || msg.toLowerCase().includes('fail')) {
        eventLog.log('⚠', msg.substring(0, 80));
    }
});