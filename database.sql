CREATE DATABASE IF NOT EXISTS penyewaan_mobil;
USE penyewaan_mobil;

-- 1. Table users
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    nomor_sim VARCHAR(50) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table kategori_mobil
CREATE TABLE IF NOT EXISTS kategori_mobil (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table mobil
CREATE TABLE IF NOT EXISTS mobil (
    id_mobil INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    nama_mobil VARCHAR(100) NOT NULL,
    merk VARCHAR(50) NOT NULL,
    tahun INT NOT NULL,
    plat_nomor VARCHAR(20) NOT NULL UNIQUE,
    harga_sewa_per_hari DECIMAL(12,2) NOT NULL,
    status ENUM('tersedia', 'disewa', 'perbaikan') DEFAULT 'tersedia',
    foto VARCHAR(255) DEFAULT NULL,
    deskripsi TEXT DEFAULT NULL,
    FOREIGN KEY (id_kategori) REFERENCES kategori_mobil(id_kategori) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table penyewaan
CREATE TABLE IF NOT EXISTS penyewaan (
    id_sewa INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_mobil INT NOT NULL,
    tanggal_sewa DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    lama_sewa INT NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL,
    status_sewa ENUM('belum_bayar', 'sudah_bayar', 'diambil', 'kembali', 'batal') DEFAULT 'belum_bayar',
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_mobil) REFERENCES mobil(id_mobil) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Table pembayaran
CREATE TABLE IF NOT EXISTS pembayaran (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_sewa INT NOT NULL,
    metode_pembayaran VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) DEFAULT NULL,
    jumlah_bayar DECIMAL(12,2) NOT NULL,
    status_pembayaran ENUM('pending', 'success', 'expire', 'cancel') DEFAULT 'pending',
    tanggal_pembayaran TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sewa) REFERENCES penyewaan(id_sewa) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Table pengembalian
CREATE TABLE IF NOT EXISTS pengembalian (
    id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_sewa INT NOT NULL,
    tanggal_pengembalian DATE NOT NULL,
    kondisi_mobil VARCHAR(100) NOT NULL,
    denda DECIMAL(12,2) DEFAULT 0.00,
    catatan TEXT DEFAULT NULL,
    FOREIGN KEY (id_sewa) REFERENCES penyewaan(id_sewa) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- SEED DATA
-- ==========================================

-- Seed Kategori
INSERT INTO kategori_mobil (id_kategori, nama_kategori) VALUES
(1, 'Supercar V8'),
(2, 'V12 Grand Tourer'),
(3, 'Hybrid Hypercar'),
(4, 'Luxury SUV')
ON DUPLICATE KEY UPDATE id_kategori=VALUES(id_kategori);

-- Seed Default Users (Passwords are admin123 and user123)
INSERT INTO users (id_user, nama, email, password, no_hp, alamat, nomor_sim, role) VALUES
(1, 'Alessandro Rossi', 'admin@ferrarirental.com', '$2y$10$eoeEeRLAbqHT.NUa/ua8ruLeNd45M7P5h3B8BxFSQjKmva/ZCI4XW', '081234567890', 'Maranello, Italy', 'SIM-ADMIN-999', 'admin'),
(2, 'Michael Schumacher', 'user@ferrarirental.com', '$2y$10$t0bc18MgMdlv7zQD8oy3IO.QAneFw5RUJde7olp6hCg8k9DJGZwcG', '089876543210', 'Monaco GP Street No. 1', 'SIM-USER-777', 'user')
ON DUPLICATE KEY UPDATE id_user=VALUES(id_user);

-- Seed Cars (Mobil)
INSERT INTO mobil (id_mobil, id_kategori, nama_mobil, merk, tahun, plat_nomor, harga_sewa_per_hari, status, foto, deskripsi) VALUES
(1, 3, 'SF90 Stradale', 'Ferrari', 2023, 'B 90 SF', 15000000.00, 'tersedia', 'sf90.jpg', 'Plug-in Hybrid supercar featuring a twin-turbo V8 engine and three electric motors, generating a total of 1000 cv (986 hp). Pure performance redrawn for the future.'),
(2, 1, 'F8 Tributo', 'Ferrari', 2022, 'B 8 FT', 10000000.00, 'tersedia', 'f8.jpg', 'The tribute to the ultimate V8 engine. Delivering 720 cv of instant power without turbo lag, offering unmatched driver involvement on road and track.'),
(3, 2, '812 Superfast', 'Ferrari', 2021, 'B 812 SF', 12000000.00, 'tersedia', '812.jpg', 'Front mid-mounted 6.5-liter naturally aspirated V12 engine. The fastest and most powerful road-going Ferrari of its era, outputting 800 cv of symphonic power.'),
(4, 2, 'Roma', 'Ferrari', 2023, 'B 25 RM', 8000000.00, 'tersedia', 'roma.jpg', 'La Nuova Dolce Vita. A timeless, elegant, and minimal front-engined V8 coupe designed to represent the carefree, pleasurable way of life in Rome during the 1950s and 60s.')
ON DUPLICATE KEY UPDATE id_mobil=VALUES(id_mobil);
