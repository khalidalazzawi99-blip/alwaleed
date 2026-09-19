# Render deployment with PostgreSQL

Create a Render PostgreSQL database, then configure these environment variables on the web service:

```text
DB_CONNECTION=pgsql
DATABASE_URL=<Render Internal Database URL>
DB_SSLMODE=require
CACHE_STORE=file
SESSION_DRIVER=file
APP_ENV=production
APP_DEBUG=false
INITIAL_ADMIN_NAME=خالد العزاوي
INITIAL_ADMIN_EMAIL=<set in Render>
INITIAL_ADMIN_PASSWORD=<temporary; remove after first setup>
```

Copy `DATABASE_URL` from the database's current **Internal Database URL** in
Render. Do not reuse a URL or hostname from a deleted or replaced database. The
short `dpg-...-a` hostname resolves only when the web service and database are
in the same Render region/account. If the application is hosted elsewhere, use
the **External Database URL** instead.

The application gives `DATABASE_URL` precedence and retains `DB_URL` only as a
legacy fallback. Prefer one complete URL instead of manually maintaining
`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. Remove
stale `DATABASE_URL` / `DB_URL` values and stale individual `DB_*` values from
the Render web service before adding the current URL, so an old host cannot be
selected accidentally.

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
