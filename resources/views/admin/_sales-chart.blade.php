{{-- Compact 30-day revenue line (single series, inline SVG, hover crosshair + tooltip, table fallback). Expects $series. --}}
@php
    $w = 800; $h = 180; $pl = 52; $pr = 12; $pt = 10; $pb = 26;
    $max = max(1, $series->max('value'));
    $mag = 10 ** floor(log10($max));
    $step = collect([1, 2, 2.5, 5, 10])->map(fn ($m) => $m * $mag)->first(fn ($s) => $max / $s <= 4) ?? $mag * 10;
    $top = ceil($max / $step) * $step;
    $n = $series->count();
    $x = fn ($i) => $pl + ($w - $pl - $pr) * ($n > 1 ? $i / ($n - 1) : 0);
    $y = fn ($v) => $pt + ($h - $pt - $pb) * (1 - $v / $top);
    $points = $series->values()->map(fn ($p, $i) => round($x($i), 1).','.round($y($p['value']), 1))->join(' ');
    $area = 'M'.round($x(0), 1).','.($h - $pb).' L'.str_replace(' ', ' L', $points).' L'.round($x($n - 1), 1).','.($h - $pb).' Z';
    $sym = setting('currency_symbol');
@endphp
<div class="position-relative" data-line-chart>
    <svg class="chart-svg" viewBox="0 0 {{ $w }} {{ $h }}" role="img" aria-label="Revenue per day for the last 30 days">
        @for ($t = 0; $t <= $top + 0.0001; $t += $step)
            <line class="grid" x1="{{ $pl }}" x2="{{ $w - $pr }}" y1="{{ round($y($t), 1) }}" y2="{{ round($y($t), 1) }}"></line>
            <text class="axis-label" x="{{ $pl - 8 }}" y="{{ round($y($t), 1) + 4 }}" text-anchor="end">{{ $sym }}{{ $t >= 1000 ? rtrim(rtrim(number_format($t / 1000, 1), '0'), '.').'k' : number_format($t) }}</text>
        @endfor
        @foreach ([0, intdiv($n - 1, 2), $n - 1] as $i)
            <text class="axis-label" x="{{ round($x($i), 1) }}" y="{{ $h - 8 }}" text-anchor="{{ $i === 0 ? 'start' : ($i === $n - 1 ? 'end' : 'middle') }}">{{ $series[$i]['date']->format('M j') }}</text>
        @endforeach
        <path class="area" d="{{ $area }}"></path>
        <polyline class="line" points="{{ $points }}"></polyline>
        <circle class="dot" cx="{{ round($x($n - 1), 1) }}" cy="{{ round($y($series->last()['value']), 1) }}" r="4"></circle>
        <line class="crosshair" x1="0" x2="0" y1="{{ $pt }}" y2="{{ $h - $pb }}" stroke="var(--ms-text-2)" stroke-width="1" stroke-dasharray="3 3" visibility="hidden"></line>
        <circle class="hover-dot dot" r="5" cx="0" cy="0" visibility="hidden"></circle>
        @foreach ($series->values() as $i => $p)
            <rect x="{{ round($x($i) - ($w - $pl - $pr) / ($n - 1) / 2, 1) }}" y="{{ $pt }}" width="{{ round(($w - $pl - $pr) / ($n - 1), 1) }}" height="{{ $h - $pt - $pb }}"
                  fill="transparent" tabindex="0" data-x="{{ round($x($i), 1) }}" data-y="{{ round($y($p['value']), 1) }}"
                  data-label="{{ $p['date']->format('D, M j') }}" data-value="{{ money($p['value']) }}"
                  aria-label="{{ $p['date']->format('M j') }}: {{ money($p['value']) }}"></rect>
        @endforeach
    </svg>
    <div class="chart-tip panel px-2 py-1 small shadow-sm" hidden style="position:absolute;pointer-events:none;white-space:nowrap">
        <div class="fw-extrabold tip-value" style="color: var(--ms-heading)"></div>
        <div class="text-muted-2 tip-label"></div>
    </div>
</div>
<details class="mt-2 small">
    <summary class="text-muted-2 fw-semibold">Show as table</summary>
    <div class="table-responsive mt-2" style="max-height: 16rem">
        <table class="table table-sm">
            <thead><tr><th>Date</th><th class="text-end">Revenue</th></tr></thead>
            <tbody>
                @foreach ($series as $p)
                    <tr><td>{{ $p['date']->format('D, M j') }}</td><td class="text-end tabular">{{ money($p['value']) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</details>
