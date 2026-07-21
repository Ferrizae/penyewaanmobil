<?php
// midtrans_callback.php
require_once 'config/db.php';
require_once 'config/midtrans.php';

header('Content-Type: application/json');

$json_input = file_get_contents('php://input');
$notification = json_decode($json_input, true);

if (!$notification) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
    exit;
}

$order_id = $notification['order_id'] ?? null;
$status_code = $notification['status_code'] ?? null;
$gross_amount = $notification['gross_amount'] ?? null;
$signature_key = $notification['signature_key'] ?? null;
$transaction_status = $notification['transaction_status'] ?? null;
$payment_type = $notification['payment_type'] ?? 'Midtrans';

if (!$order_id || !$transaction_status) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required notification parameters']);
    exit;
}

// Signature Verification (Security check)
if ($signature_key) {
    $expected_signature = hash('sha512', $order_id . $status_code . $gross_amount . MIDTRANS_SERVER_KEY);
    if ($signature_key !== $expected_signature && !empty(MIDTRANS_SERVER_KEY) && strpos(MIDTRANS_SERVER_KEY, 'DEMO') === false) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Invalid signature key']);
        exit;
    }
}

// Update Database based on Notification
try {
    if (isset($pdo)) {
        // Find payment by transaction_id
        $stmt = $pdo->prepare("SELECT * FROM pembayaran WHERE transaction_id = ?");
        $stmt->execute([$order_id]);
        $pay = $stmt->fetch();

        if ($pay) {
            $id_sewa = $pay['id_sewa'];

            if ($transaction_status == 'settlement' || $transaction_status == 'capture') {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'success', metode_pembayaran = ? WHERE id_pembayaran = ?")
                    ->execute(['Midtrans (' . $payment_type . ')', $pay['id_pembayaran']]);
                $pdo->prepare("UPDATE penyewaan SET status_sewa = 'sudah_bayar' WHERE id_sewa = ?")
                    ->execute([$id_sewa]);
                $pdo->commit();
            } elseif ($transaction_status == 'pending') {
                $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'pending', metode_pembayaran = ? WHERE id_pembayaran = ?")
                    ->execute(['Midtrans (' . $payment_type . ')', $pay['id_pembayaran']]);
            } elseif (in_array($transaction_status, ['deny', 'expire', 'cancel'])) {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'cancel' WHERE id_pembayaran = ?")
                    ->execute([$pay['id_pembayaran']]);
                $pdo->prepare("UPDATE penyewaan SET status_sewa = 'batal' WHERE id_sewa = ?")
                    ->execute([$id_sewa]);
                $pdo->commit();
            }
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Webhook processed successfully']);
?>
