# Provisioning and builds

## Administrator accounts

Role seeding defines roles only; it does not grant access based on an email domain. Running it repeatedly is safe:

```sh
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

Create the intended user through registration, verify their email, then explicitly select that account on a trusted server console:

```sh
php artisan app:set-user-role you@example.com
```

The command fails without an exact verified account and records the target account ID in the application log. Existing roles are not removed automatically. Review accounts granted access by earlier domain-based provisioning, including any seeded `admin@betterbloq.com` account: the old factory supplied the known password `password`. This change prevents future implicit grants; it does not repair existing credentials.

The default database seeder creates demonstration data and is now restricted to `local` and `testing`. It cannot be forced to run in production or staging. Demo seeding suppresses model events so fixtures do not send welcome mail or create Stripe products.

The legacy `deployment/start-container` script no longer seeds on startup. Its migration step is opt-in through `RUNNING_MIGRATIONS=true` and does not fall back to an unlocked migration. On an empty installation with database-backed cache, bootstrap the schema in a single controlled release command first: the cache lock tables do not exist yet. The current Dockerfile uses the server image's own entrypoint; apply schema changes through a controlled release step appropriate to that deployment rather than running demo seeders.

## Image and dependency inputs

Frontend dependencies come from the tracked `bun.lock`, using Bun 1.3.14 and `--frozen-lockfile`. Docker and CI use the same installer. The production PHP install omits development packages. Frontend builds resolve lazy chunks and preloaded CSS relative to the bundle itself, so the same build can run on a local hostname or the deployed origin without baking a preview URL into imports.

`.dockerignore` excludes all environment variants, deployment secrets, cloud credentials, private keys, runtime storage, and local SQLite databases. Git ignoring a file alone does not prevent Docker from copying it into an image.

CI installs Composer dependencies before building frontend assets because Ziggy is resolved from `vendor/`. The tests run against both SQLite and MariaDB 11.4. Style checks examine changed files without rewriting them. The repository's pre-existing TypeScript diagnostics remain separate work; these checks do not suppress them or claim the whole frontend has a clean type baseline.
