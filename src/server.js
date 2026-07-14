const WebSocket = require('ws');
const { createServer } = require('http');
const url = require('url');
const { authenticate, authorize, isStaff } = require('./auth');
const { createSession } = require('./session');
const { auditLog } = require('./audit');
const config = require('./config');

const wss = new WebSocket.Server({ 
    host: '127.0.0.1', 
    port: 8085,
    maxPayload: 1024 * 1024 
});

console.log(`[Planet-Terminal] Listening on 127.0.0.1:8085`);

wss.on('connection', (ws, req) => {
    const params = url.parse(req.url, true).query;
    const token = params.token;
    const terminalType = params.type || 'hosting';

    if (!token) {
        ws.close(4001, 'Missing token');
        return;
    }

    // Authenticate via Planet Hosts session
    authenticate(token).then(user => {
        if (!user) {
            ws.close(4001, 'Invalid session');
            return;
        }

        // Check authorization for terminal type
        if (!authorize(user, terminalType)) {
            ws.close(4003, 'Not authorized for this terminal type');
            return;
        }

        // For staff terminals, verify 2FA
        if (isStaff(user) && terminalType === 'admin' && !user.two_factor_verified) {
            ws.close(4004, '2FA required for admin terminal');
            return;
        }

        // Create session
        const session = createSession(ws, user, terminalType);
        
        ws.on('message', (data) => {
            try {
                const msg = JSON.parse(data.toString());
                handleMessage(session, msg, user);
            } catch (e) {
                ws.send(JSON.stringify({ type: 'error', message: 'Invalid message format' }));
            }
        });

        ws.on('close', () => {
            session.close();
            console.log(`[${user.username}] Session closed`);
        });

        ws.on('error', (err) => {
            console.error(`[${user.username}] WS Error:`, err.message);
            session.close();
        });

        // Send ready
        ws.send(JSON.stringify({ 
            type: 'ready', 
            cwd: session.cwd,
            username: user.username,
            hostname: config.hostname,
            terminalType 
        }));

    }).catch(err => {
        console.error('Auth error:', err.message);
        ws.close(4001, 'Authentication failed');
    });
});

function handleMessage(session, msg, user) {
    switch (msg.type) {
        case 'input':
            session.write(msg.data);
            break;
        case 'resize':
            session.resize(msg.cols, msg.rows);
            break;
        case 'command':
            // For restricted terminals, run single command with audit
            session.execCommand(msg.command, user);
            break;
        default:
            session.ws.send(JSON.stringify({ type: 'error', message: 'Unknown message type' }));
    }
}

console.log('[Planet-Terminal] Service started');
