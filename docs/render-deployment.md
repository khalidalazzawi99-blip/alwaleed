# Render deployment with persistent SQLite

Configure a Render Persistent Disk with this mount path:

```text
/app/database
```

Configure these Render environment variables:

```text
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite
```

The container creates the SQLite file only when it does not already exist, then runs `php artisan migrate --force`. Normal deployments do not run database seeders and do not replace existing users or passwords.

## First installation only

On a genuinely empty database, migrations create the schema but no user. To create the initial super admin:

1. Add temporary secret environment variables `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_NAME`, and `INITIAL_ADMIN_PASSWORD` in Render.
2. From Render Shell, run:

   ```text
   php artisan db:seed --class=AdminUserSeeder --force
   ```

3. Remove `INITIAL_ADMIN_PASSWORD` from the Render environment after the command succeeds.

The seeder exits without changing anything when that email already exists. It never resets an existing password.
