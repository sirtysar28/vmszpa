// ============================================================
// VMS ZPA — Script kiosk tablet (jam real-time)
// ============================================================
(function () {
    'use strict';

    var bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    var hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

    function tick() {
        var d = new Date();
        var p = function (n) { return n < 10 ? '0' + n : '' + n; };
        var elJam = document.getElementById('kiosk-clock');
        var elTgl = document.getElementById('kiosk-date');
        if (elJam) elJam.textContent = p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
        if (elTgl) elTgl.textContent = hari[d.getDay()] + ', ' + d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
    }

    tick();
    setInterval(tick, 1000);

    // ============================================================
    // Menu operator kiosk — klik nama untuk menampilkan opsi keluar
    // (pakai klik, bukan hover, supaya bisa dipakai di iPad)
    // ============================================================
    var userToggle = document.getElementById('kiosk-user-toggle');
    var userMenu = document.getElementById('kiosk-user-menu');

    function tutupMenuOperator() {
        if (!userMenu) { return; }
        userMenu.hidden = true;
        if (userToggle) { userToggle.setAttribute('aria-expanded', 'false'); }
    }

    if (userToggle && userMenu) {
        userToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var buka = userMenu.hidden;
            userMenu.hidden = !buka;
            userToggle.setAttribute('aria-expanded', buka ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!userMenu.hidden && !userMenu.contains(e.target)) { tutupMenuOperator(); }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { tutupMenuOperator(); }
        });
    }

    // ============================================================
    // Kolom "Dari Perusahaan" (registrasi tamu) — otomatis huruf kapital
    // ============================================================
    var companyInput = document.getElementById('company');
    if (companyInput) {
        companyInput.addEventListener('input', function () {
            var awal = this.selectionStart;
            var akhir = this.selectionEnd;
            var kapital = this.value.toUpperCase();
            if (kapital !== this.value) {
                this.value = kapital;
                this.setSelectionRange(awal, akhir); // jaga posisi kursor
            }
        });
    }

    // ============================================================
    // Kolom "Nomor Kendaraan" (check-in) — otomatis huruf kapital
    // ============================================================
    var vehicleInput = document.getElementById('vehicle_number');
    if (vehicleInput) {
        vehicleInput.addEventListener('input', function () {
            var awal = this.selectionStart;
            var akhir = this.selectionEnd;
            var kapital = this.value.toUpperCase();
            if (kapital !== this.value) {
                this.value = kapital;
                this.setSelectionRange(awal, akhir); // jaga posisi kursor
            }
        });
    }
})();
