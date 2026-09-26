{{-- Shows a rendered email in a sandboxed iframe. Pass $subject and $html (mailables are Renderable, so views would stringify the object). --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-body py-3">
        <h2 class="h6 mb-0">Preview</h2>
        <div class="small text-body-secondary text-truncate">Subject: {{ $subject }}</div>
    </div>
    <iframe title="Email preview" sandbox srcdoc="{{ $html }}" style="width:100%;height:560px;border:0;border-radius:0 0 .5rem .5rem;"></iframe>
</div>
