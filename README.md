# Planet Hosts WHM

Web Hosting Manager with streaming engine management, billing, and modular widget-based dashboard.

## Quick Start
- **Server**: `ssh root@45.61.59.55`
- **Web root**: `/var/www/radiohosting/`
- **Local**: `D:\New_WOw\whm\` (primary), `K:\site_del\Masterinstall\` (deployment mirror)

## Architecture
```
core/              — Framework (Router, Database, Auth, View, Widget, WidgetManager)
admin/             — Admin panel controllers & views
user/              — User panel controllers & views
plugins/           — Plugin modules (Radio, etc.)
widgets/           — Dashboard widget files (auto-discovered)
theme/             — Layout templates
routes/            — Route definitions
public/            — Entry point (index.php)
```

## Dashboard Widget System
The dashboard is fully widget-based. Each widget is a file in `widgets/` returning a config array. Widgets can be added, removed, reordered, and placed in zones via drag-and-drop.

See `Project.md` for full widget inventory and development rules.

## Deploy
```bash
# Individual file
scp path/to/file.php root@45.61.59.55:/var/www/radiohosting/path/to/file.php

# Verify syntax
ssh root@45.61.59.55 "php -l /var/www/radiohosting/path/to/file.php"
```

## Rules
1. **No duplication** — search existing code before creating anything new
2. **Update docs** — update Project.md and README.md before each session ends
3. **Preserve widgets** — extend existing, never recreate
4. **Backup first** — backup files to `backups/` before making changes
