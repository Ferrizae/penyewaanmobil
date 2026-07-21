<?php
// checkout_process.php
require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_sewa = isset($_REQUEST['id_sewa']) ? (int)$_REQUEST['id_sewa'] : 0;
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$metode = isset($_REQUEST['metode']) && !empty($_REQUEST['metode']) ? $_REQUEST['metode'] : 'Midtrans Snap';
$transaction_id = isset($_REQUEST['transaction_id']) ? $_REQUEST['transaction_id'] : '';

if ($id_sewa <= 0) {
    header("Location: history.php");
    exit;
}

$db_connected = false;

try {
    if (isset($pdo)) {
        // Verify rental belongs to user
        $stmt = $pdo->prepare("SELECT * FROM penyewaan WHERE id_sewa = ? AND id_user = ?");
        $stmt->execute([$id_sewa, $_SESSION['id_user']]);
        $rental = $stmt->fetch();

        if ($rental) {
            $db_connected = true;

            if ($action === 'success') {
                $pdo->beginTransaction();

                // Update penyewaan status
                $stmt_sewa = $pdo->prepare("UPDATE penyewaan SET status_sewa = 'sudah_bayar' WHERE id_sewa = ?");
                $stmt_sewa->execute([$id_sewa]);

                // Update or Insert pembayaran
                $stmt_check = $pdo->prepare("SELECT * FROM pembayaran WHERE id_sewa = ? ORDER BY id_pembayaran DESC LIMIT 1");
                $stmt_check->execute([$id_sewa]);
                $pay = $stmt_check->fetch();

                if ($pay) {
                    $stmt_pay = $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'success', metode_pembayaran = ? WHERE id_pembayaran = ?");
                    $stmt_pay->execute([$metode, $pay['id_pembayaran']]);
                } else {
                    $stmt_pay = $pdo->prepare("INSERT INTO pembayaran (id_sewa, metode_pembayaran, transaction_id, jumlah_bayar, status_pembayaran) VALUES (?, ?, ?, ?, 'success')");
                    $stmt_pay->execute([$id_sewa, $metode, $transaction_id ?: ('TRX-MID-' . time()), $rental['total_harga']]);
                }

                $pdo->commit();
                header("Location: history.php?pay_success=1");
                exit;
            } elseif ($action === 'pending') {
                $stmt_pay = $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'pending', metode_pembayaran = ? WHERE id_sewa = ? AND status_pembayaran = 'pending'");
                $stmt_pay->execute([$metode, $id_sewa]);
                header("Location: payment.php?id_sewa=" . $id_sewa);
                exit;
            }
        }
    }
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

// Session mock fallback
if (!$db_connected) {
    if (isset($_SESSION['mock_rentals'][$id_sewa])) {
        if ($action === 'success') {
            $_SESSION['mock_rentals'][$id_sewa]['status_sewa'] = 'sudah_bayar';
            if (isset($_SESSION['mock_payments'][$id_sewa])) {
                $_SESSION['mock_payments'][$id_sewa]['status_pembayaran'] = 'success';
                $_SESSION['mock_payments'][$id_sewa]['metode_pembayaran'] = $metode;
            }
            header("Location: history.php?pay_success=1");
            exit;
        } elseif ($action === 'pending') {
            if (isset($_SESSION['mock_payments'][$id_sewa])) {
                $_SESSION['mock_payments'][$id_sewa]['status_pembayaran'] = 'pending';
                $_SESSION['mock_payments'][$id_sewa]['metode_pembayaran'] = $metode;
            }
            header("Location: payment.php?id_sewa=" . $id_sewa);
            exit;
        }
    }
}

header("Location: payment.php?id_sewa=" . $id_sewa);
exit;
?>
