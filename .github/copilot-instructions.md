# Copilot instructions for HairLook

This file gives repository-specific commands, architecture notes, and conventions so future Copilot sessions can work effectively here.

## Quick commands (run from repo root)
- Start local stack (Windows/XAMPP): open XAMPP Control Panel → start Apache and MySQL.
- Import DB (mysql CLI):
  - mysql -u root -p hairlook < "DB Hairlook\hairlook.sql"
- Import DB (phpMyAdmin): http://localhost/phpmyadmin → import `DB Hairlook/hairlook.sql`.

### Run tests (single-file CLI scripts)
- Run full, simple test script: php test_funciones_barberia.php
- Run the alternate test suite: php test_simple_primary_functions.php
- Run client demo script: php Client\user.php

### Run a single test function
- Execute one test function from a test file (example):
  - php -r "require 'test_funciones_barberia.php'; test_validar_correo();"
  - Note: these test files `require_once` the helpers; run from repo root.

### Syntax check / lint (minimal)
- PHP syntax check: php -l "Funciones Hairlook\funciones_barberia.php"
- If a linter is added later, prefer `phpcs` / `phpstan`; none are currently configured.

## High-level architecture
- Procedural PHP backend: core application logic lives in one main helper file:
  - Funciones Hairlook/funciones_barberia.php — contains PDO-based DB helpers, auth, booking, and CRUD functions.
- Data schema: DB Hairlook/hairlook.sql — full MySQL/MariaDB dump (tables: usuario, profesional, servicio, cita, detalle_cita, disponibilidad, etc.).
- Test & demo scripts: simple CLI scripts in repo root and Client/ that exercise core flows (register/login, create appointment, list services).
- No framework: plain PHP 8.x + PDO + session-based auth is used. The app expects to run under Apache (XAMPP) on Windows.

## Key repository conventions and gotchas
- Language & naming:
  - Function and DB identifiers are Spanish (e.g., registrar_usuario, crear_cita, traer_profesionales).
  - DB column names use Spanish terms and some accented identifiers (e.g., `Descripción`) — preserve exact column names and encoding when writing queries.
- Return conventions:
  - Many functions return boolean (success/fail), arrays (data), or null on error — check return types before using results.
  - Some functions return mixed (e.g., administrar_servicios: null|new ID|bool).
- Session keys:
  - Auth sets $_SESSION['usuario'] for clients and $_SESSION['profesional'] for professionals. Verify session_start() is called in scripts that rely on session state (some files have it commented out).
- Database connection:
  - getConnection() uses PDO with hard-coded local credentials (host=localhost, dbname=hairlook, user=root, blank password). Adjust for your environment or move to config before committing secrets.
- File layout / require paths:
  - Core helpers are located at "Funciones Hairlook\funciones_barberia.php". Some test files assume `require_once __DIR__ . '/funciones_barberia.php'` (i.e., file in repo root). When running tests, run from repo root so relative requires resolve; if require failures occur, either update the require path or add a copy/link in the root.
- Error handling:
  - Functions log failures with error_log(...) and return false/null. Tests and scripts usually treat a false/null as failure.
- Side-effecting tests:
  - Several test/demo scripts insert rows into the DB; tests that modify data are commented out in the scripts. Be cautious when running them against a production DB.

## Existing AI assistant-related files
- Claude/OpenCode ignore: Claudeignore/.claudecodeignore — instructs the Claude/OpenCode agent which paths to ignore (node_modules, .env, caches, logs). Copilot should honor similar ignores when exploring or proposing changes.

## Suggested small hygiene steps for maintainers (non-mandatory)
- Standardize require paths to a single canonical location (or add a bootstrap loader) so tests and demo scripts don't rely on fragile relative paths.
- Externalize DB credentials into a config file or environment variables (avoid committing secrets).
- Add a composer.json and simple test runner (PHPUnit) if you want more structured tests; update this file when that exists.

---
