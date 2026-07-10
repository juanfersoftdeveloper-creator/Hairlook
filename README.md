# HairLook - Salon and Barbershop Management System

HairLook is a web-oriented management application for user authentication, appointment scheduling, stylist agendas, and service tracking for hair salons and barber shops. The project is split into a **PHP Backend** API and a **React + Vite Frontend** application.

---
Github Profile Link: https://github.com/juanfersoftdeveloper-creator/Hairlook
## 🛠️ Tech Stack

### Backend
* **Language/Engine**: PHP 8.x (procedural, PDO)
* **Server**: Apache (XAMPP Control Panel)
* **Database**: MySQL / MariaDB (XAMPP)

### Frontend
* **Framework**: React 18+ (Vite)
* **State Management**: React Context API (for Auth and Session states)
* **Styling**: Vanilla CSS (highly customizable layout and aesthetics)

---

## 📁 Project Structure

```text
Hairlook/
├── backend/                   # PHP Backend Application
│   ├── app/                   # Backend core logic
│   │   ├── bootstrap.php      # Main boostrapper for DB connections & functions
│   │   └── funciones_barberia.php
│   ├── config/
│   │   └── database.php       # DB credentials
│   ├── database/
│   │   └── hairlook.sql       # MySQL schema dump
│   ├── public/                # Web entry points & JSON API endpoints
│   │   ├── index.php          # Redirects to app root
│   │   ├── registro.php       # Legacy registration view
│   │   └── c_registro_usuario.php # REST API endpoint for user registration (POST)
│   ├── scripts/               # CLI utility and database schema scripts
│   │   ├── apply_schema_migration.php # Applies incremental DB modifications
│   │   ├── check_schema.php   # Compares active DB with expectation schema
│   │   ├── check_schema_detail.php
│   │   ├── show_columns.php
│   │   └── user_flow.php      # CLI client demo flow
│   └── tests/                 # PHPUnit-equivalent CLI test suites
│       ├── test_funciones_barberia.php
│       └── test_simple_primary_functions.php
│
├── frontend/                  # React Frontend Application
│   ├── public/
│   ├── src/
│   │   ├── assets/            # Static assets & icons
│   │   ├── components/        # UI components
│   │   │   ├── Home.jsx       # Public landing page
│   │   │   ├── auth/          # Login & registration forms
│   │   │   │   ├── Login.jsx
│   │   │   │   ├── RegisterCliente.jsx
│   │   │   │   └── RegisterProfesional.jsx
│   │   │   └── cliente/       # Client Dashboard / User home page
│   │   │       └── Home.jsx
│   │   ├── context/
│   │   │   └── AuthContext.jsx # Global auth provider
│   │   └── services/
│   │       └── authService.js # API helper methods connecting to the backend
│   ├── package.json
│   └── vite.config.js
│
├── .antigravityignore         # Context ignore configuration
└── .cursorignore              # Cursor tooling ignore configuration
```

---

## 🚀 Setup & Installation (Local XAMPP)

### 1. Database Setup
1. Start **Apache** and **MySQL** services in the XAMPP Control Panel.
2. Open `http://localhost/phpmyadmin` and create a database named `hairlook`.
3. Import the base database schema:
   * Import `backend/database/hairlook.sql` via phpMyAdmin **or** run the following in terminal:
     ```bash
     mysql -u root -p hairlook < backend/database/hairlook.sql
     ```
4. Adjust DB connection settings if needed in `backend/config/database.php`.

### 2. Database Migrations
We have added scripts to verify and run incremental changes dynamically.
* **Verify Sync State**: Checks if local MySQL schema matches expectations.
  ```bash
  php backend/scripts/check_schema.php
  ```
* **Apply Migrations**: Runs pending table additions/alterations (e.g. `usuario` profiles, rating columns, notifications).
  ```bash
  php backend/scripts/apply_schema_migration.php
  ```

---

## 💻 Running the Applications

### Backend (PHP Server)
Since it runs through Apache (XAMPP), place the root folder in your `htdocs` folder.
* **Home Directory**: `http://localhost/Hairlook/backend/`
* **JSON API registration**: `POST http://localhost/Hairlook/backend/public/c_registro_usuario.php`

### Frontend (React App)
1. Navigate to the frontend directory:
   ```bash
   cd frontend
   ```
2. Install dependencies:
   ```bash
   npm install
   ```
3. Run the Vite development server:
   ```bash
   npm run dev
   ```
4. Access the React app at `http://localhost:5173/` (or the port specified by Vite).

---

## 🧪 CLI Commands (Backend Utilities)

| Command / Script | Purpose |
|------------------|---------|
| `php backend/tests/test_funciones_barberia.php` | Run the core backend logic tests |
| `php backend/scripts/user_flow.php` | Simulates user flow (registrations, bookings) on the CLI |
| `php backend/scripts/check_schema.php` | Compares current MySQL state with `hairlook.sql` |
| `php backend/scripts/apply_schema_migration.php` | Applies database migrations automatically |
