const { spawn } = require('child_process');
const path = require('path');
const { auditLog } = require('./audit');
const config = require('./config');

class TerminalSession {
    constructor(ws, user, terminalType) {
        this.ws = ws;
        this.user = user;
        this.terminalType = terminalType;
        this.shell = null;
        this.cwd = this.getStartDirectory();
        this.id = `${user.id}-${Date.now()}-${Math.random().toString(36).substr(2, 6)}`;
        
        this.startShell();
    }

    getStartDirectory() {
        switch (this.terminalType) {
            case 'hosting': return `/home/${this.user.username}/`;
            case 'radio': return `/home/${this.user.username}/radio/`;
            default: return `/home/${this.user.username}/`;
        }
    }

    startShell() {
        const shellOpts = ['/bin/bash', '--norc', '--noprofile'];
        const env = {
            TERM: 'xterm-256color',
            HOME: `/home/${this.user.username}`,
            USER: this.user.username,
            SHELL: '/bin/bash',
            PATH: '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin'
        };

        // For admin/staff, start as root
        if (this.terminalType === 'admin') {
            shellOpts.unshift('-u', 'root', 'bash');
            this.shell = spawn('sudo', shellOpts, { 
                cwd: '/root',
                env: { ...env, HOME: '/root', USER: 'root' }
            });
        } else {
            // Start as the user
            this.shell = spawn('sudo', ['-u', this.user.username, '/bin/bash', '--norc', '--noprofile'], {
                cwd: this.cwd,
                env
            });
        }

        // Set up restricted shell for non-admin
        if (this.terminalType !== 'admin' && this.terminalType !== 'vps') {
            this.setupRestrictions();
        }

        this.shell.stdout.on('data', (data) => {
            if (this.ws.readyState === 1) {
                this.ws.send(JSON.stringify({ type: 'output', data: data.toString() }));
            }
        });

        this.shell.stderr.on('data', (data) => {
            if (this.ws.readyState === 1) {
                this.ws.send(JSON.stringify({ type: 'output', data: data.toString() }));
            }
        });

        this.shell.on('close', (code) => {
            console.log(`[${this.id}] Shell exited with code ${code}`);
            if (this.ws.readyState === 1) {
                this.ws.send(JSON.stringify({ type: 'exit', code }));
                this.ws.close();
            }
        });

        this.shell.on('error', (err) => {
            console.error(`[${this.id}] Shell error:`, err.message);
            if (this.ws.readyState === 1) {
                this.ws.send(JSON.stringify({ type: 'error', message: `Shell error: ${err.message}` }));
            }
        });
    }

    setupRestrictions() {
        // Override cd command to restrict paths
        const username = this.user.username;
        const allowedPrefix = this.terminalType === 'radio' 
            ? `/home/${username}/radio` 
            : `/home/${username}`;

        // Set up a restricted prompt that shows path
        const ps1 = `\\[\\e[32m\\]${this.user.username}@${config.hostname}\\[\\e[0m\\]:\\[\\e[34m\\]\\w\\[\\e[0m\\]\\$ `;
        this.write(`export PS1='${ps1}'\n`);
        this.write(`export TERM=xterm-256color\n`);
        
        // Custom cd that checks paths
        this.write(`cd() { builtin cd "$@" && pwd | grep -q "^${allowedPrefix}" || { builtin cd "${allowedPrefix}"; echo "Access denied: outside allowed directory"; }; }\n`);
    }

    write(data) {
        if (this.shell && this.shell.stdin.writable) {
            this.shell.stdin.write(data);
        }
    }

    resize(cols, rows) {
        if (this.shell && this.shell.stdout) {
            try {
                // Resize PTY if available
                process.stdout.rows = rows || process.stdout.rows;
                process.stdout.columns = cols || process.stdout.columns;
            } catch (e) {}
        }
    }

    execCommand(command, user) {
        // For restricted terminals, execute single command and log it
        if (this.terminalType !== 'admin') {
            // Check for restricted commands
            const firstWord = command.split(' ')[0];
            if (['sudo', 'su', 'passwd', 'adduser', 'useradd', 'groupadd'].includes(firstWord)) {
                this.ws.send(JSON.stringify({ type: 'error', message: 'Command not allowed' }));
                return;
            }
            
            // Check for package managers
            if (['apt', 'apt-get', 'dpkg', 'yum', 'dnf', 'pacman'].includes(firstWord)) {
                this.ws.send(JSON.stringify({ type: 'error', message: 'Package management not allowed' }));
                return;
            }
        }

        // Audit command for staff
        if (isStaffCommand(user, command)) {
            auditLog(user.id, null, command, user.username, null);
        }

        this.write(command + '\n');
    }

    close() {
        if (this.shell) {
            this.shell.kill('SIGTERM');
            setTimeout(() => {
                if (this.shell) this.shell.kill('SIGKILL');
            }, 3000);
        }
    }
}

function isStaffCommand(user, command) {
    const staffRoles = ['owner', 'administrator', 'server_technician'];
    if (!staffRoles.includes(user.role)) return false;
    const adminCmds = ['sudo', 'systemctl', 'journalctl', 'docker', 'podman', 'apt', 'apt-get', 'dpkg'];
    const firstWord = command.trim().split(' ')[0];
    return adminCmds.includes(firstWord) || command.includes('sudo');
}

module.exports = { createSession: (ws, user, type) => new TerminalSession(ws, user, type), TerminalSession };
