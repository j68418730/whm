# Planet Hosts Panel

Web hosting control panel with streaming and game server management.

## Access

### Admin Panel
Navigate to your server's admin port (e.g. `https://your-server:2087`) and log in with your admin credentials.

### User Panel
Users access their control panel at `https://your-server:2083` using their hosting account credentials.

## Getting Started

### First Login
1. Log into the admin panel using the admin account created during installation
2. Navigate to **Accounts** to create hosting accounts
3. Navigate to **Packages** to define hosting packages with resource limits
4. Set up nameservers in **DNS** settings

### Creating a Hosting Account
1. Go to **Accounts → Create Account**
2. Fill in the username, domain, password, and select a package
3. The account is provisioned with the selected package's resources

### Managing Streaming
1. Go to **Streams** in the admin sidebar
2. Use the **Create Stream** wizard to set up a new radio station
3. Select engine type (Icecast/SHOUTcast), configure ports and bitrates
4. Start/stop/restart streams from the streams index

### Managing Game Servers
1. Navigate to **Game Servers** in the admin sidebar
2. Define game types and slot pricing under configuration
3. Users can deploy game servers from their control panel

### Billing
- **Products**: Define billable products with pricing and billing cycles
- **Orders**: View and manage customer orders
- **Invoices**: Create and manage invoices
- **Payments**: Record manual payments
- **Coupons**: Create discount codes
- **Taxes**: Configure tax rates

## Sections

| Section | Description |
|---------|-------------|
| Dashboard | Server stats, widgets, quick actions |
| Accounts | Hosting account management |
| Packages | Resource package definitions |
| DNS | DNS zone management |
| Email | Mail accounts, forwarders, autoresponders |
| FTP | FTP account management |
| MySQL | Database management with phpMyAdmin |
| SSL | Certificate management and AutoSSL |
| Streams | Radio streaming management |
| Game Servers | Game server administration |
| Billing | Full billing suite |
| Backups | Backup profiles and archives |
| Migration | Import from other hosting panels |
| Server | Server overview and configuration |
| Security | Firewall, IP blocking, ModSecurity |
| Terminal | Browser-based SSH terminal |

## Backup & Restore

Create full or per-user backups from the **Backups** section. Save backup profiles for recurring backups. Use restore preview to inspect archive contents before restoring.

## Migration

The **Migration Wizard** supports importing accounts from:
- cPanel (WHM API)
- Plesk
- DirectAdmin
- SonicPanel
- Centova Cast

## Maintenance

- Use the terminal for server-level commands
- Restart services from their respective pages
- Monitor server health from the dashboard
- Run AutoSSL for automatic Let's Encrypt certificates
