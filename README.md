# whyme-api

WHY ME backend: REST API for the website and mobile apps, plus the Filament staff
console. Laravel modular monolith on MySQL. Architecture: `../docs/01-architecture.md`.

## Layout

| Path | What |
|---|---|
| `app/Modules/Platform` | Error format, idempotency, request IDs, locale, app-version gate, settings, feature flags, languages, audit log |
| `app/Modules/Identity` | Phone + OTP signup, login (new devices need a code), recovery, tokens per device, capabilities, staff accounts |
| `app/Filament` | Staff console (guard `admin`, TOTP MFA, roles and permissions) |
| `lang/en`, `lang/ig` | Server messages; Igbo falls back to English until reviewed |
| `api.json` | OpenAPI spec consumed by `whyme-apps` (`pnpm api:generate`) |

## First run (Laragon)

```bash
composer install
cp .env.example .env && php artisan key:generate
# create MySQL databases `whyme` and `whyme_testing` (utf8mb4_0900_ai_ci)
# set ADMIN_EMAIL / ADMIN_PASSWORD in .env for the first super-admin
php artisan migrate --seed
php artisan serve            # API at http://localhost:8000/api/v1, staff console at /admin
```

With `SMS_DRIVER=log`, OTP codes are written to `storage/logs/laravel.log`.

## Everyday commands

```bash
php artisan test                         # feature tests (MySQL whyme_testing)
php artisan scramble:export --path=api.json   # after API changes, then `pnpm api:generate` in whyme-apps
php artisan model:prune                  # schedule daily: old idempotency keys and OTPs
```

## Rules

- A module writes only its own tables; other modules call its Actions.
- Every API error is `{ code, message, fields, retryable }` (throw `ApiException`).
- Writes that offline clients retry must be safe with `Idempotency-Key`.
- Editable records carry `version`; stale edits return 409 with the current record.
- Staff actions that change data are recorded with `Audit::record()`.
