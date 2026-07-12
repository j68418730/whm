const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

function installService() {
    const connectorPath = path.dirname(__dirname);
    const nodePath = process.execPath;
    const scriptPath = path.join(connectorPath, 'index.js');
    const platform = process.platform;

    console.log(`Installing Planet Hosts Connector as a ${platform} service...`);

    if (platform === 'win32') {
        installWindows(connectorPath, nodePath, scriptPath);
    } else if (platform === 'linux') {
        installLinux(connectorPath, nodePath, scriptPath);
    } else if (platform === 'darwin') {
        installMacOS(connectorPath, nodePath, scriptPath);
    } else {
        console.error(`Unsupported platform: ${platform}`);
        process.exit(1);
    }
}

function installWindows(connectorPath, nodePath, scriptPath) {
    const nssm = 'nssm.exe';
    const serviceName = 'PlanetHostsConnector';

    try {
        execSync(`${nssm} stop ${serviceName} 2>nul || exit /b 0`, { stdio: 'pipe' });
        execSync(`${nssm} remove ${serviceName} confirm 2>nul || exit /b 0`, { stdio: 'pipe' });
        execSync(`${nssm} install ${serviceName} "${nodePath}" "${scriptPath}"`, { stdio: 'inherit' });
        execSync(`${nssm} set ${serviceName} AppDirectory "${connectorPath}"`, { stdio: 'inherit' });
        execSync(`${nssm} set ${serviceName} Start SERVICE_AUTO_START`, { stdio: 'inherit' });
        execSync(`${nssm} set ${serviceName} AppStdout "${connectorPath}\\logs\\stdout.log"`, { stdio: 'inherit' });
        execSync(`${nssm} set ${serviceName} AppStderr "${connectorPath}\\logs\\stderr.log"`, { stdio: 'inherit' });
        execSync(`${nssm} start ${serviceName}`, { stdio: 'inherit' });
        console.log('Windows service installed and started.');
    } catch (err) {
        console.error('Windows service installation failed. Is nssm.exe in PATH?');
        console.error(err.message);
    }
}

function installLinux(connectorPath, nodePath, scriptPath) {
    const serviceContent = `[Unit]
Description=Planet Hosts Desktop Connector
After=network.target

[Service]
Type=simple
User=%i
ExecStart=${nodePath} ${scriptPath}
WorkingDirectory=${connectorPath}
Restart=on-failure
RestartSec=10
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target
`;

    const servicePath = '/etc/systemd/system/planet-hosts-connector.service';
    try {
        fs.writeFileSync('/tmp/ph-connector.service', serviceContent);
        execSync('sudo mv /tmp/ph-connector.service ' + servicePath, { stdio: 'inherit' });
        execSync('sudo systemctl daemon-reload', { stdio: 'inherit' });
        execSync('sudo systemctl enable planet-hosts-connector', { stdio: 'inherit' });
        execSync('sudo systemctl start planet-hosts-connector', { stdio: 'inherit' });
        console.log('Linux systemd service installed and started.');
    } catch (err) {
        console.error('Linux service installation failed:', err.message);
    }
}

function installMacOS(connectorPath, nodePath, scriptPath) {
    const plistContent = `<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>com.planet-hosts.connector</string>
    <key>ProgramArguments</key>
    <array>
        <string>${nodePath}</string>
        <string>${scriptPath}</string>
    </array>
    <key>WorkingDirectory</key>
    <string>${connectorPath}</string>
    <key>RunAtLoad</key>
    <true/>
    <key>KeepAlive</key>
    <true/>
    <key>StandardOutPath</key>
    <string>${connectorPath}/logs/stdout.log</string>
    <key>StandardErrorPath</key>
    <string>${connectorPath}/logs/stderr.log</string>
</dict>
</plist>`;

    const plistPath = path.join(process.env.HOME, 'Library/LaunchAgents/com.planet-hosts.connector.plist');
    try {
        const logsDir = path.join(connectorPath, 'logs');
        if (!fs.existsSync(logsDir)) fs.mkdirSync(logsDir, { recursive: true });

        fs.writeFileSync(plistPath, plistContent);
        execSync(`launchctl unload ${plistPath} 2>/dev/null || true`, { stdio: 'pipe' });
        execSync(`launchctl load ${plistPath}`, { stdio: 'inherit' });
        console.log('macOS launchd service installed and started.');
    } catch (err) {
        console.error('macOS service installation failed:', err.message);
    }
}

if (require.main === module) {
    installService();
}

module.exports = installService;