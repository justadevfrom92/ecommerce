<?php

return [

    /*
    | Front-end libraries are loaded from a CDN (no build step). Pin versions
    | here; fill in "integrity" with the SRI hash published by each project
    | to have the browser verify the file.
    */
    'cdn' => [
        'bootstrap_css' => [
            'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
            'integrity' => env('CDN_BOOTSTRAP_CSS_SRI'),
        ],
        'bootstrap_js' => [
            'url' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js',
            'integrity' => env('CDN_BOOTSTRAP_JS_SRI'),
        ],
        'bootstrap_icons' => [
            'url' => 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css',
            'integrity' => env('CDN_BOOTSTRAP_ICONS_SRI'),
        ],
    ],

    // Rows-per-page choices offered by every data table.
    'per_page_options' => [10, 20, 50, 100],

    /*
    | Every permission the admin area checks. Seeded into the permissions
    | table; roles are built from these in /admin/roles.
    */
    'permissions' => [
        'General' => [
            'admin.access' => 'Access the admin area',
            'settings.manage' => 'Manage site settings & homepage sliders',
        ],
        'Catalog' => [
            'products.view' => 'View products',
            'products.manage' => 'Create, edit & delete products',
            'departments.manage' => 'Manage departments',
            'categories.manage' => 'Manage categories',
        ],
        'Sales' => [
            'orders.view' => 'View orders',
            'orders.manage' => 'Update order status',
        ],
        'People' => [
            'users.view' => 'View users',
            'users.manage' => 'Create, edit & deactivate users',
            'roles.manage' => 'Manage roles & permissions',
        ],
        'Emails' => [
            'emails.templates' => 'Edit email templates',
            'emails.log' => 'View the outgoing email log',
            'emails.inbox' => 'Read & reply to inbox messages',
        ],
    ],

];
