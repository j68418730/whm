const mysql = require('mysql2/promise');
const crypto = require('crypto');
const config = require('./config');

let pool;

async function getPool() {
    if (!pool) {
        pool = mysql.createPool(config.db);
    }
    return pool;
}

async function authenticate(token) {
    try {
        const db = await getPool();
        // Verify Planet Hosts session token
        const [rows] = await db.execute(
            'SELECT u.id, u.username, u.role, u.two_factor_enabled, s.id as session_id FROM sessions s JOIN users u ON u.id = s.user_id WHERE s.token = ? AND s.expires_at > NOW()',
            [token]
        );
        if (rows.length === 0) return null;
        
        const user = rows[0];
        return {
            id: user.id,
            username: user.username,
            role: user.role,
            sessionId: user.session_id,
            two_factor_enabled: !!user.two_factor_enabled,
            two_factor_verified: false // Set to true after 2FA verification
        };
    } catch (err) {
        console.error('Auth error:', err.message);
        return null;
    }
}

const STAFF_ROLES = ['owner', 'administrator', 'server_technician'];
const CUSTOMER_ROLES = ['hosting', 'radio'];

function authorize(user, terminalType) {
    if (terminalType === 'admin') {
        return STAFF_ROLES.includes(user.role);
    }
    if (terminalType === 'vps') {
        return true; // Any logged-in user with a VPS can access
    }
    if (terminalType === 'radio') {
        return user.role === 'radio' || STAFF_ROLES.includes(user.role);
    }
    // hosting terminal
    return true;
}

function isStaff(user) {
    return STAFF_ROLES.includes(user.role);
}

function verify2FA(user, code) {
    // In production, verify against TOTP or backup codes
    return true; // Placeholder
}

module.exports = { authenticate, authorize, isStaff, verify2FA };
