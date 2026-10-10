# Elshbiny Core

Laravel backend for the Elshbiny digital storefront. It provides a versioned API and a Filament administration panel for catalog management, quote requests, consultations, and the fit-out questionnaire flow.

## Main capabilities

- Product and category catalog API
- Sanctum-backed authentication for the storefront
- Quote and inquiry workflows
- Fit-out questionnaire and result resources
- Filament tools for catalog and CRM operations
- Search integration through Laravel Scout, with Meilisearch available

## Stack

- PHP 8.2 or newer and Laravel 12
- Composer
- Node.js and npm for the admin assets
- SQLite for local development, or MySQL/MariaDB
- Optional Meilisearch service for search

## Local setup

```bash
cp .env.example .env
composer install
mkdir -p database
touch database/database.sqlite
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve --port=8011
```

The local API base is `http://127.0.0.1:8011/api/v1`. Configure `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` to match the local storefront origin. Search and outbound mail can remain local or disabled during development.

This public export contains no production database, demo/customer records, admin credentials, or payment secrets. It deliberately has no privileged-user seeders; provision real administrator accounts through the owner-controlled process.

## License

No project license has been selected. Public visibility does not grant permission to reuse or redistribute this code; all rights remain reserved by the copyright holder unless a license is added. Bundled third-party components remain subject to their own terms.
