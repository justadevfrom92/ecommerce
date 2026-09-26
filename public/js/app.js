/* MyStore — site behaviour. Plain JS, no build step; Bootstrap's bundle is loaded before this file. */
(function () {
    'use strict';

    /* ---------- Product sliders ---------- */
    function initSlider(slider) {
        var track = slider.querySelector('[data-slider-track]');
        var prev = slider.querySelector('[data-slider-prev]');
        var next = slider.querySelector('[data-slider-next]');
        if (!track || !prev || !next) return;

        function update() {
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
