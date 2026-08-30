# ASTGD Task Tracker 🚀

A modern, production-grade **Office Task Tracker** web application built with **Laravel 13**, **PHP 8.3+**, and **Bootstrap 5.3**. Designed for fast-paced office environments to seamlessly assign, track, filter, and analyze operational tasks.

---

## 📑 Table of Contents
- [Architecture & Request Lifecycle](#-architecture--request-lifecycle)
- [Key Features](#-key-features)
- [Configuration & Best Practices](#-configuration--best-practices)
- [Installation & Setup](#-installation--setup)
- [Database Configuration (MySQL & SQLite)](#-database-configuration)
- [Running Automated Tests](#-running-automated-tests)
- [License & Credits](#-license--credits)

---

## 🏗 Architecture & Request Lifecycle

This application strictly follows Laravel's MVC (Model-View-Controller) architecture and industry best practices.

```
       HTTP Request (e.g. GET /tasks?status=Pending)
                           │
                           ▼
                    [routes/web.php]
                           │
                           ▼
            [App\Http\Controllers\TaskController]
             ├── Validates Form Input (via FormRequests)
             ├── Reads configuration via config('office.*')
             └── Interacts with Eloquent Model
                           │
                           ▼
                 [App\Models\Task.php]
             ├── Query Scopes (scopeSearch, scopeFilterStatus, scopeOverdue)
             └── Accessors (is_overdue, is_due_soon)
                           │
                           ▼
                   [Database / SQL]
                           │
                           ▼
            [resources/views/tasks/index.blade.php]
             └── Inherits layouts/app.blade.php
                           │
                           ▼
                   HTML HTTP Response
```

---

## ✨ Key Features

### 1. 📊 Interactive Dashboard
- **Live Metric Cards:** Real-time counters for Total Tasks, Pending, In Progress, Completed, and High Priority.
- **Task Completion Rate:** Dynamic progress bar showing the percentage of completed tasks.
- **Due Soon Alerts:** Dedicated alert section displaying tasks due within the next 3 days.
- **Recent Tasks Widget:** Instant snapshot of the 5 most recently created tasks.

### 2. 📝 Full Task Management (CRUD)
- **Fields Supported:** Title, Description, Assigned To, Priority (*Low, Medium, High*), Status (*Pending, In Progress, Completed*), and Due Date.
- **Robust Validation:** Server-side and client-side validation with friendly custom error messages (e.g., *"Task title is required."*, *"Please select or enter the person responsible for this task."*).
- **Delete Confirmation:** Interactive modal preventing accidental deletions.

### 3. 🔍 Search & Multi-Criteria Filtering
- **Keyword Search:** Instant search across task titles and assigned team members.
- **Multi-Filter:** Combine status, priority, and quick filters (e.g. *Overdue*, *Due Soon*).
- **Overdue Indicator:** Bold visual warning badge for incomplete tasks whose due date has elapsed.
- **Configurable Pagination:** Tasks per page dynamically driven by `.env` (`TASKS_PER_PAGE`).

### 4. 📤 CSV Data Export (Feature Flagged)
- Controlled via the `.env` toggle `ENABLE_TASK_EXPORT`.
- Respects active search and filter parameters during export.

---

## ⚙️ Configuration & Best Practices

### `.env` vs `.env.example`
- **`.env` (Private):** Contains sensitive local credentials (database passwords, app keys, environment toggles). This file is ignored by Git (`.gitignore`) to prevent accidental leaks.
- **`.env.example` (Template):** Committed to source control as a safe template. When cloning the repository, developers copy `.env.example` to `.env` and fill in their local values.

### The `config/` Wrapper Architecture
In Laravel, calling `env('VARIABLE_NAME')` outside configuration files causes `env()` to return `null` when running `php artisan config:cache` in production. 

To ensure **100% caching compatibility**, all custom environment variables are registered in [config/office.php](file:///c:/Users/Emon%20Ahmed/OneDrive/Desktop/office_tracking/config/office.php) and accessed via `config('office.key')`:

| Environment Variable | Config Mapping | Default Value | Description |
|---|---|---|---|
| `OFFICE_APP_NAME` | `office.app_name` | `"Office Task Tracker"` | Brand name shown in navigation & title |
| `COMPANY_NAME` | `office.company_name` | `"Zenith Core Ltd."` | Company name shown in navbar and footer |
| `COMPANY_EMAIL` | `office.company_email` | `"support@zenithcore.com"` | Official support contact email |
| `TASKS_PER_PAGE` | `office.tasks_per_page` | `10` | Number of tasks per paginated page |
| `ENABLE_TASK_EXPORT` | `office.enable_task_export` | `true` | Feature flag to enable/disable CSV exports |

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.2 or 8.3+
- Composer 2.x
- Node.js & NPM
- MySQL / MariaDB (or SQLite for zero-configuration testing)

### Step-by-Step Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url> office_tracking
   cd office_tracking
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment File:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Database Migrations & Seeders:**
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Start the Development Server:**
   ```bash
   php artisan serve
   ```
   Open [http://localhost:8000](http://localhost:8000) in your browser.

---

## 🗄️ Database Configuration

### Option A: SQLite (Default - Zero Configuration)
SQLite is pre-configured and requires zero setup:
```env
DB_CONNECTION=sqlite
```

### Option B: MySQL / MariaDB
For a production MySQL setup:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=office_task_tracker
DB_USERNAME=root
DB_PASSWORD=your_password
```
Make sure to create the database `office_task_tracker` in MySQL before running `php artisan migrate`.

---

## 🧪 Running Automated Tests

A comprehensive PHPUnit test suite is included covering metrics, CRUD validations, search, filtering, and feature flags.

Run the test suite:
```bash
php artisan test
```

---

## 📜 Git Commit Strategy

This project follows meaningful, staged commit conventions:
1. `chore: setup project foundation and custom environment configuration layer`
2. `feat: create tasks schema migration, Eloquent model with query scopes and seeders`
3. `feat: implement FormRequests with custom validation messages and TaskController CRUD`
4. `feat: add dynamic DashboardController with completion analytics and due soon alerts`
5. `feat: design responsive UI with Bootstrap, Overdue indicators, modal delete and CSV export`
6. `test: add comprehensive feature test suite and documentation in README`

---

## 👤 Author
Developed with precision for the **Office Task Tracker** assignment.
