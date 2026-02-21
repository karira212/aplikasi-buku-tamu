CREATE DATABASE IF NOT EXISTS tamu_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tamu_db;

DROP TABLE IF EXISTS tamu;
DROP TABLE IF EXISTS kegiatan;
DROP TABLE IF EXISTS instansi;
DROP TABLE IF EXISTS app_settings;
DROP TABLE IF EXISTS admin_users;

CREATE TABLE kegiatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE instansi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE app_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE tamu (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_lengkap VARCHAR(255) NOT NULL,
  no_hp VARCHAR(30) NOT NULL,
  jabatan VARCHAR(255) NULL,
  alamat TEXT NULL,
  kegiatan_id INT NOT NULL,
  kegiatan_lainnya VARCHAR(255) DEFAULT NULL,
  instansi_id INT NOT NULL DEFAULT 0,
  instansi_lainnya VARCHAR(255) DEFAULT NULL,
  foto_path VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_kegiatan (kegiatan_id),
  INDEX idx_instansi (instansi_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO kegiatan (nama) VALUES
('Rapat Rutin'),
('Pelatihan'),
('Sosialisasi'),
('Kunjungan Kerja');

INSERT INTO instansi (nama) VALUES
('Kelurahan Contoh'),
('Puskesmas Contoh'),
('RW 01'),
('RW 02');

INSERT INTO app_settings (name, value) VALUES
('guestbook_open', '1');
