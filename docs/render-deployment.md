# Render deployment with persistent SQLite

Configure a Render Persistent Disk with this mount path:

```text
/var/data
```

Configure these Render environment variables:

```text
DB_CONNECTION=sqlite
DB_DATABASE=/var/data/database.sqlite
INITIAL_ADMIN_NAME=خالد العزاوي
INITIAL_ADMIN_EMAIL=<set in Render>
INITIAL_ADMIN_PASSWORD=<temporary; remove after first setup>
```

The container creates the SQLite file only when it does not already exist, then runs `php artisan migrate --force`. Normal deployments do not run database seeders and do not replace existing users or passwords.

## First installation only

On a genuinely empty database, migrations create the schema but no user. To create the initial super admin:

1. Add temporary secret environment variables `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_NAME`, and `INITIAL_ADMIN_PASSWORD` in Render.
2. From Render Shell, run:

   ```text
   [ -f /var/data/database.sqlite ] || touch /var/data/database.sqlite
   php artisan config:clear
   php artisan migrate --force
   php artisan db:seed --class=AdminUserSeeder --force
   ```

3. Remove `INITIAL_ADMIN_PASSWORD` from the Render environment after the command succeeds.

The seeder exits without changing anything when that email already exists. It never resets an existing password.
