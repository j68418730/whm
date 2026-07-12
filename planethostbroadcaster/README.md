# Planet Hosts Studio

## Golden Rule
If a feature already exists anywhere in Planet Hosts, DO NOT build another one. Find it, understand it, reuse it, extend it, improve it. Never duplicate it.

## Project Rules
See `RULES.md` for all 40 development rules.

## Existing Systems (DO NOT REBUILD)
- Streaming Engine (SHOUTcast/Icecast)
- AutoDJ
- DJ Management
- Upload Manager
- Playlist Manager
- Queue Manager
- Song Requests
- Widgets
- Statistics
- Scheduler
- Authentication
- Permissions
- Licensing
- Plugin System
- Marketplace
- Hosting Panel

## Studio Scope
Planet Hosts Studio is ONLY a new interface — a web-based broadcaster and desktop connector. It uses existing APIs and never replaces backend systems.

### Web Broadcaster
- Browser-based mic capture
- Play files from local computer
- Stream to existing SHOUTcast server via existing APIs

### Desktop Connector
- Scan local music folders
- Upload via existing Upload Manager
- Queue songs via existing Queue Manager
- Control playback via Streaming API
- Authenticate via existing DJ accounts

## Architecture
```
Planet Hosts Studio
       │
       ▼
  Existing Planet Hosts APIs/Services
       │
       ├── RadioController
       ├── RadioAutoDJPlayer
       ├── Upload Manager
       ├── Playlist Manager
       ├── DJ Manager
       └── Statistics
       │
       ▼
  SHOUTcast DNAS
```