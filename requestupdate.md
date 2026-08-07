# Request System — How It Works (Desktop App Update Guide)

This describes how the **song request** flow works end-to-end between the web panel, the connector API, and **Planet Hosts Studio** (desktop app), including **per-station branding** on requests.

---

## 1. What is a "request"?

A **song request** is a listener asking a station to play a specific song. Each request belongs to **one station** (`stream_id`). Requests are stored in the `radio_requests` table and are shown in:
- The web radio panel (`/user/radio?tab=requests`)
- The **DJ panel** (`/dj_panel.php` → Requests tab)
- **Planet Hosts Studio** (desktop) via the connector API

---

## 2. API endpoints (the desktop app uses these)

Base: `https://planet-hosts.com`

### Submit a request (public — no API key)
```
POST /connector/station/{station}/requests
Content-Type: application/json

{ "artist": "Some Artist", "title": "Some Song", "guest_name": "Listener", "message": "optional" }
```
`{station}` may be a **station id** (`12`) OR a **station name slug** (`test-plans-uce`).

Response: `{ "success": true, "message": "Request submitted" }`

### List pending requests (with branding) — for a specific station
```
GET /connector/station/{station}/requests
```
Response includes the station's **branding** so the desktop app can render each station's requests under the correct logo:

```json
{
  "success": true,
  "data": [
    {
      "id": 6,
      "guest_name": "Listener",
      "artist": "Artist",
      "title": "Song",
      "message": "",
      "status": "pending",
      "created_at": "2026-08-07 10:00:00",
      "station_name": "Test Plans uce",
      "station_engine": "icecast"
    }
  ],
  "station": {
    "id": 14,
    "name": "Test Plans uce",
    "slug": "test-plans-uce",
    "logo": "https://planet-hosts.com/radio/branding/14/brand_logo.png",
    "banner": "https://planet-hosts.com/radio/branding/14/brand_banner.png",
    "default_art": "",
    "primary_color": "#0A84FF",
    "accent_color": "#00C853",
    "slogan": "Your station slogan"
  }
}
```

### DJ actions (approve / deny) — API key required
```
POST /connector/station/{station}/requests
X-API-Key: <dj_api_config.api_key>
Content-Type: application/json

{ "request_id": 6, "action": "approve" }   // or "deny"
```

---

## 3. Per-station branding (the important part)

Each station has its own **branding record** in the `radio_branding` table, keyed by the **real station id** (`streaming_stations.id`, NOT the composite `10000+id`):

| Field | Meaning |
|---|---|
| `brand_logo` | Station logo URL (path, prepend `https://planet-hosts.com`) |
| `brand_banner` | Station banner image URL |
| `brand_default_art` | Default album art |
| `brand_primary_color` | Primary brand color (hex) |
| `brand_accent_color` | Accent color (hex) |
| `brand_slogan` | Station slogan |

**Where it's configured:** `/user/radio?tab=branding` per station (the account owner uploads the logo, colors, slogan for each station).

**How the desktop app should use it:**
- When fetching requests for a station, the `station` object in the response carries that station's logo/colors/slogan.
- Show request lists **per station**, using the matching `station.logo` + `primary_color` so requests from "Test Plans uce" appear under the Icecast station's branding, and requests from "Test Plans v2" appear under that station's branding, etc.
- If a station has no custom branding, `logo` falls back to the station's `logo_url` (or empty). Use `primary_color` default `#0A84FF`.

---

## 4. Station identity in URLs

The connector accepts **both** a numeric station id and a readable slug:
- `https://planet-hosts.com/connector/station/14/requests` (id)
- `https://planet-hosts.com/connector/station/test-plans-uce/requests` (slug)

Slug = station name lowercased, spaces/dashes → single `-`. Example:
- `Test Plans uce` → `test-plans-uce`
- `Test Plans v2` → `test-plans-v2`
- `Test Plans` → `test-plans`

The desktop app can use the `station.slug` returned by the API, or the numeric `station.id`.

---

## 5. Data model

```
radio_requests: id, stream_id (real station id), guest_name, artist, title, message, status (pending/played/removed), created_at
radio_branding: id, station_id (real station id, UNIQUE), brand_logo, brand_banner, brand_default_art, brand_primary_color, brand_accent_color, brand_slogan, ...
streaming_stations: id, name, engine, port, mount_point, ...
```

**Important:** `radio_requests.stream_id` and `radio_branding.station_id` are the **real** `streaming_stations.id` (12/13/14). Composite ids (10000+) are only used in the web UI URLs for convenience.

---

## 6. How the desktop app should integrate

1. **For each station** the user has, call `GET /connector/station/{id-or-slug}/requests`.
2. Read the `station` object → logo, colors, slogan → render that station's request section with its branding.
3. Poll every `poll_interval_seconds` (default 15s, configurable in `dj_api_config`).
4. To approve/deny, call `POST .../requests` with `{request_id, action}` and the station's `api_key` header.
5. Use the per-station `api_key` from `dj_api_config` — **each station has its own key**, so requests stay scoped to the right station/branding.

---

*Last updated: 2026-08-07*
