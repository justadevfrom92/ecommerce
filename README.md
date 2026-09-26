# MyStore

An e-commerce site built with **Laravel 13**, **MySQL** and **Bootstrap 5**.
Bootstrap and Bootstrap Icons load from a CDN, and the site's own CSS and JS are
plain files in `public/`, so there is **no Node, no Vite and no build step**.

The layout follows the same ideas as the Phoenix admin template: a Bootstrap
shell, reusable table, card and slider pieces, and admin sections at their own
URLs. All code here is original.

## Requirements

- PHP 8.3+ with `pdo_mysql`
- Composer
- MySQL 8+ or MariaDB 10.6+

## Setup

```bash
cp .env.example .env             # then set DB_DATABASE / DB_USERNAME / DB_PASSWORD
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link         # product image uploads
php artisan serve
```

Open http://localhost:8000.

**First admin:** `admin@mystore.test` / `password` (change it in `.env` with
`ADMIN_EMAIL` / `ADMIN_PASSWORD` before seeding, or under *My account* after
logging in). In `APP_ENV=local`, demo departments, products and 75 customers
are seeded too.

## What's where

| Area | URL |
| --- | --- |
| Home (department tiles + product sliders) | `/` |
| Search / shop all | `/search?q=` |
| Department (a slider per category) | `/departments/{slug}` |
| Category grid | `/categories/{slug}` |
| Product | `/products/{slug}` |
| Cart | `/cart` |
| Sign up / log in | `/register`, `/login` (also the popup under the account icon) |
| My account | `/account` |
| Admin | `/admin`, `/admin/products`, `/admin/users`, … |

### Reusable pieces

- **Data table**: `<x-data-table>` (`resources/views/components/data-table.blade.php`)
  paired with `App\Support\DataTable::paginate()` in the controller. It handles
  search, sortable columns, rows per page, extra filters, and
  *Showing X to Y of Z results* with `1 2 3 4 5 6 7 … 20` pagination
  (`<x-pagination>`, window logic in `App\Support\PageWindow`).
- **Product slider**: `<x-product-slider title="…" :products="$products" :view-all="$url" />`,
  built on CSS scroll-snap plus a few lines in `public/js/app.js`, with no slider library.
- **Product card**: `<x-product-card :product="$product" />`.

### Roles & permissions

Every permission is listed in `config/store.php`. Roles are sets of
permissions. A role flagged *super* passes every check. Permissions work as
normal Laravel gates (`@can('orders.manage')`, `can:` middleware), and the admin
sidebar shows only the sections a user's roles allow. After adding a permission
to the config, run `php artisan db:seed --class=PermissionSeeder`.

### CDN files

Versions are pinned in `config/store.php`. To have browsers verify the files,
set `CDN_BOOTSTRAP_CSS_SRI`, `CDN_BOOTSTRAP_JS_SRI` and `CDN_BOOTSTRAP_ICONS_SRI`
in `.env` to the SRI hashes published by Bootstrap and jsDelivr.

## Tests

```bash
php artisan test
```

Tests run on in-memory SQLite, so no MySQL is needed.
