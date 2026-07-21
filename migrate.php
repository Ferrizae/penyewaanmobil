<?php
// migrate.php
require_once 'config/db.php';

echo "=== MEMULAI MIGRASI DATABASE ===\n";

// 1. Tambah kolom bukti_pembayaran dan snap_token ke tabel pembayaran
try {
    if (isset($pdo)) {
        // Cek apakah kolom bukti_pembayaran sudah ada
        $check = $pdo->query("SHOW COLUMNS FROM pembayaran LIKE 'bukti_pembayaran'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE pembayaran ADD COLUMN bukti_pembayaran VARCHAR(255) DEFAULT NULL AFTER status_pembayaran");
            echo "[SUKSES] Kolom 'bukti_pembayaran' berhasil ditambahkan ke tabel 'pembayaran'.\n";
        }
        // Cek apakah kolom snap_token sudah ada
        $check_snap = $pdo->query("SHOW COLUMNS FROM pembayaran LIKE 'snap_token'");
        if ($check_snap->rowCount() == 0) {
            $pdo->exec("ALTER TABLE pembayaran ADD COLUMN snap_token VARCHAR(255) DEFAULT NULL AFTER transaction_id");
            echo "[SUKSES] Kolom 'snap_token' berhasil ditambahkan ke tabel 'pembayaran'.\n";
        }

        // Cek & tambah kolom spesifikasi ke tabel mobil
        $spec_cols = [
            'kapasitas_kursi' => "VARCHAR(50) DEFAULT '7 Kursi'",
            'transmisi' => "VARCHAR(50) DEFAULT 'Manual / Matic'",
            'bahan_bakar' => "VARCHAR(50) DEFAULT 'Bensin'",
            'kapasitas_mesin' => "VARCHAR(50) DEFAULT '1.500 cc'"
        ];

        foreach ($spec_cols as $col => $type) {
            $c = $pdo->query("SHOW COLUMNS FROM mobil LIKE '$col'");
            if ($c->rowCount() == 0) {
                $pdo->exec("ALTER TABLE mobil ADD COLUMN $col $type");
                echo "[SUKSES] Kolom '$col' berhasil ditambahkan ke tabel 'mobil'.\n";
            }
        }
    } else {
        echo "[ERROR] Koneksi database (PDO) tidak tersedia.\n";
    }
} catch (PDOException $e) {
    echo "[ERROR] Gagal merubah struktur database: " . $e->getMessage() . "\n";
}

// 2. Buat direktori uploads/bukti_pembayaran/ jika belum ada
$upload_dir = 'uploads/bukti_pembayaran';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "[SUKSES] Direktori '$upload_dir' berhasil dibuat.\n";
    } else {
        echo "[ERROR] Gagal membuat direktori '$upload_dir'.\n";
    }
} else {
    echo "[INFO] Direktori '$upload_dir' sudah ada.\n";
}

echo "=== MIGRASI SELESAI ===\n";
?>
