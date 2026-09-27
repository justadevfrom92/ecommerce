@props(['title' => null, 'heading' => null, 'subtitle' => null, 'crumbs' => []])
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

<header class="admin-topbar navbar sticky-top px-3 px-lg-4">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-link nav-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>
        <a href="{{ route('home') }}" class="site-logo d-flex align-items-center gap-2 text-decoration-none" aria-label="{{ setting('store_name') }} home">
            <img src="{{ asset('images/logo.svg') }}" alt="{{ setting('store_name') }}" width="34" height="34">
        </a>
        <span class="pill pill-dark ms-1">Admin</span>
    </div>
    @can('products.view')
        <form action="{{ route('admin.products.index') }}" method="GET" role="search" class="d-none d-md-block mx-auto">
            <div class="search-pill">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="search" class="form-control" placeholder="Search products…" aria-label="Search products">
            </div>
        </form>
    @endcan
    <div class="d-flex align-items-center gap-1">
        <a href="{{ route('home') }}" class="nav-icon-btn" title="View store" aria-label="View store"><i class="bi bi-shop"></i></a>
        @if (Route::has('admin.emails.inbox.index') && $user->can('emails.inbox'))
            @php($unread = \App\Models\InboundMessage::whereNull('read_at')->count())
            <a href="{{ route('admin.emails.inbox.index', ['filter' => 'unread']) }}" class="nav-icon-btn position-relative" aria-label="Inbox, {{ $unread }} unread" title="Inbox">
                <i class="bi bi-bell"></i>
                @if ($unread)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill cart-badge" style="background: var(--ms-red) !important">{{ $unread > 99 ? '99+' : $unread }}</span>@endif
            </a>
        @endif
        <div class="dropdown ms-1">
            <button class="btn p-0 border-0" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account">
                <span class="avatar-sm" style="width: 2.25rem; height: 2.25rem">{{ \Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('') }}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm p-0" style="min-width: 15rem">
                <div class="px-3 py-3 border-bottom">
                    <div class="fw-bold" style="color: var(--ms-heading)">{{ $user->name }}</div>
                    <div class="small text-muted-2">{{ $user->roles->pluck('name')->join(', ') ?: 'No role' }}</div>
                </div>
                <div class="py-2">
                    <a class="dropdown-item" href="{{ route('account.edit') }}"><i class="bi bi-person me-2"></i>My account</a>
                    <a class="dropdown-item" href="{{ route('home') }}"><i class="bi bi-shop me-2"></i>View store</a>
                </div>
                <div class="border-top p-2">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-soft btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i>Log out</button></form>
                </div>
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

    <main id="main" class="admin-main flex-grow-1">
        @if ($heading ?? $title)
            <div class="admin-page-head">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                        @foreach ($crumbs as $label => $url)
                            <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                        @endforeach
                        <li class="breadcrumb-item active" aria-current="page">{{ $heading ?? $title }}</li>
                    </ol>
                </nav>
                <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
                    <div>
                        <h1 class="page-title">{{ $heading ?? $title }}</h1>
                        @if ($subtitle)<p class="admin-subtitle mt-1">{{ $subtitle }}</p>@endif
                    </div>
                    @isset($actions)
                        <div class="d-flex flex-wrap gap-2 align-items-center">{{ $actions }}</div>
                    @endisset
                </div>
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

<footer class="admin-footer px-3 px-lg-4 py-3 d-flex justify-content-between">
    <span>{{ setting('store_name') }} admin</span>
    <span>&copy; {{ date('Y') }} {{ setting('store_name') }}</span>
</footer>

@include('partials.scripts')
@stack('scripts')
</body>
</html>
