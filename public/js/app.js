// ============================================================
// VMS ZPA — Script umum area management & auth
// (eksternal, agar CSP ketat tanpa inline-script)
// ============================================================
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // ---- Intip password (tombol mata) ----
        document.querySelectorAll('.input-eye, .pw-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var sel = btn.dataset.target || 'password';
                var group = btn.closest('.input-group, .form-group');
                var input = document.getElementById(sel) || (group && group.querySelector('input'));
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                var open = btn.querySelector('#eyeOpen, .eye-open');
                var slash = btn.querySelector('#eyeSlash, .eye-slash');
                if (open) open.style.display = show ? 'none' : '';
                if (slash) slash.style.display = show ? '' : 'none';
            });
        });

        // ---- Refresh captcha ----
        var captchaImg = document.getElementById('captchaImage');
        if (captchaImg) {
            captchaImg.addEventListener('click', function () {
                this.src = this.src.split('?')[0] + '?_=' + Date.now();
            });
        }

        // ---- Tombol cetak ----
        document.querySelectorAll('.js-print').forEach(function (btn) {
            btn.addEventListener('click', function () { window.print(); });
        });

        // ---- Konfirmasi submit form ----
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!window.confirm(form.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });

        // ---- Chips tujuan cepat (kiosk check-in) ----
        document.querySelectorAll('.chip[data-purpose]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                var target = document.getElementById(chip.dataset.for || 'purpose');
                if (target) target.value = chip.dataset.purpose;
            });
        });
    });
})();
