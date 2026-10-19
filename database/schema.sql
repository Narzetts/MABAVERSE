-- MABAVERSE Database Schema
-- Created: 2026-10-18

CREATE DATABASE IF NOT EXISTS mabaverse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mabaverse;

-- Fakultas
CREATE TABLE fakultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Jurusan
CREATE TABLE jurusan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fakultas_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fakultas_id) REFERENCES fakultas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Kelompok Ospek
CREATE TABLE kelompok (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    panitia_id INT DEFAULT NULL,
    jurusan_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jurusan_id) REFERENCES jurusan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(50) UNIQUE NOT NULL,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('maba', 'panitia', 'admin', 'superadmin') DEFAULT 'maba',
    fakultas_id INT DEFAULT NULL,
    jurusan_id INT DEFAULT NULL,
    kelompok_id INT DEFAULT NULL,
    foto_profil VARCHAR(255) DEFAULT NULL,
    no_kontak_darurat VARCHAR(20) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fakultas_id) REFERENCES fakultas(id) ON DELETE SET NULL,
    FOREIGN KEY (jurusan_id) REFERENCES jurusan(id) ON DELETE SET NULL,
    FOREIGN KEY (kelompok_id) REFERENCES kelompok(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tahap Ospek (Timeline)
CREATE TABLE tahap_ospek (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    urutan INT NOT NULL,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME NOT NULL,
    target_level ENUM('universitas', 'fakultas', 'jurusan') DEFAULT 'universitas',
    lokasi_text VARCHAR(255) DEFAULT NULL,
    lampiran VARCHAR(255) DEFAULT NULL,
    status ENUM('belum_mulai', 'berlangsung', 'selesai') DEFAULT 'belum_mulai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sesi Ospek
CREATE TABLE sesi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tahap_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    tanggal DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    lokasi_text VARCHAR(255) NOT NULL,
    lokasi_lat DECIMAL(10, 8) DEFAULT NULL,
    lokasi_lng DECIMAL(11, 8) DEFAULT NULL,
    radius_meter INT DEFAULT 150,
    pic VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tahap_id) REFERENCES tahap_ospek(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tugas
CREATE TABLE tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tahap_id INT NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    format_diizinkan VARCHAR(255) DEFAULT 'pdf,jpg,png,doc,docx',
    ukuran_max_mb INT DEFAULT 10,
    deadline DATETIME NOT NULL,
    target_fakultas_id INT DEFAULT NULL,
    target_jurusan_id INT DEFAULT NULL,
    target_kelompok_id INT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tahap_id) REFERENCES tahap_ospek(id) ON DELETE CASCADE,
    FOREIGN KEY (target_fakultas_id) REFERENCES fakultas(id) ON DELETE SET NULL,
    FOREIGN KEY (target_jurusan_id) REFERENCES jurusan(id) ON DELETE SET NULL,
    FOREIGN KEY (target_kelompok_id) REFERENCES kelompok(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Pengumpulan Tugas
CREATE TABLE pengumpulan_tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tugas_id INT NOT NULL,
    user_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    waktu_submit_server TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('terkumpul', 'terlambat', 'revisi', 'diterima') DEFAULT 'terkumpul',
    catatan_panitia TEXT DEFAULT NULL,
    diverifikasi_oleh INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Absensi
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesi_id INT NOT NULL,
    user_id INT NOT NULL,
    foto_path VARCHAR(255) NOT NULL,
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    jarak_dari_lokasi_meter DECIMAL(10, 2) DEFAULT NULL,
    waktu_submit_server TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('hadir', 'di_luar_radius', 'terlambat', 'tidak_hadir', 'izin') DEFAULT 'hadir',
    diverifikasi_oleh INT DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sesi_id) REFERENCES sesi(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_absensi (sesi_id, user_id)
) ENGINE=InnoDB;

-- Pengumuman
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    prioritas ENUM('normal', 'penting', 'mendesak') DEFAULT 'normal',
    target_level ENUM('semua', 'fakultas', 'jurusan', 'kelompok') DEFAULT 'semua',
    target_id INT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Pengumuman Dibaca
CREATE TABLE pengumuman_dibaca (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pengumuman_id INT NOT NULL,
    user_id INT NOT NULL,
    dibaca_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengumuman_id) REFERENCES pengumuman(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_read (pengumuman_id, user_id)
) ENGINE=InnoDB;

-- Pengesahan
CREATE TABLE pengesahan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    status ENUM('disahkan', 'belum_memenuhi') DEFAULT 'belum_memenuhi',
    detail_kekurangan TEXT DEFAULT NULL,
    diproses_oleh INT DEFAULT NULL,
    diproses_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (diproses_oleh) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Audit Log
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    aksi VARCHAR(255) NOT NULL,
    target_tabel VARCHAR(100) NOT NULL,
    target_id INT NOT NULL,
    data_lama TEXT DEFAULT NULL,
    data_baru TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Password Reset Tokens
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Pengaturan aplikasi (key-value), mis. ambang minimal kehadiran pengesahan
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE,
    nilai VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Indexes for performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_nim ON users(nim);
CREATE INDEX idx_tahap_tanggal ON tahap_ospek(tanggal_mulai, tanggal_selesai);
CREATE INDEX idx_sesi_tanggal ON sesi(tanggal);
CREATE INDEX idx_tugas_deadline ON tugas(deadline);
CREATE INDEX idx_absensi_waktu ON absensi(waktu_submit_server);
CREATE INDEX idx_pengumuman_created ON pengumuman(created_at);
CREATE INDEX idx_audit_created ON audit_log(created_at);
