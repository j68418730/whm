<style>
.term-wrap{background:#0d1117;border:1px solid rgba(255,255,255,.12);border-radius:10px;overflow:hidden;font-family:'Cascadia Code','Fira Code','Consolas',monospace}
.term-bar{background:rgba(255,255,255,.05);padding:8px 14px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,.08);gap:8px;flex-wrap:wrap}
.term-bar .t-status{display:flex;align-items:center;gap:6px;font-size:12px;color:#8b949e}
.term-bar .t-status .dot{width:9px;height:9px;border-radius:50%;background:#f87171;transition:.2s}
.term-bar .t-status.connected .dot{background:#4ade80;box-shadow:0 0 8px rgba(74,222,128,.5)}
.term-bar .t-btn{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.04);color:#c9d1d9;text-decoration:none;transition:.1s}
.term-bar .t-btn:hover{background:rgba(0,140,255,.15);color:#58a6ff}
.term-bar .t-btn.danger:hover{background:rgba(248,113,113,.15);color:#f87171}
.term-screen{background:#0d1117;padding:10px 14px;min-height:420px;max-height:70vh;overflow:auto}
.term-screen pre{margin:0;color:#c9d1d9;font-family:'Cascadia Code','Fira Code','Consolas',monospace;font-size:14px;line-height:1.5;white-space:pre-wrap;word-break:break-all}
.term-hint{padding:6px 14px;font-size:11px;color:#484f58;border-top:1px solid rgba(255,255,255,.06)}
</style>

<div class="term-wrap">
<div class="term-bar">
  <div class="t-status" id="tStatus"><span class="dot"></span><span id="tStatusText">Disconnected</span></div>
  <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
    <span id="tSessionLabel" style="font-size:12px;color:#8b949e"></span>
    <a href="#" class="t-btn" id="tNewBtn">+ New</a>
    <a href="#" class="t-btn" id="tClearBtn">Clear</a>
    <a href="#" class="t-btn" id="tFsBtn">⛶ Fullscreen</a>
    <a href="#" class="t-btn danger" id="tKillBtn">✕ Kill</a>
  </div>
</div>
<div class="term-screen" id="tScreen"><pre id="tOutput"></pre></div>
<div class="term-hint">Real PTY shell (ROOT). Click inside and type. Ctrl+C / arrows / Tab / interactive apps supported.</div>
</div>

<script>
(function(){
var base = '/admin/server/terminal';
var sid = null, es = null, outEl = document.getElementById('tOutput'), screen = document.getElementById('tScreen');
var statusEl = document.getElementById('tStatus'), statusText = document.getElementById('tStatusText');
var offset = 0, buf = '', termW = 80, termH = 24;

function ansiEscape(str){
  str = str.replace(/\x1b\[(\d+(?:;\d+)*)m/g, function(m, codes){
    var html = '';
    codes.split(';').forEach(function(c){
      c = parseInt(c,10)||0;
      if (c===0) html += '</span>';
      else if (c===1) html += '<b>';
      else if (c===22) html += '</b>';
      else if (c>=30 && c<=37) html += '<span style="color:'+['#1b1f23','#f85149','#3fb950','#d29922','#58a6ff','#bc8cff','#39c5cf','#c9d1d9'][c-30]+'">';
      else if (c>=90 && c<=97) html += '<span style="color:'+['#484f58','#ff7b72','#7ee787','#d29922','#79c0ff','#d2a8ff','#56d4dd','#e6edf3'][c-90]+'">';
    });
    return html;
  });
  str = str.replace(/\x1b\[[0-9;?]*[A-Za-z]/g, '');
  str = str.replace(/\x1b[()][0-9A-B]/g, '');
  str = str.replace(/\x1b[=>]/g, '');
  return str;
}

function render(data){
  var text = new TextDecoder().decode(data);
  text = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
  if (/\x1b\[2J|\x1b\[H/.test(text)) { buf = ''; outEl.innerHTML = ''; }
  text = text.replace(/\x08/g, '');
  text = text.replace(/\x1b\[[0-9;?]*[A-Za-z]/g, '');
  text = text.replace(/\x1b[()][0-9A-B]/g, '').replace(/\x1b[=>]/g, '');
  if (!text) return;
  var escText = ansiEscape(text).replace(/</g,'&lt;').replace(/>/g,'&gt;');
  buf += text;
  outEl.innerHTML += escText;
  if (buf.length > 100000) { buf = buf.slice(-50000); outEl.innerHTML = outEl.innerHTML.slice(-100000); }
  screen.scrollTop = screen.scrollHeight;
}

function connect(){
  if (!sid) return;
  if (es) es.close();
  es = new EventSource(base + '/stream/' + sid + '?offset=' + offset);
  es.onopen = function(){ statusEl.classList.add('connected'); statusText.textContent = 'Connected'; };
  es.onerror = function(){ statusEl.classList.remove('connected'); statusText.textContent = 'Reconnecting…'; };
  es.onmessage = function(ev){
    if (ev.data === 'ping') return;
    var bytes = Uint8Array.from(atob(ev.data), function(c){ return c.charCodeAt(0); });
    render(bytes);
  };
  es.addEventListener('end', function(){ statusEl.classList.remove('connected'); statusText.textContent = 'Session ended'; });
}

function send(data){
  if (!sid) return;
  var x = new XMLHttpRequest();
  x.open('POST', base + '/input/' + sid, true);
  x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  x.send('data=' + encodeURIComponent(btoa(String.fromCharCode.apply(null, data))));
}

function resizeTerm(){
  var w = screen.clientWidth - 28;
  termW = Math.max(20, Math.floor(w / 8.5));
  termH = Math.max(10, Math.floor((screen.clientHeight - 20) / 21));
  if (sid) {
    var x = new XMLHttpRequest();
    x.open('POST', base + '/resize/' + sid, true);
    x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    x.send('cols=' + termW + '&rows=' + termH);
  }
}
window.addEventListener('resize', resizeTerm);

document.addEventListener('keydown', function(e){
  if (!sid) return;
  if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A') return;
  if (e.key === 'F11') { e.preventDefault(); toggleFs(); return; }
  if (e.ctrlKey && e.key.toLowerCase() === 'c') { send([3]); e.preventDefault(); return; }
  if (e.ctrlKey && e.key.toLowerCase() === 'd') { send([4]); e.preventDefault(); return; }
  if (e.ctrlKey && e.key.toLowerCase() === 'z') { send([26]); e.preventDefault(); return; }
  if (e.ctrlKey && e.key.toLowerCase() === 'l') { send([12]); e.preventDefault(); return; }
  if (e.key === 'Enter') { send([13]); e.preventDefault(); return; }
  if (e.key === 'Backspace') { send([127]); e.preventDefault(); return; }
  if (e.key === 'Tab') { send([9]); e.preventDefault(); return; }
  if (e.key === 'ArrowUp') { send([27,91,65]); e.preventDefault(); return; }
  if (e.key === 'ArrowDown') { send([27,91,66]); e.preventDefault(); return; }
  if (e.key === 'ArrowRight') { send([27,91,67]); e.preventDefault(); return; }
  if (e.key === 'ArrowLeft') { send([27,91,68]); e.preventDefault(); return; }
  if (e.key === 'Home') { send([27,91,72]); e.preventDefault(); return; }
  if (e.key === 'End') { send([27,91,70]); e.preventDefault(); return; }
  if (e.ctrlKey && e.key.toLowerCase() === 'u') { send([21]); e.preventDefault(); return; }
  if (e.ctrlKey && e.key.toLowerCase() === 'w') { send([23]); e.preventDefault(); return; }
  if (e.ctrlKey && e.key.toLowerCase() === 'r') { send([18]); e.preventDefault(); return; }
  if (e.key.length === 1) { send(new TextEncoder().encode(e.key)); e.preventDefault(); }
});

var hidden = document.createElement('input');
hidden.id = 'tHidden';
hidden.style.cssText = 'position:absolute;opacity:0;width:1px;height:1px';
document.body.appendChild(hidden);
screen.addEventListener('click', function(){ hidden.focus(); });

function newSession(){
  var x = new XMLHttpRequest();
  x.open('POST', base + '/session', true);
  x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  x.onload = function(){
    var r = JSON.parse(x.responseText);
    if (r.ok) {
      if (es) es.close();
      sid = r.session.id; offset = 0; buf = '';
      outEl.innerHTML = '';
      document.getElementById('tSessionLabel').textContent = r.session.label;
      connect();
      setTimeout(resizeTerm, 200);
    } else { statusText.textContent = 'Error: ' + r.error; }
  };
  x.send('cols=' + termW + '&rows=' + termH);
}
document.getElementById('tNewBtn').addEventListener('click', function(e){ e.preventDefault(); newSession(); });
document.getElementById('tClearBtn').addEventListener('click', function(e){ e.preventDefault(); outEl.innerHTML=''; buf=''; });
document.getElementById('tKillBtn').addEventListener('click', function(e){
  e.preventDefault();
  if (!sid) return;
  var x = new XMLHttpRequest();
  x.open('POST', base + '/kill/' + sid, true);
  x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
  x.onload = function(){ statusEl.classList.remove('connected'); statusText.textContent = 'Session killed'; if (es) es.close(); sid=null; };
  x.send('');
});
function toggleFs(){
  if (!document.fullscreenElement) { document.querySelector('.term-wrap').requestFullscreen().catch(function(){}); }
  else { document.exitFullscreen(); }
  setTimeout(resizeTerm, 300);
}
document.getElementById('tFsBtn').addEventListener('click', function(e){ e.preventDefault(); toggleFs(); });

newSession();
})();
</script>