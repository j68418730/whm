#!/usr/bin/env node

const path = require('path');
const fs = require('fs');
const readline = require('readline');

process.chdir(path.dirname(__dirname));

const configPath = path.join(process.cwd(), 'config.json');

// First-run setup wizard
if (!fs.existsSync(configPath)) {
    console.log('');
    console.log('  ╔═══════════════════════════════════════════════╗');
    console.log('  ║      Planet Hosts Desktop Connector          ║');
    console.log('  ║         Setup Wizard                         ║');
    console.log('  ╚═══════════════════════════════════════════════╝');
    console.log('');
    
    const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
    
    const ask = (q) => new Promise((r) => rl.question(q, r));
    
    (async () => {
        console.log('  Enter your Planet Hosts API credentials:');
        console.log('  (Get these from your Dashboard → Settings → API)');
        console.log('');
        const apiKey = await ask('  API Key: ');
        const apiUrl = await ask('  API URL [https://planet-hosts.com:2083]: ') || 'https://planet-hosts.com:2083';
        const stationId = await ask('  Station ID: ');
        const watchDir = await ask('  Music folder to watch (or leave blank): ');
        
        let watchDirs = [];
        if (watchDir) watchDirs = [watchDir];
        
        const config = { apiKey, apiUrl, stationId, watchDirs };
        fs.writeFileSync(configPath, JSON.stringify(config, null, 2));
        
        console.log('');
        console.log('  ✅ Configuration saved!');
        console.log('  🔄 Restarting connector...');
        console.log('');
        rl.close();
        
        // Now start normally
        require('../index.js');
    })();
    return;
}

// Normal start
require('../index.js');