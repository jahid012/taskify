# Taskify

Taskify is a simple task management system for teams built with Laravel and jQuery. It provides a clean shared board for capturing work, updating progress, tracking overdue items, and keeping daily execution organized without a heavy workflow.

## Features

- Create, edit, and delete tasks from a single dashboard
- Track work by status with `Pending`, `In Progress`, and `Completed` lanes
- Add priority and optional due dates to keep daily work organized
- Update status quickly from each task card
- View live summary metrics for total, pending, in-progress, completed, due-today, and overdue tasks
- Filter by status and search tasks instantly on the page
- Use backend validation and automated tests for core flows

## Technologies Used

- PHP 8.3
- Laravel 13
- MySQL
- jQuery 3.7
- Blade
- Vite
- Tailwind CSS 4
- PHPUnit 12

## Setup Instructions

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Create the environment file and generate the application key:

```bash
copy .env.example .env
php artisan key:generate
```

4. Create a MySQL database named `taskify` and make sure your `.env` credentials match your local setup.

5. Run migrations:

```bash
php artisan migrate
```

6. Optionally seed demo tasks:

```bash
php artisan db:seed
```

7. Start the app for development:

```bash
php artisan serve
npm run dev
```

8. Build frontend assets for a production-style run:

```bash
npm run build
```

## Testing Approach

The critical backend flows are covered with automated tests:

- Dashboard loads successfully
- Tasks can be created
- Tasks can be updated
- Task status can be changed independently
- Tasks can be deleted
- Invalid payloads are rejected
- Task status and priority enums behave as expected

Run the test suite with:

```bash
php artisan test
```

### Testing Database Note

The local development environment for this project did not have the SQLite PDO driver enabled, so tests are configured to use a dedicated MySQL database named `taskify_testing`.

Create it once with:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS taskify_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

If your environment supports `pdo_sqlite` and you prefer SQLite for tests, you can change the test database settings in `phpunit.xml`.

## Assumptions and Decisions

- This is a shared team board, so there is no authentication or per-user ownership layer.
- Blade partials are returned from the backend after task mutations so the frontend stays simple while still feeling responsive.
- Priority was included as a lightweight organizational aid even though only status tracking was explicitly required.
- The database starts empty unless the optional seed command is run.

## Important Paths

- `app/Http/Controllers/TaskController.php`
- `app/Http/Requests/*`
- `app/Enums/*`
- `resources/views/tasks/*`
- `resources/js/task-board.js`
- `tests/Feature/TaskManagementTest.php`

## GitHub Repository Link

No GitHub remote is attached from this environment yet. The project can be pushed after connecting a repository, and a local commit history has been prepared to make that straightforward.
