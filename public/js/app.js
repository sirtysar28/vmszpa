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

        // ============================================================
        // Dropdown topbar: notifikasi & menu profil
        // ============================================================
        var notifBtn = document.getElementById('notifBtn');
        var notifPanel = document.getElementById('notifPanel');
        var userBtn = document.getElementById('userMenuBtn');
        var userPanel = document.getElementById('userMenuPanel');

        function closeDropdowns(except) {
            [ [notifBtn, notifPanel], [userBtn, userPanel] ].forEach(function (pair) {
                if (pair[0] && pair[1] && pair[1] !== except) {
                    pair[1].hidden = true;
                    pair[0].setAttribute('aria-expanded', 'false');
                }
            });
        }

        function wireToggle(btn, panel) {
            if (!btn || !panel) return;
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var open = !panel.hidden;
                closeDropdowns(panel);
                panel.hidden = open;
                btn.setAttribute('aria-expanded', String(!open));
            });
            panel.addEventListener('click', function (e) { e.stopPropagation(); });
        }

        wireToggle(notifBtn, notifPanel);
        wireToggle(userBtn, userPanel);

        document.addEventListener('click', function () { closeDropdowns(null); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDropdowns(null);
        });

        // ============================================================
        // Notifikasi aktivitas check-in / check-out (lonceng)
        // ============================================================
        var notifBadge = document.getElementById('notifBadge');
        var notifList = document.getElementById('notifList');
        var notifReadBtn = document.getElementById('notifReadBtn');

        function esc(s) {
            var d = document.createElement('div');
            d.textContent = s == null ? '' : String(s);
            return d.innerHTML;
        }

        function renderNotif(data) {
            if (!notifList) return;

            if (!data || !data.items || !data.items.length) {
                notifList.innerHTML = '<p class="notif-empty">Belum ada aktivitas check-in / check-out.</p>';
            } else {
                var rows = data.items.map(function (it) {
                    var cls = it.type === 'checkin' ? 'in' : 'out';
                    var ico = it.type === 'checkin'
                        ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>'
                        : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>';
                    return '<div class="notif-item">' +
                        '<span class="notif-ico ' + cls + '">' + ico + '</span>' +
                        '<div class="notif-body">' +
                            '<b>' + esc(it.title) + '</b>' +
                            '<small>' + esc(it.dept) + ' &middot; ' + esc(it.code) + '</small>' +
                            '<small class="ago">' + esc(it.ago) + '</small>' +
                        '</div>' +
                    '</div>';
                }).join('');
                notifList.innerHTML = rows;
            }

            var unread = data ? (data.unread || 0) : 0;
            if (notifBadge) {
                if (unread > 0) {
                    notifBadge.textContent = unread > 99 ? '99+' : String(unread);
                    notifBadge.hidden = false;
                } else {
                    notifBadge.hidden = true;
                }
            }
        }

        function loadNotif() {
            if (!notifList) return;
            fetch('/notifikasi/data', { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.text(); })
                .then(function (text) {
                    // Robust: abaikan notice/warning PHP yang mungkin ikut tercetak sebelum JSON
                    var start = text.indexOf('{');
                    if (start < 0) return null;
                    try { return JSON.parse(text.slice(start)); } catch (e) { return null; }
                })
                .then(renderNotif)
                .catch(function () {});
        }

        if (notifReadBtn) {
            notifReadBtn.addEventListener('click', function () {
                fetch('/notifikasi/baca', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken() } })
                    .then(function () {
                        if (notifBadge) notifBadge.hidden = true;
                    })
                    .catch(function () {});
            });
        }

        function csrfToken() {
            var m = document.querySelector('meta[name="csrf-token"]');
            if (m) return m.content;
            var i = document.querySelector('input[name="_token"]');
            return i ? i.value : '';
        }

        if (notifList) {
            loadNotif();
            setInterval(loadNotif, 30000); // refresh tiap 30 detik
        }

        // ============================================================
        // PWA — registrasi service worker (tanpa tombol install;
        // install tetap bisa lewat menu browser "Add to Home Screen")
        // ============================================================
        if ('serviceWorker' in navigator &&
            (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js').catch(function () {});
            });
        }
    });
})();
