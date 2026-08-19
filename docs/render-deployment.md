# Render deployment with PostgreSQL

Create a Render PostgreSQL database, then configure these environment variables on the web service:

```text
DB_CONNECTION=pgsql
DB_HOST=<Render internal PostgreSQL hostname>
DB_PORT=5432
DB_DATABASE=<Render PostgreSQL database name>
DB_USERNAME=<Render PostgreSQL username>
DB_PASSWORD=<Render PostgreSQL password>
INITIAL_ADMIN_NAME=خالد العزاوي
INITIAL_ADMIN_EMAIL=<set in Render>
INITIAL_ADMIN_PASSWORD=<temporary; remove after first setup>
```

The container runs only forward migrations with `php artisan migrate --force`. It runs `AdminUserSeeder` only while all three `INITIAL_ADMIN_*` variables are present.

## First installation only

On a genuinely empty database, migrations create the schema. To create the initial super admin without Render Shell:

1. Add `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_NAME`, and temporary `INITIAL_ADMIN_PASSWORD` in Render.
2. Deploy once. Startup creates the admin only when the email does not already exist.
3. Remove `INITIAL_ADMIN_PASSWORD` after login succeeds and deploy again.

The seeder exits without changing anything when that email already exists. It never resets an existing password.
