// ─── STREAMING ───
const logContainer = document.createElement('div');
logContainer.style.cssText = 'position:fixed;bottom:32px;right:8px;width:320px;max-height:150px;background:rgba(13,17,23,.95);border:1px solid rgba(48,54,61,.3);border-radius:6px;padding:6px;font-family:monospace;font-size:10px;color:#8b949e;overflow-y:auto;z-index:50;display:none';
logContainer.id = 'streamLog';
document.body.appendChild(logContainer);

document.addEventListener('keydown', e => {
    if (e.ctrlKey && e.key === 'l') {
        e.preventDefault();
        logContainer.style.display = logContainer.style.display === 'none' ? 'block' : 'none';
    }
});

api.onStreamLog(msg => {
    if (msg.includes('size=') && msg.includes('time=')) return;
    logContainer.innerHTML += `<div>${msg.substring(0, 120)}</div>`;
    logContainer.scrollTop = logContainer.scrollHeight;
    if (logContainer.style.display === 'none') logContainer.style.display = 'block';
});

api.onStreamStopped(code => {
    if (app.isLive) {
        // Auto-advance: play next to the empty deck
        setTimeout(() => queue.playNext(), 500);
    }
});