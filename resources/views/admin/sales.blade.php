<x-layouts.admin title="Sales">
    <div class="panel panel-body" style="max-width: 52rem">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
            <div>
                <h2 class="h6 fw-extrabold mb-0">Last 30 days</h2>
            </div>
            <dl class="d-flex gap-4 mb-0 small">
                <div><dt class="fw-semibold text-muted-2">Revenue</dt><dd class="mb-0 fw-extrabold fs-6 tabular" style="color: var(--ms-heading)">{{ money($revenue) }}</dd></div>
                <div><dt class="fw-semibold text-muted-2">Paid orders</dt><dd class="mb-0 fw-extrabold fs-6 tabular" style="color: var(--ms-heading)">{{ number_format($orderCount) }}</dd></div>
                <div><dt class="fw-semibold text-muted-2">Average order</dt><dd class="mb-0 fw-extrabold fs-6 tabular" style="color: var(--ms-heading)">{{ money($average) }}</dd></div>
            </dl>
        </div>
        @include('admin._sales-chart', ['series' => $series])
    </div>
</x-layouts.admin>
