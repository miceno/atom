# AGENTS.md

## Project overview
- AtoM (Access to Memory) archival app: Symfony 1.x/Propel-style PHP codebase.
- Main app: `apps/qubit/`. Core code: `lib/`. Features/themes often live in `plugins/` (e.g. `arDominionB5Plugin`, `qtAccessionPlugin`, standards plugins like `sfIsadPlugin`).
- Root `symfony` is the project CLI (`php symfony ...`). `config/` and `apps/qubit/config/` hold runtime/app config (defaults in `config/app.yml`).

## Setup and build
- PHP deps: `composer install` (uses `vendor/composer` per `composer.json` config).
- JS deps: `npm ci` in CI, or `npm install` locally.
- Frontend build: `npm run build` (webpack; writes hashed bundles to `dist/` and regenerates plugin layout/templates from `*_webpack` templates for plugins with `webpack.entry.js` + `templates/_layout_start_webpack.php`; see `webpack.config.js`).
- Watch assets: `npm run watch`.
- Docker dev stack/config lives under `docker/` (`docker/docker-compose.dev.yml`, `docker-compose.test.yml`, `docker/etc/*`, `docker/entrypoint.sh`). Entrypoint modes: `worker` (`php symfony jobs:worker`) and `fpm`.

## Lint / format
- PHP style (CI): `composer php-cs-fixer -- fix --dry-run -v` (config: `.php-cs-fixer.dist.php`).
- PHP style (apply): `composer php-cs-fixer -- fix`.
- JS/CSS/etc. format check (CI): `npm run check-format`; format: `npm run format`.
- Prettier scope is intentionally narrow via `.prettierignore` (mostly ignores repo except `webpack.config.js` and selected plugin paths, notably `plugins/arDominionB5Plugin`).

## Tests
- PHPUnit config: `phpunit.xml` (bootstrap `test/bootstrap/phpunit.php`, suite `test/phpunit`, runs with `stderr=true`, boots `qubit` test env).
- Run all PHPUnit unit tests: `composer test` (or `vendor/composer/bin/phpunit`).
- Single test/file: `vendor/composer/bin/phpunit test/phpunit/<TestFile>.php` (or pass `--filter <name>`); debug/coverage helpers in composer scripts: `composer test-debug`, `composer test-cov`.
- CI unit-test environment (verified): starts Percona + Elasticsearch from `docker/docker-compose.dev.yml`, then installs demo DB/index before tests:
  - `php symfony tools:install --database-host=127.0.0.1 --database-port=63003 --database-name=atom --database-user=atom --database-password=atom_12345 --search-host=127.0.0.1 --search-port=63002 --search-index=atom --demo --no-confirmation`
  - then `composer test`
- Integration tests: Cypress (`cypress.config.js`, specs under `cypress/e2e/`), default base URL `http://localhost:63001`, admin creds in config env (`demo@example.com` / `demo`); CI runs `npx cypress run -b <browser>` against a fully installed app (nginx/php-fpm/gearman worker) and uses service configs from `test/etc/` and `docker/docker-compose.test.yml`.

## Repo quirks / guardrails
- `dist/` contains built frontend assets and is present in the repo; frontend changes may require rebuilding and can affect generated layout/templates.
- AtoM is commonly deployed to shared/managed hosting; avoid assuming root-only services or long-running infra unless the environment provides them (Docker configs in `docker/` define local dev/test expectations).
- Follow existing project style/tooling and inspect nearby plugin/app code before introducing patterns; this is a mature, plugin-heavy codebase with framework-specific conventions.
