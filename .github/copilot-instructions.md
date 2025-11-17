## Quick context

This is a Laravel 12 + Vue 3 (Inertia) single-repo application. The frontend lives under `resources/js` (Inertia pages in `resources/js/Pages`), and the backend is a standard Laravel app in `app/` with routes in `routes/`.

## Big-picture architecture

- Server: Laravel (PHP 8.2) — controllers in `app/Http/Controllers`, form validation in `app/Http/Requests`, models in `app/Models`.
- Client: Vue 3 powered by Inertia (`resources/js/app.js`). Pages are single-file Vue components under `resources/js/Pages` and are rendered via `Inertia::render()` from routes/controllers.
- Routing: Laravel routes in `routes/web.php` and `routes/auth.php`. Ziggy is installed (`vendor/tightenco/ziggy`) for generating named routes on the client.
- Assets: Vite + Tailwind. Tailwind config: `tailwind.config.js`. Frontend entry is `resources/js/app.js` and `resources/css/app.css`.

## Developer workflows & commands (tested from project root)

- Install & setup (recommended):
  - `composer install` then `npm install`
  - Copy `.env.example` to `.env` and run `php artisan key:generate`
  - Run migrations: `php artisan migrate`
  - Alternatively use: `composer run-script setup` (runs install, migrate, npm build as defined in `composer.json`).
- Local dev with live reload (uses concurrently):
  - `composer run-script dev` — this starts `php artisan serve`, queue listener, and `npm run dev` (Vite). On Windows PowerShell this command works as-is if you have Composer and Node installed.
- Frontend only:
  - `npm run dev` (Vite dev server)
  - `npm run build` (production assets)
- Tests:
  - `composer run-script test` or `php artisan test` (Pest is configured under `tests/`).

## Project-specific conventions & patterns

- Inertia pages are referenced by name in backend code. Example: `Inertia::render('Dashboard')` maps to `resources/js/Pages/Dashboard.vue`.
- Use `app/Http/Requests` for request validation — controllers expect validated data from these classes.
- Eloquent models use PSR-4 autoloading. Factories live in `database/factories` and seeders in `database/seeders`.
- Routes that require auth use the `auth` middleware (see `routes/web.php`). Many examples use closures returning Inertia pages — prefer controllers for complex logic.

## Integration points & external dependencies

- Ziggy (route generation in JS) — used via `ZiggyVue` in `resources/js/app.js`.
- Inertia (`@inertiajs/vue3`) — client-server bridge; pages are client-side Vue components rendered by server routes.
- Tailwind + PostCSS configured in `package.json`/`postcss.config.js`/`tailwind.config.js`.

## Where to look for examples

- Inertia+Vue setup: `resources/js/app.js` and `resources/js/Pages/`.
- Auth and profile flows: `routes/auth.php`, `app/Http/Controllers/ProfileController.php`, and `resources/js/Pages/Profile*.vue` (if present).
- Database migrations: `database/migrations/` and example factory `database/factories/UserFactory.php`.

## Small how-to examples

- Add a new page:
  1. Create `resources/js/Pages/MyNewPage.vue`.
  2. Add route/controller that returns `Inertia::render('MyNewPage')`.
  3. Use Ziggy to reference named routes in Vue: `route('route.name')`.

## Assumptions and notes for the agent

- PHP 8.2 and Laravel 12 are used — prefer PHP 8.2 language features only where consistent with repository style.
- Do not change CI or Composer script names unless the change includes updated scripts that maintain existing behaviors (setup/dev/test).
- When modifying frontend routes or components, keep server-side Inertia names in sync with `resources/js/Pages` filenames.

If anything is unclear or you want more/less detail in any section (build, tests, or architecture), tell me which area to expand.
