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
    kapasitas_kursi VARCHAR(50) DEFAULT '7 Kursi',
    transmisi VARCHAR(50) DEFAULT 'Manual / Matic',
    bahan_bakar VARCHAR(50) DEFAULT 'Bensin',
    kapasitas_mesin VARCHAR(50) DEFAULT '1.500 cc',
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
    snap_token VARCHAR(255) DEFAULT NULL,
    jumlah_bayar DECIMAL(12,2) NOT NULL,
    status_pembayaran ENUM('pending', 'success', 'expire', 'cancel') DEFAULT 'pending',
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
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
(1, 'MPV Keluarga'),
(2, 'SUV Tangguh'),
(3, 'Hatchback / City Car'),
(4, 'Sedan Elegan')
ON DUPLICATE KEY UPDATE id_kategori=VALUES(id_kategori);

-- Seed Default Users (Passwords are admin123 and user123)
INSERT INTO users (id_user, nama, email, password, no_hp, alamat, nomor_sim, role) VALUES
(1, 'Hardi Wijaya', 'admin@familydriverental.com', '$2y$10$eoeEeRLAbqHT.NUa/ua8ruLeNd45M7P5h3B8BxFSQjKmva/ZCI4XW', '081234567890', 'Mataram, NTB, Indonesia', 'SIM-ADMIN-999', 'admin'),
(2, 'Ferdi Pratama', 'user@familydriverental.com', '$2y$10$t0bc18MgMdlv7zQD8oy3IO.QAneFw5RUJde7olp6hCg8k9DJGZwcG', '089876543210', 'Jl. Langko No. 10, Mataram', 'SIM-USER-777', 'user')
ON DUPLICATE KEY UPDATE id_user=VALUES(id_user);

-- Seed Cars (Mobil)
INSERT INTO mobil (id_mobil, id_kategori, nama_mobil, merk, tahun, plat_nomor, harga_sewa_per_hari, status, foto, deskripsi, kapasitas_kursi, transmisi, bahan_bakar, kapasitas_mesin) VALUES
(1, 1, 'Avanza', 'Toyota', 2023, 'DR 1902 SA', 350000.00, 'tersedia', 'avanza.jpg', 'Toyota Avanza merupakan mobil MPV keluarga terfavorit di Indonesia, menawarkan kabin lapang dengan 7 kursi penumpang, AC double blower, dan kenyamanan berkendara terbaik untuk seluruh anggota keluarga.', '7 Kursi', 'Manual / Matic', 'Bensin', '1.500 cc'),
(2, 1, 'Xpander', 'Mitsubishi', 2022, 'DR 8008 FT', 450000.00, 'tersedia', 'xpander.jpg', 'Mitsubishi Xpander hadir dengan desain eksterior yang gagah, interior mewah senyap, suspensi stabil, serta ruang kabin lega yang ideal untuk petualangan keluarga Anda.', '7 Kursi', 'CVT Otomatis', 'Bensin', '1.500 cc'),
(3, 1, 'Kijang Innova', 'Toyota', 2021, 'DR 8128 SF', 650000.00, 'tersedia', 'innova.jpg', 'Toyota Kijang Innova Reborn menghadirkan kemewahan berkelas, kenyamanan maksimal dengan ruang kaki luas, performa mesin tangguh, dan sangat cocok untuk perjalanan jarak jauh.', '7 Kursi', 'Manual / Matic', 'Diesel / Bensin', '2.400 cc'),
(4, 1, 'All New Veloz', 'Toyota', 2023, 'DR 2525 RM', 450000.00, 'tersedia', 'veloz.jpg', 'Toyota All New Veloz menyuguhkan fitur keselamatan mutakhir Toyota Safety Sense, desain modern premium, kabin fleksibel, dan kenyamanan prima untuk mobilitas urban keluarga.', '7 Kursi', 'CVT Otomatis', 'Bensin', '1.500 cc'),
(5, 2, 'Pajero Sport', 'Mitsubishi', 2022, 'DR 1555 PS', 850000.00, 'tersedia', 'pajero.jpg', 'Mitsubishi Pajero Sport adalah SUV premium dengan mesin tangguh, ground clearance tinggi, fitur keselamatan lengkap, cocok untuk menaklukkan segala kondisi jalan dengan gagah.', '7 Kursi', '8-Speed Matic', 'Diesel (Solar)', '2.400 cc'),
(6, 3, 'Jazz', 'Honda', 2020, 'DR 9099 JZ', 350000.00, 'tersedia', 'jazz.jpg', 'Honda Jazz merupakan hatchback lincah nan sporty, kabin fleksibel dengan sistem Ultra Seat, efisiensi bahan bakar tinggi, sangat ideal untuk bermanuver di jalan perkotaan.', '5 Kursi', 'Manual / Matic', 'Bensin', '1.500 cc'),
(7, 4, 'Civic Turbo', 'Honda', 2023, 'DR 1000 CV', 900000.00, 'tersedia', 'civic.jpg', 'Honda Civic Sedan menyajikan desain eksterior agresif premium, kenyamanan berkendara tingkat tinggi, performa mesin turbo yang responsif, mencerminkan prestise dan gaya hidup modern.', '5 Kursi', 'CVT Otomatis', 'Bensin Turbo', '1.500 cc')
ON DUPLICATE KEY UPDATE id_mobil=VALUES(id_mobil);
