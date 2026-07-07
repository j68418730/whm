# Planet Hosts Desktop Connector

Local companion application for Planet Hosts Studio. Watches folders, uploads music, streams audio, and keeps your station library in sync.

## Installation

### Prerequisites
- Node.js 18+
- npm
- FFmpeg (for microphone streaming and transcoding)

### Setup
```bash
cd desktop-connector
npm install
npm start
```

### First-Time Configuration
```bash
npm start configure
```

### Service Installation (Auto-Start)
```bash
npm run install-service
```

## Features

| Feature | Status | Description |
|---------|--------|-------------|
| Folder Watching | Done | Watches directories for new music files |
| Metadata Reading | Done | Reads ID3 tags and audio properties |
| Music Upload | Done | Uploads files and metadata to panel |
| Album Art | Done | Extracts and sends album art |
| Tag Editor | API Ready | Panel-side metadata editing |
| Audio Engine | Done | FFmpeg-based playback engine |
| Microphone | Done | Live microphone streaming |
| Encoder | Done | Multi-format audio encoding |
| WebSocket | Done | Real-time panel communication |
| Auto-Updates | Done | Self-updating mechanism |

## API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | /connector/auth | Authenticate with API key |
| GET | /connector/station/{id}/library | Fetch music library |
| GET | /connector/station/{id}/queue | Fetch current queue |
| GET | /connector/station/{id}/status | Station status and health |
| GET | /connector/station/{id}/history | Song history |
| POST | /connector/station/{id}/upload | Upload music file |
| GET | /connector/devices | Active connector sessions |

## Commands

```bash
npm start              # Run connector
npm start configure    # Interactive setup
npm run install-service  # Install as system service
npm run update         # Check and apply updates
npm run watch          # File watching only mode
```

## Architecture

```
desktop-connector/
├── index.js              # Main entry, CLI
├── package.json          # Dependencies and scripts
├── bin/
│   ├── ph-connector.js   # Binary entry
│   └── install-service.js # Service installer (Win/Linux/macOS)
├── lib/
│   ├── config.js         # Configuration manager
│   ├── logger.js         # Logging
│   ├── watcher.js        # Directory watcher (chokidar)
│   ├── metadata.js       # Audio metadata reader
│   ├── uploader.js       # File uploader (REST API)
│   ├── websocket.js      # WebSocket client
│   ├── encoder.js        # FFmpeg encoder/streamer
│   └── updater.js        # Auto-updater
└── README.md
```

## Platform Support

- Windows 10/11 (x64)
- Linux (x64, ARM)
- macOS 12+ (x64, ARM)