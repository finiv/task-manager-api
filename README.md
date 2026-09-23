# Task Manager API

A small REST API for managing personal tasks, built with **Symfony 8** and **API Platform**.
Each user registers, logs in, and gets a JWT — tasks are scoped so users can only ever see and edit their own.

Built as a portfolio project to demonstrate a modern Symfony/API Platform stack: entities, Doctrine ORM,
JWT authentication, per-user authorization at the query level, automatic OpenAPI docs, and a functional
test suite that runs in CI.

## Features

- **Auth** — registration (`POST /api/users`) and JWT login (`POST /api/login`) via `lexik/jwt-authentication-bundle`.
- **Tasks CRUD** — title, description, priority (`low` / `medium` / `high`), due date, done flag.
- **Per-user data isolation** — a Doctrine query extension filters every task collection by the authenticated
  user, and item-level operations are guarded by an API Platform security expression, so one account can never
  read or modify another's tasks.
- **Filtering & sorting** — `GET /api/tasks?isDone=true`, `?priority=high`, `?order[dueDate]=asc`, etc.
- **Auto-generated docs** — interactive Swagger UI at `/api/docs`, JSON-LD/Hydra at `/api`.
- **Tests** — functional tests (`WebTestCase`) covering registration, login, and task ownership boundaries.
- **CI** — GitHub Actions runs the test suite, a container/YAML lint, and code-style checks on every push.

## Tech stack

PHP 8.3+, Symfony 8, API Platform 5, Doctrine ORM, SQLite (zero setup), LexikJWTAuthenticationBundle, PHPUnit.

## Getting started

Requires PHP 8.4+ with the `pdo_sqlite` extension, and Composer.

```bash
composer install
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:migrations:migrate --no-interaction
symfony serve -d   # or: php -S localhost:8000 -t public
```

The app uses SQLite by default (`var/data_dev.db`), so there's no database server to set up. To use
PostgreSQL instead, set `DATABASE_URL` in `.env.local` (a `compose.yaml` for a local Postgres container
is already included) and re-run the migration.

Open `http://localhost:8000/api/docs` for the interactive API documentation.

## Trying it out

```bash
# Register
curl -X POST http://localhost:8000/api/users \
  -H 'Content-Type: application/ld+json' \
  -d '{"email":"jane@example.com","plainPassword":"S3curePass!"}'

# Log in and grab a JWT
TOKEN=$(curl -s -X POST http://localhost:8000/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"jane@example.com","password":"S3curePass!"}' | jq -r .token)

# Create a task
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/ld+json' \
  -d '{"title":"Write portfolio README","priority":"high"}'

# List your tasks
curl http://localhost:8000/api/tasks -H "Authorization: Bearer $TOKEN"
```

## Running the tests

```bash
php bin/phpunit
```

Each test class recreates its own SQLite schema, so no fixtures or manual database setup are needed.

## Project layout

```
src/
  Entity/      Task, User (Doctrine entities + API Platform resource config)
  State/       processors that hash passwords and stamp the task owner on write
  Doctrine/    query extension that scopes the task collection to the current user
  Controller/  /api/login (intercepted by the security firewall) and /api/me
config/
  packages/security.yaml   firewalls: JSON login issuing a JWT, stateless JWT-guarded /api
migrations/    versioned schema, checked in CI against a real SQLite database
tests/         functional tests (registration, login, task ownership)
```

## License

MIT — see [LICENSE](LICENSE).
