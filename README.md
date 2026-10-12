# FolioLab

A Laravel portfolio generator with three responsive templates, public portfolio previews, and a private management workspace.

## Requirements

- PHP 8.4.1 or newer with the PDO driver for your database
- Composer
- Node.js and npm

## Run locally

```sh
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. The local configuration uses SQLite and the public storage disk.

## Deploy with PostgreSQL

The schema uses Laravel migrations and JSON columns supported by PostgreSQL, MySQL, and SQLite. On Railway, Supabase, or another Laravel host, provision PostgreSQL and set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_CONNECTION=pgsql
DATABASE_URL=postgresql://user:password@host:5432/database?sslmode=require
SESSION_DRIVER=database
PORTFOLIO_FILESYSTEM_DISK=s3
```

Run `php artisan migrate --force` during deployment. For profile pictures on ephemeral hosts, configure Laravel's `s3` disk with the provider's S3-compatible storage credentials (Supabase Storage or another object store). Use a persistent volume if you choose local storage instead. Keep `APP_KEY` stable and set it as a secret in the hosting dashboard. Never commit `.env` or production credentials.

## Portfolio management

Portfolios have unguessable UUID preview URLs. Edit and delete actions are restricted to the browser session that created them; use database-backed sessions in production so that ownership survives app restarts. A public preview link can be shared independently of the management session.