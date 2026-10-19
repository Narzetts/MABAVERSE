(function () {
    'use strict';

    var form = document.getElementById('absen-form');
    if (!form) return;

    var video = document.getElementById('camera-video');
    var frame = document.getElementById('camera-frame');
    var btnCapture = document.getElementById('btn-capture');
    var btnRetake = document.getElementById('btn-retake');
    var fileInput = document.getElementById('foto-input');
    var fallbackBox = document.getElementById('camera-fallback');
    var cameraNote = document.getElementById('camera-note');
    var preview = document.getElementById('camera-preview');
    var latInput = document.getElementById('lat-input');
    var lngInput = document.getElementById('lng-input');
    var gpsStatus = document.getElementById('gps-status');
    var gpsDetail = document.getElementById('gps-detail');
    var manualBox = document.getElementById('manual-loc');
    var latManual = document.getElementById('lat-manual');
    var lngManual = document.getElementById('lng-manual');
    var btnManual = document.getElementById('btn-manual');
    var submitBtn = document.getElementById('btn-submit-absen');
    var clock = document.getElementById('device-clock');
    var stream = null;
    var fotoSiap = false;
    var gpsSiap = false;

    // --- Jam perangkat (tampilan saja; waktu sah dari server) ---------------
    if (clock) {
        var tick = function () {
            var now = new Date();
            clock.textContent = now.toLocaleTimeString('id-ID', { hour12: false });
        };
        tick();
        setInterval(tick, 1000);
    }

    // --- Kamera ---------------------------------------------------------------
    var startCamera = function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            pakaiFallback('Browser ini tidak mendukung kamera langsung. Gunakan tombol kamera di bawah.');
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 } },
            audio: false
        }).then(function (s) {
            stream = s;
            video.srcObject = s;
            if (video) video.style.display = '';
            if (frame) frame.hidden = false;
            if (btnCapture) btnCapture.hidden = false;
            if (fallbackBox) fallbackBox.hidden = true;
            if (cameraNote) cameraNote.textContent = 'Kamera siap. Arahkan ke wajah Anda, lalu ambil foto.';
        }).catch(function () {
            pakaiFallback('Izin kamera ditolak atau kamera tidak tersedia. Gunakan tombol kamera perangkat di bawah.');
        });
    };

    var pakaiFallback = function (pesan) {
        if (cameraNote) cameraNote.textContent = pesan;
        if (frame) frame.hidden = true;
        if (btnCapture) btnCapture.hidden = true;
        if (fallbackBox) fallbackBox.hidden = false;
        if (fileInput) {
            fileInput.hidden = false;
            fileInput.addEventListener('change', function () {
                if (fileInput.files.length > 0) {
                    fotoSiap = true;
                    tampilkanPratinjau(fileInput.files[0]);
                }
            });
        }
    };

    var tampilkanPratinjau = function (blob) {
        if (!preview) return;
        if (preview.dataset.url) {
            URL.revokeObjectURL(preview.dataset.url);
        }
        var url = URL.createObjectURL(blob);
        preview.dataset.url = url;
        preview.src = url;
        preview.style.display = '';
        if (video) video.style.display = 'none';
        if (btnRetake) btnRetake.hidden = false;
        if (btnCapture) btnCapture.hidden = true;
        if (cameraNote) cameraNote.textContent = 'Foto terambil. Periksa dulu, ambil ulang bila kurang jelas.';
        perbaruiTombol();
    };

    if (btnCapture) {
        btnCapture.addEventListener('click', function () {
            if (!stream || !video.videoWidth) return;

            var skala = Math.min(1, 1280 / Math.max(video.videoWidth, video.videoHeight));
            var canvas = document.createElement('canvas');
            canvas.width = Math.round(video.videoWidth * skala);
            canvas.height = Math.round(video.videoHeight * skala);
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function (blob) {
                if (!blob) return;
                var file = new File([blob], 'absensi.jpg', { type: 'image/jpeg' });
                var dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
                fotoSiap = true;
                tampilkanPratinjau(blob);
                hentikanKamera();
            }, 'image/jpeg', 0.82);
        });
    }

    if (btnRetake) {
        btnRetake.addEventListener('click', function () {
            fotoSiap = false;
            if (preview) {
                preview.style.display = 'none';
                if (preview.dataset.url) {
                    URL.revokeObjectURL(preview.dataset.url);
                    delete preview.dataset.url;
                }
            }
            if (btnRetake) btnRetake.hidden = true;
            startCamera();
            perbaruiTombol();
        });
    }

    var hentikanKamera = function () {
        if (stream) {
            stream.getTracks().forEach(function (t) { t.stop(); });
            stream = null;
        }
    };

    // --- Geolocation -----------------------------------------------------------
    var siapkanGps = function () {
        if (!navigator.geolocation) {
            bukaManual('Perangkat tidak mendukung geolocation.');
            return;
        }

        gpsStatus.textContent = 'Menunggu pembacaan GPS...';

        navigator.geolocation.getCurrentPosition(function (pos) {
            latInput.value = pos.coords.latitude.toFixed(8);
            lngInput.value = pos.coords.longitude.toFixed(8);
            gpsSiap = true;
            gpsStatus.textContent = 'Lokasi terbaca.';
            gpsDetail.textContent = 'Akurasi sekitar ' + Math.round(pos.coords.accuracy) + ' meter. Titik: ' +
                Number(latInput.value).toFixed(6) + ', ' + Number(lngInput.value).toFixed(6);
            manualBox.classList.remove('is-open');
            perbaruiTombol();
        }, function (err) {
            var pesan = 'Izin lokasi ditolak atau lokasi gagal dibaca.';
            if (err && err.code === 3) {
                pesan = 'Pembacaan lokasi melebihi batas waktu. Coba lagi atau isi manual.';
            }
            bukaManual(pesan);
        }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 });
    };

    var bukaManual = function (pesan) {
        gpsStatus.textContent = pesan;
        gpsDetail.textContent = '';
        manualBox.classList.add('is-open');
    };

    if (btnManual) {
        btnManual.addEventListener('click', function () {
            var lat = latManual.value.trim();
            var lng = lngManual.value.trim();
            if (lat === '' || lng === '' || isNaN(lat) || isNaN(lng)) {
                gpsDetail.textContent = 'Isi kedua kolom koordinat dengan angka yang valid.';
                return;
            }
            latInput.value = Number(lat).toFixed(8);
            lngInput.value = Number(lng).toFixed(8);
            gpsSiap = true;
            gpsStatus.textContent = 'Koordinat manual tersimpan. Absensi ini akan ditinjau panitia.';
            gpsDetail.textContent = 'Titik: ' + Number(latInput.value).toFixed(6) + ', ' + Number(lngInput.value).toFixed(6);
            perbaruiTombol();
        });
    }

    // --- Tombol submit ----------------------------------------------------------
    var perbaruiTombol = function () {
        if (!submitBtn) return;
        submitBtn.disabled = !(fotoSiap && gpsSiap);
    };

    if (submitBtn) {
        submitBtn.addEventListener('click', function (ev) {
            if (!fotoSiap) {
                ev.preventDefault();
                if (cameraNote) cameraNote.textContent = 'Ambil foto absensi dulu sebelum mengirim.';
                return;
            }
            if (!gpsSiap) {
                ev.preventDefault();
                gpsStatus.textContent = 'Tunggu lokasi terbaca, atau isi koordinat manual.';
                manualBox.classList.add('is-open');
                return;
            }
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim...';
        });
    }

    // Kembalikan tombol bila halaman di-cache saat kembali
    window.addEventListener('pageshow', function () {
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.textContent = 'Kirim absensi';
        }
    });

    startCamera();
    siapkanGps();
    perbaruiTombol();
})();
