# StudyPlanner

A native PHP and MySQL study planner organized with MVC. It includes account registration and login, subject management, study task and exam scheduling, and progress tracking. User data is private to each account; admins can review and manage records across accounts.

## Requirements

- Laragon with Apache, PHP 8.1 or newer, and MySQL/MariaDB
- PHP PDO MySQL extension enabled

## Run locally

1. Put this folder under Laragon's `www` directory and start Apache and MySQL.
2. For a new install, run `php setup.php` from this directory to create the database and tables from `db/schema.sql`. For the original starter database, run `db/migrations/001_add_accounts_subjects_and_task_status.sql` once instead; it retains old task rows as unassigned legacy data. Then run migrations `002_add_activity_logs.sql` and `003_add_exams.sql` once to add the activity log and exam tables.
3. If your local database credentials differ from Laragon defaults, set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in the Apache/PHP environment.
4. Create your first regular account at `http://localhost/studyplanner/signup`.
5. To create an admin account, set `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` (12-72 characters, including uppercase and lowercase letters, a number, and a symbol) in the terminal environment, then run `php db/create_admin.php`. Admin is not selectable on public signup.
6. Open `http://localhost/studyplanner/`.

The app uses Laragon's common local defaults: `127.0.0.1:3306`, database `studyplanner`, user `root`, and an empty password. If PHP is on your terminal `PATH`, you can also run `php -S localhost:8000` from this directory and open `http://localhost:8000/`.

## MVC map

- `app/Controllers/` handles requests and selects responses.
- `app/Models/` contains database operations and domain data.
- `app/Views/` contains PHP templates for rendered HTML.
- `app/Core/` contains the router, base controller, and PDO connection.
- `routes/web.php` maps HTTP methods and paths to controller actions.
- `public/assets/` contains frontend CSS and JavaScript.
- `config/database.php` reads DB settings from environment variables with Laragon defaults.
- `db/schema.sql` creates the users, subjects, tasks, exams, and activity log tables.
- `setup.php` applies the full schema from the command line for a fresh install; it is not accessible over HTTP.
- Existing databases can add exams with `db/migrations/003_add_exams.sql`.
- `db/migrations/` upgrades existing database schemas without deleting existing rows.
- `db/create_admin.php` creates an admin using environment-provided credentials and is CLI-only.
- Admins can open `/admin` for the account directory, `/admin/manage-users` to add or delete regular accounts, `/admin/activity` to review recent activity, and `/admin/users/{id}` to review that account's subjects and tasks.

## Add a route

Register a route in `routes/web.php`, implement the action in a controller, keep database queries in a model, and render a template through the base controller. Dynamic route values use braces, such as `/tasks/{id}/toggle`.

Signup and admin-created accounts default to the `user` role. Account passwords must be 8-72 characters and include uppercase and lowercase letters, a number, and a symbol; the signup and admin account forms require password confirmation. CLI-created admins require at least 12 characters with the same strength rules. Users can schedule exams for their subjects and update exam status. Admins can create and delete regular accounts and review recent authentication, account, subject, task, and exam events; deleting an account also deletes its study data. Regular users can only view and manage their own data. Passwords are hashed, forms use CSRF tokens, and task, subject, and exam queries enforce ownership. This starter still needs production deployment hardening, HTTPS, rate limiting, and a robust error/logging setup before public use.