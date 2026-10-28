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
})();
