{!! $body !!}
@if ($buttonText && $buttonUrl)

{{ $buttonText }}: {!! $buttonUrl !!}
@endif

--
{{ setting('store_name') }}
@if ($unsubscribeUrl)
Unsubscribe: {!! $unsubscribeUrl !!}
@endif
