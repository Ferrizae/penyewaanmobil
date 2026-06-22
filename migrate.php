<?php
// migrate.php
require_once 'config/db.php';

echo "=== MEMULAI MIGRASI DATABASE ===\n";

// 1. Tambah kolom bukti_pembayaran ke tabel pembayaran
try {
    if (isset($pdo)) {
        // Cek apakah kolom sudah ada
        $check = $pdo->query("SHOW COLUMNS FROM pembayaran LIKE 'bukti_pembayaran'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE pembayaran ADD COLUMN bukti_pembayaran VARCHAR(255) DEFAULT NULL AFTER status_pembayaran");
            echo "[SUKSES] Kolom 'bukti_pembayaran' berhasil ditambahkan ke tabel 'pembayaran'.\n";
        } else {
            echo "[INFO] Kolom 'bukti_pembayaran' sudah ada di tabel 'pembayaran'.\n";
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
