// ─── LOGIN ───
let appConfig = {};

// Use API proxy through main process (handles self-signed certs)
async function apiGet(path) {
    try {
        return await api.apiRequest({
            url: 'https://panel.planet-hosts.com:2083' + path,
            headers: {'X-API-Key': 'ph_connector_082a37e266065a9e3047426a7119accb'}
        });
    } catch(e) {
        return {success: false, error: e.message};
    }
}

async function apiPost(path, body) {
    try {
        return await api.apiRequest({
            url: 'https://panel.planet-hosts.com:2083' + path,
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-API-Key': 'ph_connector_082a37e266065a9e3047426a7119accb'},
            body: body
        });
    } catch(e) {
        return {success: false, error: e.message};
    }
}

async function doLogin() {
    appConfig = {
        email: document.getElementById('lgEmail').value,
        password: document.getElementById('lgPass').value,
        bitrate: parseInt(document.getElementById('lgBitrate').value) || 128
    };
    
    const err = document.getElementById('loginError');
    if (!appConfig.email || !appConfig.password) {
        err.textContent = 'Enter your Planet Hosts email and password';
        err.style.display = 'block'; return;
    }
    err.style.display = 'none';
    
    // Try to authenticate and fetch station info via API proxy
    const d = await apiGet('/connector/dj/station?username=' + encodeURIComponent(appConfig.email));
    
    if (d.success && d.data) {
        appConfig.username = d.data.username;
        appConfig.stationName = d.data.station_name;
        appConfig.stationId = d.data.stream_id;
        appConfig.engine = d.data.engine;
        appConfig.stationPort = d.data.port;
        appConfig.server = '45.61.59.55';
        appConfig.port = 9002;
        
        await api.saveConfig(appConfig);
        
        // Switch to full menu
        await api.setMenuFull();
        
        document.getElementById('loginScreen').style.display = 'none';
        document.getElementById('appMain').style.display = 'flex';
        
        document.getElementById('tbStreamInfo').textContent = 
            `📡 ${appConfig.stationName} (${appConfig.stationPort || 9000})`;
        document.getElementById('ssStation').textContent = appConfig.stationName || '—';
        document.getElementById('ssBitrate').textContent = appConfig.bitrate + ' kbps';
        
        app.init();
    } else {
        // Fallback: use credentials as direct DJ login
        appConfig.username = appConfig.email;
        appConfig.server = '45.61.59.55';
        appConfig.port = 9002;
        appConfig.stationName = 'My Station';
        appConfig.stationId = 4;
        
        await api.saveConfig(appConfig);
        
        await api.setMenuFull();
        
        document.getElementById('loginScreen').style.display = 'none';
        document.getElementById('appMain').style.display = 'flex';
        document.getElementById('tbStreamInfo').textContent = '📡 Connected';
        app.init();
    }
}