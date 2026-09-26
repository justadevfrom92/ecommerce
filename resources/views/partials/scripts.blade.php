<script src="{{ config('store.cdn.bootstrap_js.url') }}" @if (config('store.cdn.bootstrap_js.integrity')) integrity="{{ config('store.cdn.bootstrap_js.integrity') }}" @endif crossorigin="anonymous"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
