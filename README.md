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
| Cart / checkout | `/cart`, `/checkout` |
| Contact | `/contact` |
| Sign up / log in | `/register`, `/login` (also the popup under the account icon) |
| My account / orders | `/account`, `/account/orders` |
| Admin | `/admin` (sections below) |

### Admin sections

| Section | URL | Permission |
| --- | --- | --- |
| Dashboard | `/admin` | `dashboard.view` (numbers) |
| Products | `/admin/products` | `products.view` / `products.manage` |
| Departments | `/admin/departments` | `departments.manage` |
| Categories | `/admin/categories` | `categories.manage` |
| Orders | `/admin/orders` | `orders.view` / `orders.manage` |
| Users | `/admin/users` | `users.view` / `users.manage` |
| Roles & permissions | `/admin/roles` | `roles.manage` |
| Inbox | `/admin/emails/inbox` | `emails.inbox` |
| Outgoing email log | `/admin/emails/log` | `emails.log` |
| Email templates | `/admin/emails/templates` | `emails.templates` |
| Newsletter subscribers | `/admin/emails/subscribers` | `emails.subscribers` |
| Campaigns | `/admin/emails/campaigns` | `emails.campaigns` |
| Site settings | `/admin/settings` | `settings.manage` |
| Homepage sliders | `/admin/sliders` | `settings.manage` |

Every admin route also requires `admin.access`.

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

## Payments (Stripe)

Stripe Checkout is called through Laravel's HTTP client, so there's no Stripe package.

1. Put your secret key in `.env` as `STRIPE_SECRET` (test keys start with `sk_test_`).
2. In the Stripe dashboard, add a webhook endpoint `{APP_URL}/webhooks/stripe` for
   `checkout.session.completed` and put its signing secret in `STRIPE_WEBHOOK_SECRET`.
   To test locally, use the Stripe CLI: `stripe listen --forward-to localhost:8000/webhooks/stripe`.

Stock is reserved when an order is placed. An order becomes **Paid** on the
success redirect or the webhook, whichever arrives first. Cancelling or
refunding an order in the admin puts its stock back. Without `STRIPE_SECRET`,
orders are saved as *Pending payment*.

## Email (Mailgun)

Mail goes out over Mailgun SMTP using Laravel's built-in `smtp` mailer, so
there's no Mailgun package. Set in `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.yourdomain.com
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=hello@yourdomain.com
MAILGUN_WEBHOOK_SIGNING_KEY=your-webhook-signing-key
```

Then in Mailgun:

- **Webhooks** → point *delivered, opened, clicked, permanent/temporary failure,
  complained, unsubscribed* at `{APP_URL}/webhooks/mailgun/events`. The outgoing
  log then shows delivery status. Complaints and hard bounces unsubscribe
  the address.
- **Receiving → Routes** → *forward* your store address to
  `{APP_URL}/webhooks/mailgun/inbound`. Incoming mail then lands in the admin inbox,
  and replies you send from there go out from your store address.

Emails the store sends automatically: welcome, order confirmation, order
status (shipped/delivered/cancelled/refunded), password reset and newsletter
welcome. You can edit their wording under **Emails → Templates**.

Until mail is configured, `MAIL_MAILER=log` writes emails to
`storage/logs/laravel.log`.

### Queue worker

Newsletter campaigns are sent by a queued job. Run a worker in production,
for example under Supervisor:

```bash
php artisan queue:work --tries=1
```

### CDN files

Versions are pinned in `config/store.php`. To have browsers verify the files,
set `CDN_BOOTSTRAP_CSS_SRI`, `CDN_BOOTSTRAP_JS_SRI` and `CDN_BOOTSTRAP_ICONS_SRI`
in `.env` to the SRI hashes published by Bootstrap and jsDelivr.

## Tests

```bash
php artisan test
```

Tests run on in-memory SQLite, so no MySQL is needed.
