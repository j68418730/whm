require('dotenv').config();

module.exports = {
    hostname: require('os').hostname(),
    db: {
        host: process.env.DB_HOST || 'localhost',
        port: parseInt(process.env.DB_PORT || '3306'),
        user: process.env.DB_USER || 'planethosts',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'planethosts'
    },
    allowedPaths: {
        hosting: (username) => [`/home/${username}/`],
        radio: (username) => [`/home/${username}/radio/`],
        vps: () => [], // VPS connected via SSH
        admin: () => [] // Full access
    },
    restrictedDirs: ['/etc', '/root', '/usr', '/var', '/boot', '/dev', '/proc', '/sys'],
    sudoCommands: ['systemctl', 'journalctl', 'docker', 'podman', 'apt', 'apt-get', 'dpkg']
};
