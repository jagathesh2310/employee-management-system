# Employee Management System (Laravel 13)

This repository will contain a complete Laravel 13 learning project: an Employee Management System (EMS) implementing modern Laravel and PHP best practices.

This initial commit adds scaffolding and a development plan. The full Laravel application (Sail configuration, app code, migrations, factories, seeders, tests, and documentation) will be added in follow-up commits.

Runbook (once the app is present)

cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
npm install
npm run dev

See PROJECT_PLAN.md for details about structure, features, and the work plan.
