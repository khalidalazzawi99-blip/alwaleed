# Render deployment with PostgreSQL

Create a Render PostgreSQL database, then configure these environment variables on the web service:

```text
DB_CONNECTION=pgsql
DB_HOST=<Render internal PostgreSQL hostname>
DB_PORT=5432
DB_DATABASE=<Render PostgreSQL database name>
DB_USERNAME=<Render PostgreSQL username>
DB_PASSWORD=<Render PostgreSQL password>
DB_SSLMODE=require
CACHE_STORE=file
SESSION_DRIVER=file
APP_ENV=production
APP_DEBUG=false
INITIAL_ADMIN_NAME=خالد العزاوي
INITIAL_ADMIN_EMAIL=<set in Render>
INITIAL_ADMIN_PASSWORD=<temporary; remove after first setup>
```

Copy `DB_HOST` from the database's current **Internal Database URL** in
Render. Do not reuse a hostname from a deleted or replaced database. The short
`dpg-...-a` hostname resolves only when the web service and database are in the
same Render region/account. If the application is hosted elsewhere, use the
host from **External Database URL** instead.

After changing any database variable, redeploy the web service so Laravel's
cached configuration is rebuilt. Keep `APP_DEBUG=false` in production; database
connection errors otherwise expose internal hostnames and application paths.

The container runs forward migrations with `php artisan migrate --force`, then invokes `AdminUserSeeder` before starting the web server. The seeder creates an admin only while all three `INITIAL_ADMIN_*` variables are present.

## First installation only

On a genuinely empty database, migrations create the schema. To create the initial super admin without Render Shell:

1. Add `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_NAME`, and temporary `INITIAL_ADMIN_PASSWORD` in Render.
2. Deploy once. Startup creates the admin only when the email does not already exist.
3. Remove `INITIAL_ADMIN_PASSWORD` after login succeeds and deploy again.

The seeder exits without changing anything when that email already exists. It never resets an existing password.
