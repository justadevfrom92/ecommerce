<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ isset($title) && $title ? $title.' · ' : '' }}{{ setting('store_name') }}</title>
<link rel="icon" href="{{ asset('images/logo.svg') }}" type="image/svg+xml">
@foreach (['bootstrap_css', 'bootstrap_icons'] as $asset)
    <link rel="stylesheet" href="{{ config("store.cdn.$asset.url") }}" @if (config("store.cdn.$asset.integrity")) integrity="{{ config("store.cdn.$asset.integrity") }}" @endif crossorigin="anonymous">
@endforeach
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
