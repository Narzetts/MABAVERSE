-- MABAVERSE Seed Data (data contoh untuk demo dan pengujian)
-- Tanggal memakai CURDATE() supaya linimasa selalu segar relatif terhadap hari ini.
-- Password semua akun contoh: admin123

USE mabaverse;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE absensi;
TRUNCATE TABLE pengumpulan_tugas;
TRUNCATE TABLE pengesahan;
TRUNCATE TABLE pengumuman_dibaca;
TRUNCATE TABLE pengumuman;
TRUNCATE TABLE audit_log;
TRUNCATE TABLE password_reset_tokens;
TRUNCATE TABLE pengaturan;
TRUNCATE TABLE tugas;
TRUNCATE TABLE sesi;
TRUNCATE TABLE tahap_ospek;
TRUNCATE TABLE users;
TRUNCATE TABLE kelompok;
TRUNCATE TABLE jurusan;
TRUNCATE TABLE fakultas;
SET FOREIGN_KEY_CHECKS = 1;

-- Fakultas
INSERT INTO fakultas (id, nama) VALUES
(1, 'Fakultas Teknik'),
(2, 'Fakultas Ekonomi dan Bisnis'),
(3, 'Fakultas Kedokteran'),
(4, 'Fakultas Ilmu Sosial dan Politik');

-- Jurusan
INSERT INTO jurusan (id, fakultas_id, nama) VALUES
(1, 1, 'Teknik Informatika'),
(2, 1, 'Teknik Elektro'),
(3, 1, 'Teknik Sipil'),
(4, 2, 'Manajemen'),
(5, 2, 'Akuntansi'),
(6, 3, 'Pendidikan Dokter'),
(7, 4, 'Ilmu Komunikasi'),
(8, 4, 'Administrasi Publik');

-- Akun staf (superadmin, admin, panitia)
INSERT INTO users (id, nim, nama, email, password_hash, role, jurusan_id) VALUES
(1, 'ADMIN001', 'Super Administrator', 'admin@mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'superadmin', NULL),
(2, 'ADM001', 'Admin Ospek', 'admin.ospek@mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'admin', NULL),
(3, 'PAN001', 'Rina Panitia', 'panitia1@mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'panitia', 1),
(4, 'PAN002', 'Yoga Panitia', 'panitia2@mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'panitia', 1);

-- Kelompok ospek (panitia_id dulu kosong, diisi setelah akun panitia ada)
INSERT INTO kelompok (id, nama, jurusan_id, panitia_id) VALUES
(1, 'Kelompok A', 1, 3),
(2, 'Kelompok B', 1, 4),
(3, 'Kelompok C', 4, NULL),
(4, 'Kelompok D', 7, NULL);

-- Mahasiswa baru contoh
INSERT INTO users (id, nim, nama, email, password_hash, role, fakultas_id, jurusan_id, kelompok_id, no_kontak_darurat) VALUES
(5, '2026001', 'Budi Santoso', 'budi@student.mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'maba', 1, 1, 1, '081234567801'),
(6, '2026002', 'Siti Nurhaliza', 'siti@student.mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'maba', 1, 1, 1, '081234567802'),
(7, '2026003', 'Ahmad Rizki', 'ahmad@student.mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'maba', 2, 4, 3, '081234567803'),
(8, '2026004', 'Dewi Lestari', 'dewi@student.mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'maba', 1, 2, 2, NULL),
(9, '2026005', 'Raka Pratama', 'raka@student.mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'maba', 4, 7, 4, NULL),
(10, '2026006', 'Nadia Safitri', 'nadia@student.mabaverse.ac.id', '$2y$10$yciq0cGW0B06ubgvX1GQj.t1BGT0R5afn8xgVqn3YKc91GYWfGbIG', 'maba', 3, 6, NULL, NULL);

-- Tahap ospek (tanggal relatif terhadap hari ini)
INSERT INTO tahap_ospek (id, judul, deskripsi, urutan, tanggal_mulai, tanggal_selesai, target_level, lokasi_text, status) VALUES
(1, 'Pengambilan KTM', 'Pengambilan Kartu Tanda Mahasiswa di gedung rektorat sesuai jadwal per jurusan.', 1, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 9 DAY), '08:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 7 DAY), '16:00:00'), 'universitas', 'Gedung Rektorat', 'selesai'),
(2, 'Pengambilan Almamater', 'Pengambilan almamater dan atribut kampus di gedung serbaguna.', 2, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 7 DAY), '08:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 6 DAY), '16:00:00'), 'universitas', 'Gedung Serbaguna', 'selesai'),
(3, 'Pemberitahuan Jadwal Ospek', 'Sosialisasi rangkaian kegiatan ospek dan pembagian kelompok.', 3, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '09:00:00'), TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '12:00:00'), 'universitas', 'Auditorium Utama', 'selesai'),
(4, 'Ospek Kampus', 'Orientasi tingkat universitas: pengenalan visi misi, materi karakter, dan kehidupan kampus.', 4, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 1 DAY), '17:00:00'), 'universitas', 'Auditorium Utama', 'berlangsung'),
(5, 'Pengumpulan Tugas Ospek Kampus', 'Batas akhir pengumpulan seluruh tugas tingkat universitas.', 5, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '00:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '23:59:00'), 'universitas', NULL, 'belum_mulai'),
(6, 'Ospek Fakultas', 'Orientasi tingkat fakultas: pengenalan dosen, laboratorium, dan tata tertib akademik.', 6, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 4 DAY), '07:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 6 DAY), '17:00:00'), 'fakultas', NULL, 'belum_mulai'),
(7, 'Pengumpulan Tugas Ospek Fakultas', 'Batas akhir pengumpulan tugas tingkat fakultas.', 7, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '00:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 8 DAY), '23:59:00'), 'fakultas', NULL, 'belum_mulai'),
(8, 'Ospek Jurusan', 'Orientasi tingkat jurusan: pengenalan kurikulum dan dosen pembimbing akademik.', 8, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 9 DAY), '07:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 11 DAY), '17:00:00'), 'jurusan', NULL, 'belum_mulai'),
(9, 'Pengumpulan Tugas Ospek Jurusan', 'Batas akhir pengumpulan tugas tingkat jurusan.', 9, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 12 DAY), '00:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 13 DAY), '23:59:00'), 'jurusan', NULL, 'belum_mulai'),
(10, 'Pengesahan Calon Mahasiswa', 'Penetapan status mahasiswa baru setelah seluruh syarat ospek terpenuhi.', 10, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 14 DAY), '08:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 14 DAY), '16:00:00'), 'universitas', NULL, 'belum_mulai'),
(11, 'Awal Perkuliahan', 'Dimulainya kegiatan perkuliahan semester pertama.', 11, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 15 DAY), '07:00:00'), TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 15 DAY), '23:59:00'), 'universitas', NULL, 'belum_mulai');

-- Sesi ospek kampus
INSERT INTO sesi (id, tahap_id, judul, tanggal, jam_mulai, jam_selesai, lokasi_text, lokasi_lat, lokasi_lng, radius_meter, pic) VALUES
(1, 4, 'Pembukaan Ospek Universitas', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:00:00', '09:00:00', 'Auditorium Utama', -6.20000000, 106.81666600, 150, 'Rektor'),
(2, 4, 'Pengenalan Visi Misi Kampus', DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:30:00', '12:00:00', 'Auditorium Utama', -6.20000000, 106.81666600, 150, 'Wakil Rektor I'),
(3, 4, 'Materi Karakter dan Kepemimpinan', CURDATE(), '08:00:00', '11:00:00', 'Gedung Serbaguna', -6.20100000, 106.81700000, 200, 'Tim BEM'),
(4, 4, 'Penutupan dan Refleksi', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:00:00', '10:00:00', 'Lapangan Rektorat', -6.20000000, 106.81666600, 150, 'Panitia Pusat');

-- Tugas
INSERT INTO tugas (id, tahap_id, judul, deskripsi, format_diizinkan, ukuran_max_mb, deadline, target_fakultas_id, target_jurusan_id, target_kelompok_id, created_by) VALUES
(1, 4, 'Essay Visi Pribadi', 'Tulis essay 500 kata tentang visi kamu sebagai mahasiswa dan kontribusi yang ingin diberikan selama kuliah.', 'pdf,doc,docx', 10, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '23:59:00'), NULL, NULL, NULL, 2),
(2, 4, 'Poster Perkenalan Diri', 'Buat poster digital berisi nama, jurusan, dan hal menarik tentang dirimu. Format bebas, wajib memuat foto.', 'jpg,png,pdf', 5, TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL 2 DAY), '23:59:00'), 1, NULL, NULL, 2),
(3, 1, 'Unggah Bukti Pengambilan KTM', 'Foto KTM yang sudah diambil beserta halaman depan kartu, unggah sebelum deadline.', 'jpg,png,pdf', 5, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 5 DAY), '23:59:00'), NULL, NULL, NULL, 2);

-- Pengumpulan tugas
INSERT INTO pengumpulan_tugas (tugas_id, user_id, file_path, waktu_submit_server, status, catatan_panitia, diverifikasi_oleh) VALUES
(3, 5, 'uploads/tugas/2026001-ktm.jpg', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 6 DAY), '15:00:00'), 'diterima', NULL, 2),
(3, 6, 'uploads/tugas/2026002-ktm.jpg', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 6 DAY), '20:30:00'), 'terkumpul', NULL, NULL),
(3, 7, 'uploads/tugas/2026003-ktm.jpg', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 4 DAY), '09:00:00'), 'terlambat', 'Diunggah setelah deadline, mohon konfirmasi ke panitia', 2),
(1, 5, 'uploads/tugas/2026001-essay.pdf', TIMESTAMP(CURDATE(), '06:30:00'), 'terkumpul', NULL, NULL),
(1, 6, 'uploads/tugas/2026002-essay.pdf', TIMESTAMP(CURDATE(), '06:45:00'), 'revisi', 'Sertakan sumber referensi dan rapikan bagian penutup', 2);

-- Absensi sesi 1 (sesi kemarin). Sesi hari ini sengaja kosong untuk uji coba alur absensi.
INSERT INTO absensi (sesi_id, user_id, foto_path, lat, lng, jarak_dari_lokasi_meter, waktu_submit_server, status, diverifikasi_oleh, catatan) VALUES
(1, 5, 'uploads/absensi/2026001-sesi-1.jpg', -6.20040000, 106.81690000, 42.50, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:15:00'), 'hadir', NULL, NULL),
(1, 6, 'uploads/absensi/2026002-sesi-1.jpg', -6.20700000, 106.82200000, 812.40, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:22:00'), 'di_luar_radius', 3, 'Lokasi terdeteksi jauh dari titik sesi, minta konfirmasi mahasiswa'),
(1, 7, 'uploads/absensi/2026003-sesi-1.jpg', -6.20050000, 106.81680000, 55.10, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '09:20:00'), 'terlambat', NULL, NULL),
(1, 8, 'uploads/absensi/2026004-sesi-1.jpg', -6.20010000, 106.81670000, 11.80, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '06:58:00'), 'izin', 3, 'Izin sakit, surat keterangan menyusul');

-- Pengumuman
INSERT INTO pengumuman (id, judul, isi, prioritas, target_level, target_id, created_by) VALUES
(1, 'Wajib Hadir Tepat Waktu', 'Seluruh mahasiswa baru wajib hadir tepat waktu pada setiap sesi ospek. Keterlambatan dicatat dan diperhitungkan dalam rekap kehadiran.', 'penting', 'semua', NULL, 2),
(2, 'Perubahan Lokasi Sesi Hari Kedua', 'Lokasi sesi Materi Karakter dan Kepemimpinan dipindah dari Auditorium Utama ke Gedung Serbaguna. Jam tetap sama.', 'mendesak', 'semua', NULL, 2),
(3, 'Pembagian Seragam Kelompok A', 'Seragam kelompok A dibagikan setelah sesi penutupan di pos panitia. Bawa kartu identitas untuk pengambilan.', 'normal', 'kelompok', 1, 3);

-- Status baca pengumuman
INSERT INTO pengumuman_dibaca (pengumuman_id, user_id) VALUES
(1, 5);

-- Pengesahan
INSERT INTO pengesahan (user_id, status, detail_kekurangan, diproses_oleh, diproses_at) VALUES
(5, 'belum_memenuhi', 'Tugas Ospek Kampus: 1 tugas belum dikumpulkan; Kehadiran: 67% (minimal 75%)', 2, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:00:00')),
(6, 'disahkan', NULL, 2, TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:05:00'));

-- Pengaturan aplikasi
INSERT INTO pengaturan (nama, nilai) VALUES
('min_kehadiran_pengesahan', '75');

-- Audit log contoh (nama aksi mengikuti yang ditulis model: LOGIN, UPDATE_STATUS_ABSENSI, PROSES_PENGESAHAN)
INSERT INTO audit_log (user_id, aksi, target_tabel, target_id, data_lama, data_baru, ip_address, created_at) VALUES
(2, 'LOGIN', 'users', 2, NULL, NULL, '127.0.0.1', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 2 DAY), '07:01:00')),
(3, 'UPDATE_STATUS_ABSENSI', 'absensi', 2, '{"status":"terlambat"}', '{"status":"di_luar_radius","catatan":"Lokasi terdeteksi jauh dari titik sesi, minta konfirmasi mahasiswa"}', '127.0.0.1', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '10:12:00')),
(2, 'PROSES_PENGESAHAN', 'pengesahan', 2, NULL, '{"status":"disahkan","override":"tidak"}', '127.0.0.1', TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 1 DAY), '16:05:00'));
