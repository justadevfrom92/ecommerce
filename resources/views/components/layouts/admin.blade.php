@props(['title' => null, 'heading' => null])
@php
    // Sidebar sections. A link shows only if its route exists and the user holds its permission.
    $nav = [
        'Overview' => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'speedometer2', 'can' => 'dashboard.view', 'match' => 'admin.dashboard'],
        ],
        'Catalog' => [
            ['route' => 'admin.products.index', 'label' => 'Products', 'icon' => 'box-seam', 'can' => 'products.view', 'match' => 'admin.products.*'],
            ['route' => 'admin.departments.index', 'label' => 'Departments', 'icon' => 'diagram-3', 'can' => 'departments.manage', 'match' => 'admin.departments.*'],
            ['route' => 'admin.categories.index', 'label' => 'Categories', 'icon' => 'tags', 'can' => 'categories.manage', 'match' => 'admin.categories.*'],
        ],
        'Sales' => [
            ['route' => 'admin.orders.index', 'label' => 'Orders', 'icon' => 'receipt', 'can' => 'orders.view', 'match' => 'admin.orders.*'],
        ],
        'People' => [
            ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => 'people', 'can' => 'users.view', 'match' => 'admin.users.*'],
            ['route' => 'admin.roles.index', 'label' => 'Roles & permissions', 'icon' => 'shield-lock', 'can' => 'roles.manage', 'match' => 'admin.roles.*'],
        ],
        'Emails' => [
            ['route' => 'admin.emails.inbox.index', 'label' => 'Inbox', 'icon' => 'inbox', 'can' => 'emails.inbox', 'match' => 'admin.emails.inbox.*'],
            ['route' => 'admin.emails.log.index', 'label' => 'Outgoing log', 'icon' => 'send', 'can' => 'emails.log', 'match' => 'admin.emails.log.*'],
            ['route' => 'admin.emails.templates.index', 'label' => 'Templates', 'icon' => 'file-earmark-text', 'can' => 'emails.templates', 'match' => 'admin.emails.templates.*'],
            ['route' => 'admin.emails.subscribers.index', 'label' => 'Subscribers', 'icon' => 'person-lines-fill', 'can' => 'emails.subscribers', 'match' => 'admin.emails.subscribers.*'],
            ['route' => 'admin.emails.campaigns.index', 'label' => 'Campaigns', 'icon' => 'megaphone', 'can' => 'emails.campaigns', 'match' => 'admin.emails.campaigns.*'],
        ],
        'Settings' => [
            ['route' => 'admin.settings.edit', 'label' => 'Site settings', 'icon' => 'gear', 'can' => 'settings.manage', 'match' => 'admin.settings.*'],
            ['route' => 'admin.sliders.index', 'label' => 'Homepage sliders', 'icon' => 'collection', 'can' => 'settings.manage', 'match' => 'admin.sliders.*'],
        ],
    ];
    $user = auth()->user();
    $nav = collect($nav)
        ->map(fn ($links) => collect($links)->filter(fn ($l) => Route::has($l['route']) && $user->can($l['can'])))
        ->filter->isNotEmpty();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $title ? $title.' · Admin' : 'Admin'])
    <meta name="robots" content="noindex">
</head>
<body class="admin-body">
<a class="visually-hidden-focusable" href="#main">Skip to content</a>

<header class="admin-topbar navbar bg-body border-bottom sticky-top px-3">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-link nav-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>
        <a href="{{ route('home') }}" class="site-logo" aria-label="{{ setting('store_name') }} home">
            <img src="{{ asset('images/logo.svg') }}" alt="{{ setting('store_name') }}" width="36" height="36">
        </a>
        <span class="badge text-bg-dark">Admin</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex"><i class="bi bi-shop me-1"></i>View store</a>
        <div class="dropdown">
            <button class="btn btn-link nav-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account"><i class="bi bi-person-circle"></i></button>
            <div class="dropdown-menu dropdown-menu-end shadow">
                <div class="px-3 py-2 small">
                    <div class="fw-semibold">{{ $user->name }}</div>
                    <div class="text-body-secondary">{{ $user->roles->pluck('name')->join(', ') ?: 'No role' }}</div>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('account.edit') }}">My account</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item">Log out</button></form>
            </div>
        </div>
    </div>
</header>

<div class="admin-shell">
    <aside class="offcanvas-lg offcanvas-start admin-sidebar border-end bg-body" tabindex="-1" id="admin-sidebar" aria-label="Admin navigation">
        <div class="offcanvas-header d-lg-none">
            <h2 class="offcanvas-title h6">Admin menu</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="Close"></button>
        </div>
        <nav class="offcanvas-body flex-column p-3">
            @foreach ($nav as $group => $links)
                <div class="admin-nav-group small text-uppercase text-body-secondary fw-semibold mt-3 mb-1 px-2">{{ $group }}</div>
                <ul class="nav nav-pills flex-column">
                    @foreach ($links as $link)
                        <li class="nav-item">
                            <a href="{{ route($link['route']) }}" class="nav-link d-flex align-items-center gap-2 @if (request()->routeIs($link['match'])) active @endif"
                               @if (request()->routeIs($link['match'])) aria-current="page" @endif>
                                <i class="bi bi-{{ $link['icon'] }}"></i>{{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>
    </aside>

    <main id="main" class="admin-main flex-grow-1 p-3 p-lg-4">
        @if ($heading ?? $title)
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <h1 class="h3 mb-0">{{ $heading ?? $title }}</h1>
                @isset($actions)
                    <div class="d-flex gap-2">{{ $actions }}</div>
                @endisset
            </div>
        @endif
        @include('partials.flash')
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the errors below.</strong>
            </div>
        @endif
        {{ $slot }}
    </main>
</div>

<footer class="admin-footer border-top small text-body-secondary px-3 py-2 d-flex justify-content-end">
    &copy; {{ date('Y') }} {{ setting('store_name') }}
</footer>

@include('partials.scripts')
@stack('scripts')
</body>
</html>
