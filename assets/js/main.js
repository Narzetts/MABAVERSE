(function () {
    'use strict';

    // --- Tema terang/gelap --------------------------------------------------
    var toggle = document.getElementById('theme-toggle');
    if (toggle) {
        var root = document.documentElement;

        var update = function () {
            var dark = root.getAttribute('data-theme') === 'dark';
            toggle.textContent = dark ? 'Mode terang' : 'Mode gelap';
            toggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
        };

        update();

        toggle.addEventListener('click', function () {
            var dark = root.getAttribute('data-theme') === 'dark';
            if (dark) {
                root.removeAttribute('data-theme');
            } else {
                root.setAttribute('data-theme', 'dark');
            }
            try {
                localStorage.setItem('mabaverse-theme', dark ? 'light' : 'dark');
            } catch (err) {}
            update();
        });
    }

    // --- Konfirmasi aksi yang tidak bisa dibatalkan --------------------------
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                ev.preventDefault();
            }
        });
    });

    // --- Isi koordinat dari geolocation (form admin, sesi) -------------------
    document.querySelectorAll('[data-geolocate]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var latInput = document.querySelector(btn.getAttribute('data-lat'));
            var lngInput = document.querySelector(btn.getAttribute('data-lng'));
            var status = btn.getAttribute('data-status')
                ? document.querySelector(btn.getAttribute('data-status'))
                : null;

            if (!latInput || !lngInput || !navigator.geolocation) {
                if (status) status.textContent = 'Perangkat tidak mendukung geolocation.';
                return;
            }

            if (status) status.textContent = 'Mengambil lokasi...';

            navigator.geolocation.getCurrentPosition(function (pos) {
                latInput.value = pos.coords.latitude.toFixed(8);
                lngInput.value = pos.coords.longitude.toFixed(8);
                if (status) status.textContent = 'Koordinat terisi dari lokasi perangkat saat ini.';
            }, function () {
                if (status) status.textContent = 'Gagal mengambil lokasi. Isi koordinat manual.';
            }, { enableHighAccuracy: true, timeout: 15000 });
        });
    });

    // --- Filter daftar (pengumuman) -------------------------------------------
    var filterInput = document.querySelector('[data-filter-input]');
    if (filterInput) {
        var listSelector = filterInput.getAttribute('data-filter-input') || '.filter-item';
        var prioritySelect = document.querySelector('[data-filter-priority]');

        var apply = function () {
            var q = filterInput.value.toLowerCase();
            var prio = prioritySelect ? prioritySelect.value : '';
            var terlihat = 0;

            document.querySelectorAll(listSelector).forEach(function (item) {
                var cocokTeks = q === '' || item.textContent.toLowerCase().indexOf(q) !== -1;
                var cocokPrio = prio === '' || item.getAttribute('data-prioritas') === prio;
                var tampil = cocokTeks && cocokPrio;
                item.style.display = tampil ? '' : 'none';
                if (tampil) terlihat += 1;
            });

            var kosong = document.getElementById('filter-kosong');
            if (kosong) {
                kosong.style.display = terlihat === 0 ? '' : 'none';
            }
        };

        filterInput.addEventListener('input', apply);
        if (prioritySelect) prioritySelect.addEventListener('change', apply);
    }

    // --- Urutkan tahap ospek: drag atau tombol naik/turun ----------------------
    var reorderTable = document.querySelector('[data-reorder]');
    if (reorderTable) {
        var tbody = reorderTable.querySelector('tbody');
        var csrf = reorderTable.getAttribute('data-csrf');
        var endpoint = reorderTable.getAttribute('data-endpoint');
        var statusEl = document.getElementById('reorder-status');
        var dragRow = null;

        var simpan = function () {
            var ids = Array.prototype.map.call(tbody.querySelectorAll('tr[data-id]'), function (tr) {
                return tr.getAttribute('data-id');
            });

            if (statusEl) statusEl.textContent = 'Menyimpan urutan...';

            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: csrf, ids: ids })
            }).then(function (res) {
                return res.json();
            }).then(function (data) {
                if (statusEl) {
                    statusEl.textContent = data.success ? 'Urutan tersimpan.' : (data.error || 'Gagal menyimpan urutan.');
                }
            }).catch(function () {
                if (statusEl) statusEl.textContent = 'Gagal menyimpan urutan (jaringan).';
            });
        };

        tbody.querySelectorAll('tr[data-id]').forEach(function (tr) {
            tr.setAttribute('draggable', 'true');

            tr.addEventListener('dragstart', function () {
                dragRow = tr;
            });

            tr.addEventListener('dragend', function () {
                dragRow = null;
                simpan();
            });

            tr.addEventListener('dragover', function (ev) {
                if (!dragRow || dragRow === tr) return;
                ev.preventDefault();
                var rect = tr.getBoundingClientRect();
                var setelah = (ev.clientY - rect.top) > rect.height / 2;
                tbody.insertBefore(dragRow, setelah ? tr.nextSibling : tr);
            });
        });

        tbody.querySelectorAll('[data-move]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var tr = btn.closest('tr');
                if (btn.getAttribute('data-move') === 'up' && tr.previousElementSibling) {
                    tbody.insertBefore(tr, tr.previousElementSibling);
                } else if (btn.getAttribute('data-move') === 'down' && tr.nextElementSibling) {
                    tbody.insertBefore(tr.nextElementSibling, tr);
                }
                simpan();
            });
        });
    }
})();
