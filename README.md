# Betterbloq

Betterbloq is a Laravel application (Laravel 12 / PHP 8.2+) with a modern React/Inertia front-end. The project includes support for Octane / FrankenPHP (production Dockerfile), Vite-based front-end tooling, Filament admin, Stripe integration and several other common Laravel packages.

## Quick overview

- Framework: Laravel (see `composer.json`, Laravel 12)
- PHP: ^8.2
- Frontend: React + Inertia + Vite
- Dev tooling: Vite, Prettier, ESLint, Pest (testing)
- Optional production runtime: FrankenPHP (see `Dockerfile.frankenphp`)

## What this project does

Betterbloq makes construction materials more affordable by combining many smaller orders into a single purchasing pool. Key user flows and benefits highlighted on the landing page:

- The problem: materials are a large portion of project budgets and small buyers pay retail margins significantly above wholesale.
- The solution: users specify what they need, Betterbloq pools those requests with other orders, negotiates improved pricing, and coordinates just-in-time deliveries to match project schedules.
- Steps (simplified): tell us what you need → we pool orders → you get better pricing → materials arrive when needed.
- Why it matters: lower material costs make more projects viable, improve neighborhoods through more affordable renovations and development, and create fairer competition for smaller builders.

The product targets contractors, small developers, flippers, and homeowners who benefit from collective buying power.

## Repo layout (important locations)

- `app/` — main application code (Controllers, Models, Managers, Mail, etc.)
- `config/` — configuration files
- `routes/` — web, api and auxiliary routes
- `resources/` — frontend assets, views
- `public/` — public webroot and built assets
- `database/` — migrations, factories and seeders
- `deployment/` — Docker deploy helpers, supervisord configs, healthchecks
- `tests/` — Pest / PHPUnit tests

## Requirements

- Docker & docker-compose (recommended for local development)
- PHP 8.2+ (if running locally without Docker)
- Composer
- Node.js / npm or Bun for frontend builds (project supports Bun in Docker builds)

## Environment

Copy the example environment and update values:

```fish
cp .env.example .env
# then edit .env
```

By default, the project expects environment variables for DB, mail, storage providers and Stripe. See `config/` and `.env.example` for keys.

## Quick start — Docker (recommended)

The repo includes `Dockerfile` (alternative) and `Dockerfile.frankenphp` (production-ready FrankenPHP/Octane image). A compose file is provided as `compose.yml` and a production variant in `compose.production.yml`.

To start the development stack:

```fish
# start docker services in background
docker compose up -d
# install PHP deps (on host or inside container)
composer install
# build frontend assets (dev watch)
npm run dev
```

Notes:
- The `Makefile` contains helper targets such as `make develop` and `make kamal-deploy` for Kamal deployments. The `Makefile` references `compose.prod.yaml` in some targets; this repository contains `compose.production.yml`. If you use the `Makefile`, update the file name or call docker compose directly: `docker compose -f compose.production.yml up --build -d`.
- The project includes a `dev` composer script that runs several processes concurrently (server, queue, pail, vite). See the `composer.json` `scripts` section for details, and `composer run dev` to start them from the host.

## Local development (without Docker)

1. Ensure PHP 8.2+, Composer and Node are installed.
2. Copy `.env.example` to `.env` and set values.
3. Install PHP dependencies: `composer install`.
4. Install JS deps: `npm ci` (or use Bun if preferred).
5. Build assets: `npm run dev` (for watch) or `npm run build` (production).
6. Run migrations (if using a database): `php artisan migrate`.

## Frontend build notes

- The repo uses Vite (see `package.json`). Docker production image optionally uses Bun to build assets inside the build stage (`Dockerfile.frankenphp`). Locally, `npm`/`pnpm`/`yarn` is supported by normal Node flows.

## Testing

The project uses Pest/PHPUnit. To run the test suite:

```fish
# run all tests (Pest)
./vendor/bin/pest
# or using phpunit directly
./vendor/bin/phpunit
```

The `phpunit.xml` sets up an in-memory sqlite DB and other testing-friendly environment variables.

## Useful commands

```fish
# start services (docker)
docker compose up -d
# install composer dependencies
composer install
# run the app alongside watchers (uses composer script)
composer run dev
# run tests
./vendor/bin/pest
# stripe local webhook tunnel (if configured in Makefile)
make stripe-listen
```

## Docker / Production notes

- `Dockerfile.frankenphp` is a multi-stage production-ready image that builds FrankenPHP, includes Bun-based frontend build stages and prepares a slim runtime image. Use it for Octane / FrankenPHP deployments.
- `Dockerfile` is an alternative image using `serversideup/php` base.
- `compose.production.yml` contains production service wiring; ensure the `Makefile` references align with the file names in this repo.

## Deployment

This repo uses Kamal for remote deployments (see `Makefile` targets `kamal-setup`, `kamal-deploy`, `kamal-app`). Example:

```fish
# deploy to staging (reads from .env.staging)
make kamal-deploy DEST=staging
```

If you rely on the Makefile, ensure the environment files `.env.staging` / `.env.production` exist and contain the expected variables.

## Contributing

- Follow coding standards in the repo (Pint, ESLint, Prettier where appropriate).
- The project uses `Pest` for tests. Please add tests for new features and critical bug fixes.

## Contact / maintainers

See repository owners for maintainers and contributor guidelines. For deployment or runtime-specific questions, consult the `deployment/` directory and `Makefile` targets.

## Testing Discipline

| What           | AI CAN Do               | AI MUST NOT Do           |
| -------------- | ----------------------- | ------------------------ |
| Implementation | Generate business logic | Touch test files         |
| Test Planning  | Suggest test scenarios  | Write test code          |
| Debugging      | Analyze test failures   | Modify test expectations |

If an AI tool touches a test file, the PR gets rejected. No exceptions.
