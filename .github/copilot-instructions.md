# Copilot instructions for HairLook

This file gives repository-specific commands, architecture notes, and conventions so future Copilot sessions can work effectively here.

## Quick commands (run from repo root)
- Start local stack (Windows/XAMPP): open XAMPP Control Panel → start Apache and MySQL.
- Import DB (mysql CLI):
  - mysql -u root -p hairlook < database/hairlook.sql
- Import DB (phpMyAdmin): http://localhost/phpmyadmin → import `database/hairlook.sql`.

### Run tests (single-file CLI scripts)
- Run full test script: `php tests/test_funciones_barberia.php`
- Run alternate test suite: `php tests/test_simple_primary_functions.php`
- Run client demo script: `php scripts/user_flow.php`

### Syntax check / lint (minimal)
- PHP syntax check: `php -l app/funciones_barberia.php`
- If a linter is added later, prefer `phpcs` / `phpstan`; none are currently configured.

## High-level architecture
- Procedural PHP backend: core application logic lives in:
  - `app/funciones_barberia.php` — PDO-based DB helpers, auth, booking, and CRUD functions.
  - `app/bootstrap.php` — canonical include entry point for all scripts and pages.
- Database config: `config/database.php` — local XAMPP credentials (host, dbname, user, password).
- Data schema: `database/hairlook.sql` — full MySQL/MariaDB dump.
- Web pages: `public/` — user-facing PHP pages (registro, index).
- CLI scripts: `scripts/` — demo flows and DB utilities.
- Tests: `tests/` — lightweight CLI test suites.
- No framework: plain PHP 8.x + PDO + session-based auth under Apache (XAMPP) on Windows.

## Key repository conventions and gotchas
- Language & naming:
  - Function and DB identifiers are Spanish (e.g., registrar_usuario, crear_cita, traer_profesionales).
  - DB column names use Spanish terms — preserve exact column names and encoding when writing queries.
- Return conventions:
  - Many functions return boolean (success/fail), arrays (data), or null on error.
  - Some functions return mixed (e.g., administrar_servicios: null|new ID|bool).
- Session keys:
  - Auth sets $_SESSION['usuario'] for clients and $_SESSION['profesional'] for professionals.
- Database connection:
  - `getConnection()` reads from `config/database.php`. Adjust for your environment before committing secrets.
- File layout / require paths:
  - Always include via `require_once __DIR__ . '/../app/bootstrap.php';` from public/, scripts/, and tests/.
- Error handling:
  - Functions log failures with error_log(...) and return false/null.
- Side-effecting tests:
  - Demo scripts insert rows into the DB; use a disposable local instance when testing.

## Notable files
- `app/funciones_barberia.php` — main application logic (read first for backend bugs).
- `scripts/user_flow.php` — CLI demo: register, login, create service and appointment.
- `database/hairlook.sql` — canonical schema; import before running tests.
- `public/registro.php` — web registration form.

---

