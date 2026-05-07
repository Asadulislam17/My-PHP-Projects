# SpeedEx Courier Service — PHP/MySQL

Pixel-perfect courier delivery system for Bangladesh.

## Setup

1. Create the database:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```

2. Update DB credentials in `config/db.php` if needed (default: `root`/no password, db `speedex_courier`).

3. Serve from a PHP-capable server (Apache/Nginx + PHP 8.1+) with this folder as the document root.
   For quick testing:
   ```bash
   php -S localhost:8000
   ```
   Then open http://localhost:8000

## Default Admin Secret Code
`SPEEDEX-ADMIN-2025` (change in `config/db.php`)

## Roles
- **Admin** — full system control, manage parcels/hubs/users.
- **Hub Manager** — manage incoming/outgoing parcels for their assigned hub.
- **No customer accounts** — anyone can send/track parcels by tracking ID.

## Project Structure
```
/project
 ├── config/db.php          # Database connection + helpers
 ├── auth/                  # login, register, logout
 ├── admin/                 # admin dashboard + management pages
 ├── hub/                   # hub manager dashboard + incoming/outgoing
 ├── api/                   # REST endpoints (create_parcel, track_parcel, update_status)
 ├── includes/              # shared header/footer
 ├── assets/style.css       # design system (dark + light)
 ├── sql/schema.sql         # MySQL schema + seed hubs
 ├── index.php              # landing page + send parcel form
 └── track.php              # track parcel page
```

## REST API
- `POST /api/create_parcel.php` — create new parcel, returns `{tracking_id}`
- `GET  /api/track_parcel.php?tracking_id=SPX...` — get parcel + logs
- `POST /api/update_status.php` — update status (auth required), returns Bangla SMS text

## SMS (Bangla)
Triggered on status update — returned in API response, ready to wire into any SMS gateway:
- রওনা হয়েছে (transit)
- পৌঁছেছে (arrived)
- প্রস্তুত (ready)
- ডেলিভার হয়েছে (delivered)
