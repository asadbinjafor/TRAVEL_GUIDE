# Travel Guide

Travel Guide is a PHP MVC application where scouts submit destination posts, verified administrators moderate users and content, and verified general users browse, comment, estimate trip costs, and maintain wishlists.

## Detected application structure

- Entry point/homepage: `/index.php`
- Login: `/index.php?route=%2Flogin`
- Registration: `/index.php?route=%2Fregister`
- Admin dashboard: `/index.php?route=%2Fadmin`
- Roles: `admin`, `scout`, `user`
- Runtime: PHP 8.3, Apache, PDO PostgreSQL
- Database: PostgreSQL/Supabase (`database.sql`)
- Persistent uploads: Supabase Storage in production; Docker volume locally

There are no committed default passwords or database credentials. Public registration always creates an unverified `user`; verified administrators create scout/admin accounts. The first administrator is created explicitly with `scripts/seed_admin.php`.

## Quick local start (PowerShell)

```powershell
Copy-Item .env.example .env
# Edit .env and replace DB_PASSWORD, ADMIN_EMAIL, and ADMIN_PASSWORD.
docker compose up --build -d
docker compose exec app php scripts/seed_admin.php
Start-Process http://localhost:8080/index.php
```

Health check: `http://localhost:8080/health.php`

For the complete Supabase, Render, Vercel, local setup, security notes, and troubleshooting instructions, see [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md).
