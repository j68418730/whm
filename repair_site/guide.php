<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>User Guide – Planet Hosts Studio</title>
<meta name="description" content="Complete Planet Hosts Studio user guide. Learn how to broadcast, manage playlists, use the mixer, AutoDJ, and more.">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📡</text></svg>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#02050e;--bg2:#0a0f1e;--bg3:#111827;--card:rgba(15,23,42,.5);--border:rgba(56,189,248,.08);--text:#e2e8f0;--text2:#94a3b8;--text3:#64748b;--accent:#38bdf8;--accent2:#0ea5e9;--grad:linear-gradient(135deg,#38bdf8,#0ea5e9);--grad2:linear-gradient(135deg,rgba(56,189,248,.1),rgba(14,165,233,.05))}
[data-theme=light]{--bg:#f8fafc;--bg2:#f1f5f9;--bg3:#e2e8f0;--card:rgba(255,255,255,.8);--border:rgba(0,0,0,.08);--text:#0f172a;--text2:#475569;--text3:#94a3b8;--accent:#0284c7;--accent2:#0369a1;--grad:linear-gradient(135deg,#0284c7,#0ea5e9);--grad2:linear-gradient(135deg,rgba(2,132,199,.08),rgba(14,165,233,.04))}
html{scroll-behavior:smooth}
body{font-family:Inter,sans-serif;background:var(--bg);color:var(--text);line-height:1.7;overflow-x:hidden;transition:background .3s,color .3s}
::-webkit-scrollbar{width:8px}
::-webkit-scrollbar-track{background:var(--bg)}
::-webkit-scrollbar-thumb{background:var(--text3);border-radius:4px}
.container{max-width:860px;margin:0 auto;padding:0 24px}
nav{position:fixed;top:0;left:0;right:0;z-index:1000;background:rgba(2,5,14,.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-bottom:1px solid var(--border)}
[data-theme=light] nav{background:rgba(248,250,252,.9)}
.nav-inner{display:flex;align-items:center;justify-content:space-between;height:56px;max-width:860px;margin:0 auto;padding:0 24px}
.nav-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:15px;text-decoration:none;color:var(--text)}
.nav-logo .logo-icon{width:28px;height:28px;background:var(--grad);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;flex-shrink:0}
.nav-right{display:flex;gap:8px;align-items:center}
.nav-right a{color:var(--text2);text-decoration:none;font-size:12px;font-weight:500;padding:5px 10px;border-radius:6px;transition:.2s}
.nav-right a:hover{color:var(--text);background:var(--grad2)}
.theme-toggle{background:none;border:none;color:var(--text2);font-size:16px;cursor:pointer;padding:4px 6px;border-radius:6px;transition:.2s}
.theme-toggle:hover{color:var(--text);background:var(--grad2)}
.hero{padding:80px 0 40px;text-align:center}
.hero h1{font-size:36px;font-weight:900;letter-spacing:-1px;margin-bottom:6px;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.hero p{color:var(--text2);font-size:14px}
.content{padding:0 0 60px}
.content h2{font-size:24px;font-weight:800;margin:48px 0 16px;padding-bottom:8px;border-bottom:2px solid var(--border);color:var(--text)}
.content h3{font-size:18px;font-weight:700;margin:32px 0 10px;color:var(--text)}
.content h4{font-size:15px;font-weight:600;margin:24px 0 8px;color:var(--text2)}
.content p{font-size:14px;color:var(--text2);margin-bottom:12px}
.content ul,.content ol{padding-left:20px;margin-bottom:14px}
.content li{font-size:14px;color:var(--text2);margin-bottom:4px}
.content strong{color:var(--text)}
.content code{background:var(--bg3);padding:2px 6px;border-radius:4px;font-size:12px;color:var(--accent)}
.content table{width:100%;border-collapse:collapse;margin:16px 0;font-size:13px}
.content th,.content td{padding:8px 12px;text-align:left;border:1px solid var(--border)}
.content th{background:var(--bg3);color:var(--text);font-weight:600}
.content td{color:var(--text2)}
.content blockquote{border-left:3px solid var(--accent);padding:10px 16px;margin:16px 0;background:var(--card);border-radius:0 8px 8px 0;color:var(--text2);font-size:14px}
.content blockquote strong{color:var(--text)}
.content hr{margin:32px 0;border:none;border-top:1px solid var(--border)}
.toc{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px 24px;margin-bottom:32px}
.toc h3{font-size:14px;font-weight:700;margin-bottom:10px;color:var(--text);border:none}
.toc ol{padding-left:20px}
.toc li{font-size:13px;margin-bottom:4px}
.toc a{color:var(--accent);text-decoration:none}
.toc a:hover{text-decoration:underline}
footer{text-align:center;padding:24px 0;border-top:1px solid var(--border);font-size:12px;color:var(--text3)}
footer a{color:var(--accent2);text-decoration:none}
@media(max-width:600px){
.hero h1{font-size:26px}
.content h2{font-size:20px}
.content table{font-size:12px}
.content th,.content td{padding:5px 8px}
}
</style>
</head>
<body>

<nav>
<div class="nav-inner">
<a href="/" class="nav-logo"><span class="logo-icon">📡</span> Planet Hosts Studio</a>
<div class="nav-right">
<a href="/">Home</a>
<a href="/#downloads">Downloads</a>
<button class="theme-toggle" onclick="toggleTheme()" id="themeBtn" aria-label="Toggle theme">🌙</button>
</div>
</div>
</nav>

<div class="hero">
<div class="container">
<h1>Planet Hosts Studio — User Guide</h1>
<p>Version 1.0 — A professional broadcast automation and streaming application.</p>
</div>
</div>

<div class="content">
<div class="container">

<div class="toc">
<h3>Table of Contents</h3>
<ol>
<li><a href="#s1">Getting Started</a></li>
<li><a href="#s2">Station Selection</a></li>
<li><a href="#s3">Broadcast Interface Overview</a></li>
<li><a href="#s4">Decks (A &amp; B)</a></li>
<li><a href="#s5">Mixer &amp; Crossfader</a></li>
<li><a href="#s6">Voice FX (Microphone)</a></li>
<li><a href="#s7">Voice Track Recording</a></li>
<li><a href="#s8">Playlist Manager</a></li>
<li><a href="#s9">Track Metadata Editor &amp; MusicBrainz</a></li>
<li><a href="#s10">AutoDJ</a></li>
<li><a href="#s11">Queue</a></li>
<li><a href="#s12">Cart Wall</a></li>
<li><a href="#s13">Requests</a></li>
<li><a href="#s14">Streaming / Broadcasting</a></li>
<li><a href="#s15">Event Scheduler</a></li>
<li><a href="#s16">Clock Widget</a></li>
<li><a href="#s17">History</a></li>
<li><a href="#s18">Statistics</a></li>
<li><a href="#s19">Relay Manager</a></li>
<li><a href="#s20">PAL Scripts</a></li>
<li><a href="#s21">Settings</a></li>
<li><a href="#s22">Desktop Layout</a></li>
</ol>
</div>

<h2 id="s1">1. Getting Started</h2>
<h3>Login</h3>
<p>Launch the application to reach the <strong>Login</strong> screen. Enter your credentials:</p>
<ul>
<li><strong>Username</strong> — Your PlanetHosts account username</li>
<li><strong>Password</strong> — Your account password</li>
<li><strong>API Key</strong> — Your API authentication key</li>
<li><strong>API Base URL</strong> — The server URL (defaults to the PlanetHosts API)</li>
</ul>
<p>Check <strong>Remember Me</strong> to save your credentials for next launch. Click <strong>Login</strong> to authenticate. On success you'll proceed to station selection.</p>

<hr>

<h2 id="s2">2. Station Selection</h2>
<p>After login, choose which stations to broadcast to:</p>
<ul>
<li>Select one or more stations from the list</li>
<li>Click <strong>Connect</strong> to enter the broadcast interface</li>
</ul>
<p>The <code>StationSelectionViewModel</code> loads your station list from the API and pre-selects any previously chosen stations.</p>

<hr>

<h2 id="s3">3. Broadcast Interface Overview</h2>
<p>The broadcast interface is a multi-panel desktop workspace organized across <strong>two canvases (Desktop A and B)</strong>. Each panel is a draggable, resizable <code>DockablePanel</code>.</p>
<h3>Desktop A panels:</h3>
<table>
<thead><tr><th>Panel</th><th>Default Position</th><th>Description</th></tr></thead>
<tbody>
<tr><td><strong>Deck A</strong></td><td>Top-left</td><td>Primary playback deck</td></tr>
<tr><td><strong>Deck B</strong></td><td>Top-center</td><td>Secondary playback deck</td></tr>
<tr><td><strong>Voice FX</strong></td><td>Top-right</td><td>Microphone controls and processing</td></tr>
<tr><td><strong>Playlist</strong></td><td>Middle-left</td><td>Folder/track browser and scheduling</td></tr>
<tr><td><strong>Queue</strong></td><td>Middle-center</td><td>Upcoming tracks</td></tr>
<tr><td><strong>Mixer</strong></td><td>Middle-right</td><td>Volume sliders and crossfader</td></tr>
<tr><td><strong>AutoDJ</strong></td><td>Bottom-left</td><td>Automated playlist rotation</td></tr>
<tr><td><strong>Cart Wall</strong></td><td>Bottom-center</td><td>Sound effect buttons (jingles, ads, drops, sweeps)</td></tr>
<tr><td><strong>History</strong></td><td>Bottom-right</td><td>Recently played tracks</td></tr>
<tr><td><strong>Statistics</strong></td><td>Far-bottom-left</td><td>Listener stats</td></tr>
<tr><td><strong>Requests</strong></td><td>Far-bottom-center</td><td>Listener song requests</td></tr>
<tr><td><strong>Clock</strong></td><td>Far-bottom-right</td><td>Multi-timezone clock</td></tr>
<tr><td><strong>Fader</strong></td><td>Far-bottom-right</td><td>Crossfader control buttons</td></tr>
</tbody>
</table>
<p>Menu items at the top provide access to: <strong>Voice Track</strong>, <strong>Settings</strong>, <strong>Playlist Manager</strong>, and window navigation.</p>

<hr>

<h2 id="s4">4. Decks (A &amp; B)</h2>
<p>Each deck functions as an independent audio player.</p>
<h3>Controls</h3>
<table>
<thead><tr><th>Button</th><th>Action</th></tr></thead>
<tbody>
<tr><td><strong>Load</strong></td><td>Open a file dialog to load an audio file</td></tr>
<tr><td><strong>Play</strong></td><td>Start playback (if no file loaded, pulls from queue)</td></tr>
<tr><td><strong>Pause</strong></td><td>Pause playback</td></tr>
<tr><td><strong>Stop</strong></td><td>Stop playback</td></tr>
<tr><td><strong>Cue</strong></td><td>Seek to the beginning of the track</td></tr>
<tr><td><strong>CP</strong> (Cue Point)</td><td>Save current position as a cue point</td></tr>
<tr><td><strong>Skip</strong></td><td>Skip to the next track in the queue</td></tr>
<tr><td><strong>EQ</strong></td><td>Toggle EQ info display</td></tr>
<tr><td><strong>Preview</strong></td><td>Toggle preview mode</td></tr>
<tr><td><strong>Talk Over</strong></td><td>Toggle talkover mode</td></tr>
<tr><td><strong>Air</strong></td><td>Toggle air status (send to stream)</td></tr>
<tr><td><strong>Cue Monitor</strong></td><td>Toggle cue monitoring</td></tr>
<tr><td><strong>Volume Slider</strong></td><td>Adjust deck volume</td></tr>
</tbody>
</table>
<h3>Display Fields</h3>
<p><strong>Track Title</strong> — Current song name<br>
<strong>Artist</strong>, <strong>Album</strong>, <strong>Year</strong>, <strong>Genre</strong>, <strong>BPM</strong>, <strong>Bitrate</strong>, <strong>File Type</strong>, <strong>Sample Rate</strong><br>
<strong>Position</strong> — Elapsed time / Remaining time / Total duration<br>
<strong>Waveform/Level</strong> — Visual audio level meters</p>
<h3>Track History</h3>
<p>Every played track is recorded automatically: <code>TrackPlayed</code> delegates fire to update both the <strong>History</strong> panel and <strong>Statistics</strong>.</p>
<h3>Deck Alternation</h3>
<p>When a track ends on one deck and the other deck is not playing, the next track is automatically started on the opposite deck. This is wired through <code>PlaybackEndedAsync</code> delegates.</p>

<hr>

<h2 id="s5">5. Mixer &amp; Crossfader</h2>
<p>The mixer provides volume control for all audio sources.</p>
<h3>Volume Sliders</h3>
<table>
<thead><tr><th>Slider</th><th>Controls</th></tr></thead>
<tbody>
<tr><td><strong>Deck A</strong></td><td>Volume for Deck A (adjusted by crossfader position)</td></tr>
<tr><td><strong>Deck B</strong></td><td>Volume for Deck B (adjusted by crossfader position)</td></tr>
<tr><td><strong>Mic</strong></td><td>Microphone input volume</td></tr>
<tr><td><strong>Cart</strong></td><td>Cart Wall volume</td></tr>
<tr><td><strong>AutoDJ</strong></td><td>AutoDJ engine volume</td></tr>
<tr><td><strong>Line</strong></td><td>Line input volume</td></tr>
<tr><td><strong>Master</strong></td><td>Overall master output volume</td></tr>
</tbody>
</table>
<h3>VU Meters</h3>
<p>Each source has a simulated VU meter showing audio level activity.</p>
<h3>Crossfader</h3>
<p>The crossfader blends between Deck A and Deck B:</p>
<ul>
<li><strong>Fully Left (0)</strong> — Only Deck A audible (Deck B silent)</li>
<li><strong>Center (50)</strong> — Both decks at full volume</li>
<li><strong>Fully Right (100)</strong> — Only Deck B audible (Deck A silent)</li>
</ul>
<h3>Fade Buttons (Fader panel)</h3>
<ul>
<li><strong>Fade A → B</strong> — Starts the next queued track on Deck B, then crossfades from A to B over 2 seconds</li>
<li><strong>Fade B → A</strong> — Starts the next queued track on Deck A, then crossfades from B to A over 2 seconds</li>
</ul>
<p>The fade animation runs at 30ms intervals (~67 steps). When a fade starts, the <code>FadeStarted</code> event triggers the destination deck to begin playing.</p>
<h3>Master Mute</h3>
<p>The <strong>Mute</strong> toggle mutes all local speakers (Decks A/B, AutoDJ, Microphone, Cart Wall, and Master output) without affecting the streaming output.</p>

<hr>

<h2 id="s6">6. Voice FX (Microphone)</h2>
<p>The Voice FX panel provides microphone control and audio processing.</p>
<h3>Controls</h3>
<table>
<thead><tr><th>Control</th><th>Action</th></tr></thead>
<tbody>
<tr><td><strong>Vertical Fader</strong></td><td>Mic gain / volume level</td></tr>
<tr><td><strong>Level Meter</strong></td><td>Green vertical bar showing mic input level (with clipping indication)</td></tr>
<tr><td><strong>Mic On</strong></td><td>Toggle the microphone on/off</td></tr>
<tr><td><strong>Auto</strong></td><td>Toggle auto-ducking (music volume reduces when mic is active)</td></tr>
<tr><td><strong>Air</strong></td><td>Toggle air status (mic sent to stream)</td></tr>
<tr><td><strong>Cue</strong></td><td>Toggle cue monitoring</td></tr>
<tr><td><strong>Press To Talk</strong></td><td>Hold to activate mic (releases when mouse is released)</td></tr>
<tr><td><strong>Lock Talk</strong></td><td>Check to keep mic locked on without holding the button</td></tr>
<tr><td><strong>Config</strong></td><td>Opens the Voice FX Configuration dialog</td></tr>
<tr><td><strong>EQ</strong></td><td>Expands the processing section</td></tr>
</tbody>
</table>
<h3>How Mic Activation Works</h3>
<p>The mic is <strong>live</strong> when all three conditions are true:</p>
<ol>
<li><strong>Mic On</strong> is enabled</li>
<li>Either <strong>Lock Talk</strong> is checked OR <strong>Press To Talk</strong> is being held</li>
</ol>
<h3>Processing Section (collapsible)</h3>
<ul>
<li><strong>Compressor</strong> — Compresses dynamic range (0–100)</li>
<li><strong>Noise Gate</strong> — Filters background noise below threshold (0–100)</li>
<li><strong>Limiter</strong> — Prevents audio clipping (0–100)</li>
<li><strong>EQ Low / Mid / High</strong> — Three-band equalizer</li>
</ul>
<h3>Auto-Ducking</h3>
<p>When enabled, active mic automatically reduces music source volumes to 25%. Original levels are restored when the mic is deactivated.</p>
<h3>Voice FX Configuration Dialog</h3>
<p>Opened by clicking <strong>Config</strong> in the Voice FX panel:</p>
<table>
<thead><tr><th>Setting</th><th>Description</th></tr></thead>
<tbody>
<tr><td><strong>Input</strong></td><td>Select microphone input device (enumerated from system)</td></tr>
<tr><td><strong>Output</strong></td><td>Select output device</td></tr>
<tr><td><strong>Fade Curve</strong></td><td>Linear / Logarithmic / Exponential</td></tr>
<tr><td><strong>Levels (L/R)</strong></td><td>Left and right channel level faders</td></tr>
<tr><td><strong>Preview</strong></td><td>Visual graph area</td></tr>
</tbody>
</table>

<hr>

<h2 id="s7">7. Voice Track Recording</h2>
<p>Voice Track lets you record audio and upload it to a playlist. Access via <strong>Menu → Voice Track</strong>.</p>
<h3>Workflow</h3>
<ol>
<li><strong>Load Playlists</strong> — Fetches available playlists from the API (falls back to local library folders)</li>
<li><strong>Record</strong> — Click the red <strong>Record</strong> button. Recording saves to the voice tracks folder with a timestamp filename</li>
<li><strong>Stop</strong> — Click <strong>Stop</strong> to end recording</li>
<li><strong>Preview</strong> — Listen to the recording before uploading</li>
<li><strong>Set Metadata</strong> — Enter Title and Artist for the track</li>
<li><strong>Choose Target</strong> — Select a destination playlist from the dropdown</li>
<li><strong>Upload</strong> — Uploads the recording to the server (or saves locally if offline)</li>
<li><strong>Discard</strong> — Delete the recorded file and start over</li>
</ol>

<h2 id="s8">8. Playlist Manager</h2>
<p>Access via <strong>Menu → Playlist Manager</strong>. This is your central library management hub.</p>
<h3>Layout</h3>
<ul>
<li><strong>Left panel</strong> — Folder list (library directories + special "Library" virtual folder)</li>
<li><strong>Right panel</strong> — Track list for the selected folder</li>
<li><strong>Search bar</strong> — Filter tracks by title/artist</li>
</ul>
<h3>Virtual "Library" Folder</h3>
<p>The <strong>Library</strong> folder (ID <code>__library__</code>) appears at the top of the folder list. When selected, it aggregates <strong>all tracks from all folders</strong> into a single view.</p>
<h3>Folder Operations</h3>
<table>
<thead><tr><th>Action</th><th>How</th></tr></thead>
<tbody>
<tr><td><strong>New Folder</strong></td><td>Enter name in "New Folder" textbox, click <strong>New</strong></td></tr>
<tr><td><strong>Rename Folder</strong></td><td>Select folder, enter new name in rename field, click <strong>Rename</strong></td></tr>
<tr><td><strong>Delete Folder</strong></td><td>Select folder, click <strong>Delete</strong> (Library virtual folder cannot be deleted)</td></tr>
<tr><td><strong>Add Folder</strong></td><td>Click <strong>Add Folder</strong>, pick a directory — recursively scans and imports audio files</td></tr>
<tr><td><strong>Add Music</strong></td><td>Click <strong>Add Music</strong>, select audio files to import into selected folder</td></tr>
</tbody>
</table>

<h2 id="s9">9. Track Metadata Editor &amp; MusicBrainz</h2>
<p>Right-click any track in the playlist and select <strong>Edit Metadata</strong> to open the full metadata editor.</p>

<h2 id="s10">10. AutoDJ</h2>
<p>AutoDJ automates playlist rotation and queue management for continuous playback.</p>
<h3>Mode</h3>
<ul>
<li><strong>Auto</strong> — Automatically advances through rotation</li>
<li><strong>Manual</strong> — Manual track selection</li>
</ul>
<h3>Controls</h3>
<table>
<thead><tr><th>Button</th><th>Action</th></tr></thead>
<tbody>
<tr><td><strong>Load Rotation</strong></td><td>Load selected playlist into rotation</td></tr>
<tr><td><strong>Start</strong></td><td>Begin AutoDJ playback</td></tr>
<tr><td><strong>Stop</strong></td><td>Stop AutoDJ</td></tr>
<tr><td><strong>Skip</strong></td><td>Skip to next track</td></tr>
<tr><td><strong>Reload</strong></td><td>Reload rotation from selected playlist</td></tr>
<tr><td><strong>Force Rotation</strong></td><td>Force-advance to next track</td></tr>
<tr><td><strong>Set Mode (Auto/Manual)</strong></td><td>Toggle auto/manual mode</td></tr>
</tbody>
</table>
<h3>Queue</h3>
<table>
<thead><tr><th>Button</th><th>Action</th></tr></thead>
<tbody>
<tr><td><strong>Remove Selected</strong></td><td>Remove the selected queue item</td></tr>
<tr><td><strong>▲ Move Up</strong></td><td>Move selected item up</td></tr>
<tr><td><strong>▼ Move Down</strong></td><td>Move selected item down</td></tr>
<tr><td><strong>Clear Queue</strong></td><td>Empty the entire queue</td></tr>
</tbody>
</table>

<h2 id="s11">11. Queue</h2>
<p>The <strong>Queue</strong> panel shows tracks loaded into the AutoDJ rotation.</p>
<h3>Controls</h3>
<table>
<thead><tr><th>Button</th><th>Action</th></tr></thead>
<tbody>
<tr><td><strong>Remove</strong></td><td>Remove selected track from queue</td></tr>
<tr><td><strong>▲</strong></td><td>Move selected track up</td></tr>
<tr><td><strong>▼</strong></td><td>Move selected track down</td></tr>
</tbody>
</table>

<h2 id="s12">12. Cart Wall</h2>
<p>The Cart Wall provides instant-play sound effects and audio clips organized into groups.</p>
<h3>Default Groups</h3>
<table>
<thead><tr><th>Group</th><th>Purpose</th></tr></thead>
<tbody>
<tr><td><strong>Jingles</strong></td><td>Station jingles</td></tr>
<tr><td><strong>Ad</strong></td><td>Commercial advertisements</td></tr>
<tr><td><strong>Drop</strong></td><td>Sound drops and effects</td></tr>
<tr><td><strong>Sweep</strong></td><td>Sweepers and transitions</td></tr>
</tbody>
</table>

<h2 id="s13">13. Requests</h2>
<p>The Requests panel polls the server every 10 seconds for listener song requests.</p>

<h2 id="s14">14. Streaming / Broadcasting</h2>
<h3>Adding a Stream</h3>
<ol>
<li>Click <strong>+ Add Stream</strong> in the Streams panel</li>
<li>Select an encoder plugin (from available providers)</li>
<li>Configure stream settings in the Stream Config window</li>
</ol>
<table>
<thead><tr><th>Setting</th><th>Description</th></tr></thead>
<tbody>
<tr><td><strong>Stream Type</strong></td><td>Icecast / SHOUTcast v1 / SHOUTcast v2</td></tr>
<tr><td><strong>Hostname</strong></td><td>Streaming server address</td></tr>
<tr><td><strong>Port</strong></td><td>Server port</td></tr>
<tr><td><strong>Username</strong></td><td>Authentication username</td></tr>
<tr><td><strong>Password</strong></td><td>Authentication password</td></tr>
<tr><td><strong>Mount Point</strong></td><td>Icecast mount point (e.g. <code>/stream.mp3</code>)</td></tr>
<tr><td><strong>Codec</strong></td><td>MP3 / AAC / AAC+ / OGG / Opus / FLAC / WAV</td></tr>
<tr><td><strong>Bitrate</strong></td><td>Stream bitrate</td></tr>
<tr><td><strong>Auto Connect</strong></td><td>Automatically connect on startup</td></tr>
</tbody>
</table>
<h3>Protocol Support</h3>
<ul>
<li><strong>Icecast</strong> — PUT-based source protocol</li>
<li><strong>SHOUTcast v2</strong> — Standard SOURCE protocol</li>
<li><strong>SHOUTcast v1</strong> — Two-step handshake (password first, then headers)</li>
</ul>
<h3>Encoder Pipeline</h3>
<p>Audio flows: <strong>PCM Capture</strong> → <strong>Encoder</strong> → <strong>Streamer</strong> → <strong>Server</strong></p>

<h2 id="s15">15. Event Scheduler</h2>
<p>The scheduler panel displays scheduled playlist events as bordered cards. A 30-second timer checks all schedule entries.</p>

<h2 id="s16">16. Clock Widget</h2>
<p>The clock panel shows the current time and multi-timezone clocks. Zone clocks update every second and persist across sessions.</p>

<h2 id="s17">17. History</h2>
<p>The History panel tracks recently played songs. Each entry shows timestamp, artist, title, and duration. Capped at <strong>200 entries</strong>.</p>

<h2 id="s18">18. Statistics</h2>
<table>
<thead><tr><th>Field</th><th>Description</th></tr></thead>
<tbody>
<tr><td><strong>Current Listeners</strong></td><td>Live listener count from server</td></tr>
<tr><td><strong>Peak Listeners</strong></td><td>Peak listener count</td></tr>
<tr><td><strong>Unique Listeners</strong></td><td>Unique listener count</td></tr>
<tr><td><strong>Current Song</strong></td><td>Currently playing song title</td></tr>
<tr><td><strong>Current DJ</strong></td><td>DJ name</td></tr>
<tr><td><strong>Bitrate</strong></td><td>Stream bitrate</td></tr>
<tr><td><strong>Uptime</strong></td><td>Session uptime (hh:mm:ss)</td></tr>
</tbody>
</table>

<h2 id="s19">19. Relay Manager</h2>
<p>The Relay Manager lets you configure relay/re-broadcast targets.</p>

<h2 id="s20">20. PAL Scripts</h2>
<p>PAL (Programming Automation Language) scripts enable custom automation.</p>
<table>
<thead><tr><th>Command</th><th>Description</th></tr></thead>
<tbody>
<tr><td><code>Enqueue(query)</code></td><td>Search library for matching track and add to queue</td></tr>
<tr><td><code>Log(message)</code></td><td>Append text to the script output log</td></tr>
<tr><td><code>Lock()</code></td><td>Lock AutoDJ to manual mode (prevents auto-play)</td></tr>
<tr><td><code>Unlock()</code></td><td>Unlock AutoDJ back to auto mode</td></tr>
</tbody>
</table>

<h2 id="s21">21. Settings</h2>
<p>Access via <strong>Menu → Settings</strong>. Configure API URL, Remember Me, station info, and FTP settings.</p>

<h2 id="s22">22. Desktop Layout</h2>
<p>The broadcast interface uses a canvas-based layout with draggable panels across two desktops. Panel positions save automatically.</p>

</div>
</div>

<footer>
<div class="container">
<p>&copy; 2026 Planet Hosts. <a href="/">Back to Studio Home</a></p>
</div>
</footer>

<script>
function toggleTheme(){
var html=document.documentElement;
var btn=document.getElementById('themeBtn');
if(html.getAttribute('data-theme')==='dark'){
html.setAttribute('data-theme','light');
btn.textContent='☀️';
localStorage.setItem('phs-theme','light');
}else{
html.setAttribute('data-theme','dark');
btn.textContent='🌙';
localStorage.setItem('phs-theme','dark');
}
}
if(localStorage.getItem('phs-theme')==='light'){
document.documentElement.setAttribute('data-theme','light');
document.getElementById('themeBtn').textContent='☀️';
}
</script>
</body>
</html>
