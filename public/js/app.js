/* MyStore — site behaviour. Plain JS, no build step; Bootstrap's bundle is loaded before this file. */
(function () {
    'use strict';

    /* ---------- Product sliders ---------- */
    function initSlider(slider) {
        var track = slider.querySelector('[data-slider-track]');
        var prev = slider.querySelector('[data-slider-prev]');
        var next = slider.querySelector('[data-slider-next]');
        if (!track || !prev || !next) return;

        var viewport = slider.querySelector('.slider-viewport');

        // Centre both arrows on the product images, not on the whole card.
        function placeButtons() {
            var img = track.querySelector('.product-card-image');
            if (img && viewport) {
                var box = img.getBoundingClientRect();
                viewport.style.setProperty('--slider-btn-top', (box.top - viewport.getBoundingClientRect().top + box.height / 2) + 'px');
            }
        }

        function update() {
            placeButtons();
            var max = track.scrollWidth - track.clientWidth - 2;
            prev.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft >= max;
            slider.toggleAttribute('data-slider-static', max <= 0);
        }

        function page(direction) {
            track.scrollBy({ left: direction * track.clientWidth, behavior: 'smooth' });
        }

        prev.addEventListener('click', function () { page(-1); });
        next.addEventListener('click', function () { page(1); });
        track.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowRight') { e.preventDefault(); page(1); }
            if (e.key === 'ArrowLeft') { e.preventDefault(); page(-1); }
        });
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    }

    document.querySelectorAll('[data-slider]').forEach(initSlider);

    /* ---------- Dashboard line chart: crosshair + tooltip ---------- */
    document.querySelectorAll('[data-line-chart]').forEach(function (wrap) {
        var svg = wrap.querySelector('svg'), tip = wrap.querySelector('.chart-tip');
        var cross = svg.querySelector('.crosshair'), dot = svg.querySelector('.hover-dot');
        function show(r) {
            var x = r.getAttribute('data-x'), y = r.getAttribute('data-y');
            cross.setAttribute('x1', x); cross.setAttribute('x2', x); cross.setAttribute('visibility', 'visible');
            dot.setAttribute('cx', x); dot.setAttribute('cy', y); dot.setAttribute('visibility', 'visible');
            tip.querySelector('.tip-value').textContent = r.getAttribute('data-value');
            tip.querySelector('.tip-label').textContent = r.getAttribute('data-label');
            tip.hidden = false;
            var scale = svg.clientWidth / svg.viewBox.baseVal.width;
            var left = x * scale + 12;
            if (left + tip.offsetWidth > wrap.clientWidth) left = x * scale - tip.offsetWidth - 12;
            tip.style.left = left + 'px';
            tip.style.top = Math.max(0, y * scale - tip.offsetHeight - 8) + 'px';
        }
        function hide() { tip.hidden = true; cross.setAttribute('visibility', 'hidden'); dot.setAttribute('visibility', 'hidden'); }
        svg.querySelectorAll('rect[data-x]').forEach(function (r) {
            r.addEventListener('pointerenter', function () { show(r); });
            r.addEventListener('focus', function () { show(r); });
            r.addEventListener('blur', hide);
        });
        svg.addEventListener('pointerleave', hide);
    });

    /* ---------- Quantity steppers (− n +) ---------- */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-qty-step]');
        if (!btn) return;
        var input = btn.closest('[data-qty]').querySelector('input');
        var min = parseInt(input.min || '0', 10), max = parseInt(input.max || '99', 10);
        var next = Math.min(max, Math.max(min, (parseInt(input.value, 10) || 0) + parseInt(btn.getAttribute('data-qty-step'), 10)));
        if (String(next) !== input.value) {
            input.value = next;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    /* ---------- Auto-submit selects (per page, sort, filters, cart qty) ---------- */
    document.addEventListener('change', function (e) {
        if (e.target.matches('[data-auto-submit]') && e.target.form) {
            e.target.form.requestSubmit ? e.target.form.requestSubmit() : e.target.form.submit();
        }
    });

    /* ---------- Confirm before destructive actions ---------- */
    document.addEventListener('submit', function (e) {
        var message = e.target.getAttribute('data-confirm');
        if (message && !window.confirm(message)) e.preventDefault();
    });

    /* ---------- Re-open the login popup when it has errors ---------- */
    var loginToggle = document.querySelector('[data-open-on-load]');
    if (loginToggle && window.bootstrap) {
        window.bootstrap.Dropdown.getOrCreateInstance(loginToggle).show();
    }

    /* ---------- "Select all" checkbox groups (roles form) ---------- */
    document.querySelectorAll('[data-check-group]').forEach(function (master) {
        var group = master.getAttribute('data-check-group');
        var boxes = document.querySelectorAll('[data-check-member="' + group + '"]');
        master.addEventListener('change', function () {
            boxes.forEach(function (b) { b.checked = master.checked; });
        });
    });

    /* ---------- Product image preview on upload ---------- */
    document.querySelectorAll('[data-image-preview]').forEach(function (input) {
        var target = document.getElementById(input.getAttribute('data-image-preview'));
        input.addEventListener('change', function () {
            if (target && input.files && input.files[0]) {
                target.src = URL.createObjectURL(input.files[0]);
            }
        });
    });
})();
