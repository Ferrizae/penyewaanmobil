<?php
// rent.php
require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure logged in and is a normal user
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id_mobil = (int)$_POST['id_mobil'];
$tanggal_sewa = $_POST['tanggal_sewa'];
$tanggal_kembali = $_POST['tanggal_kembali'];

if (empty($tanggal_sewa) || empty($tanggal_kembali) || $id_mobil <= 0) {
    header("Location: index.php");
    exit;
}

// Calculate duration
$start = new DateTime($tanggal_sewa);
$end = new DateTime($tanggal_kembali);
$interval = $start->diff($end);
$days = $interval->days;

if ($days <= 0) {
    $days = 1; // Minimum 1 day rent
}

$car = null;
$db_connected = false;

// 1. Fetch car data
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT * FROM mobil WHERE id_mobil = ?");
        $stmt->execute([$id_mobil]);
        $car = $stmt->fetch();
        if ($car) {
            $db_connected = true;
        }
    }
} catch (PDOException $e) {
    $db_connected = false;
}

// Fallback Mock Data if DB offline
if (!$db_connected || !$car) {
    $mock_cars = [
        1 => ['id_mobil' => 1, 'nama_mobil' => 'SF90 Stradale', 'harga_sewa_per_hari' => 15000000.00],
        2 => ['id_mobil' => 2, 'nama_mobil' => 'F8 Tributo', 'harga_sewa_per_hari' => 10000000.00],
        3 => ['id_mobil' => 3, 'nama_mobil' => '812 Superfast', 'harga_sewa_per_hari' => 12000000.00],
        4 => ['id_mobil' => 4, 'nama_mobil' => 'Roma', 'harga_sewa_per_hari' => 8000000.00]
    ];
    $car = isset($mock_cars[$id_mobil]) ? $mock_cars[$id_mobil] : $mock_cars[1];
}

$harga_sewa = $car['harga_sewa_per_hari'];
$total_harga = $days * $harga_sewa;
$id_user = $_SESSION['id_user'];

// 2. Insert into DB or Mock Session
if ($db_connected) {
    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Insert rental record
        $stmt = $pdo->prepare("INSERT INTO penyewaan (id_user, id_mobil, tanggal_sewa, tanggal_kembali, lama_sewa, total_harga, status_sewa) VALUES (?, ?, ?, ?, ?, ?, 'belum_bayar')");
        $stmt->execute([$id_user, $id_mobil, $tanggal_sewa, $tanggal_kembali, $days, $total_harga]);
        $id_sewa = $pdo->lastInsertId();

        // Update car status to 'disewa'
        $stmt_update = $pdo->prepare("UPDATE mobil SET status = 'disewa' WHERE id_mobil = ?");
        $stmt_update->execute([$id_mobil]);

        $pdo->commit();
        
        header("Location: checkout.php?id_sewa=" . $id_sewa);
        exit;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Terjadi kesalahan transaksi: " . $e->getMessage());
    }
} else {
    // If DB is offline, simulate transaction details by storing it in PHP session
    // This allows testing the checkout page seamlessly
    if (!isset($_SESSION['mock_rentals'])) {
        $_SESSION['mock_rentals'] = [];
    }
    
    $id_sewa = count($_SESSION['mock_rentals']) + 1;
    $_SESSION['mock_rentals'][$id_sewa] = [
        'id_sewa' => $id_sewa,
        'id_user' => $id_user,
        'id_mobil' => $id_mobil,
        'nama_mobil' => $car['nama_mobil'],
        'tanggal_sewa' => $tanggal_sewa,
        'tanggal_kembali' => $tanggal_kembali,
        'lama_sewa' => $days,
        'total_harga' => $total_harga,
        'status_sewa' => 'belum_bayar'
    ];

    header("Location: checkout.php?id_sewa=" . $id_sewa);
    exit;
}
?>
