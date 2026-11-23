-- Buat database
CREATE DATABASE IF NOT EXISTS simkam;
USE simkam;

-- Tabel pengguna
CREATE TABLE users (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE,
  password VARCHAR(50),
  nama     VARCHAR(100),
  role     ENUM('admin','dosen','mahasiswa')
);

-- Tabel mahasiswa
CREATE TABLE mahasiswa (
  nim     VARCHAR(15) PRIMARY KEY,
  id_user INT,
  ipk     DECIMAL(3,2) DEFAULT 0
);

-- Tabel dosen
CREATE TABLE dosen (
  nidn    VARCHAR(15) PRIMARY KEY,
  id_user INT
);

-- Tabel mata kuliah
CREATE TABLE matakuliah (
  kode VARCHAR(10) PRIMARY KEY,
  nama VARCHAR(100),
  sks  INT
);

-- Tabel kelas (jadwal buka MK)
CREATE TABLE kelas (
  id_kelas     INT AUTO_INCREMENT PRIMARY KEY,
  kode_mk      VARCHAR(10),
  nidn         VARCHAR(15),
  tahun_ajaran VARCHAR(9),
  semester     VARCHAR(10)
);

-- Tabel KRS (transaksi utama)
CREATE TABLE krs (
  id_krs   INT AUTO_INCREMENT PRIMARY KEY,
  nim      VARCHAR(15),
  id_kelas INT,
  status   ENUM('DIAJUKAN','DISETUJUI','DITOLAK') DEFAULT 'DIAJUKAN',
  UNIQUE KEY cegah_dobel (nim, id_kelas)
);

-- Tabel nilai
CREATE TABLE nilai (
  id_nilai INT AUTO_INCREMENT PRIMARY KEY,
  id_krs   INT UNIQUE,
  angka    DECIMAL(5,2),
  huruf    CHAR(2),
  bobot    DECIMAL(3,2),
  publish  TINYINT(1) DEFAULT 0
);

-- ============ DATA CONTOH ============
INSERT INTO users VALUES
(1,'admin','123','Admin BAAK','admin'),
(2,'dosen1','123','Pak Budi','dosen'),
(3,'mhs1','123','Andi Wijaya','mahasiswa');

INSERT INTO dosen     VALUES ('D001', 2);
INSERT INTO mahasiswa VALUES ('2021001', 3, 0.00);

INSERT INTO matakuliah VALUES
('IF101','Pemrograman Web',3),
('IF102','Basis Data',3);

INSERT INTO kelas (kode_mk, nidn, tahun_ajaran, semester) VALUES
('IF101','D001','2026/2027','GANJIL'),
('IF102','D001','2026/2027','GANJIL');