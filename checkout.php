<?php
// checkout.php
require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_sewa = isset($_GET['id_sewa']) ? (int)$_GET['id_sewa'] : 0;
if ($id_sewa <= 0) {
    header("Location: index.php");
    exit;
}

$rental = null;
$db_connected = false;

// 1. Fetch rental details
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("
            SELECT s.*, m.nama_mobil, m.merk, m.harga_sewa_per_hari, m.foto, u.nama, u.email
            FROM penyewaan s
            JOIN mobil m ON s.id_mobil = m.id_mobil
            JOIN users u ON s.id_user = u.id_user
            WHERE s.id_sewa = ? AND s.id_user = ?
        ");
        $stmt->execute([$id_sewa, $_SESSION['id_user']]);
        $rental = $stmt->fetch();
        if ($rental) {
            $db_connected = true;
        }
    }
} catch (PDOException $e) {
    $db_connected = false;
}

// Fallback to session mockup if offline
if (!$db_connected || !$rental) {
    if (isset($_SESSION['mock_rentals'][$id_sewa])) {
        $rental = $_SESSION['mock_rentals'][$id_sewa];
        if ($rental['status_sewa'] !== 'belum_bayar') {
            header("Location: history.php");
            exit;
        }
        $rental['merk'] = 'Ferrari';
        $rental['foto'] = strtolower(str_replace(' ', '', $rental['nama_mobil'])) . '.jpg';
        $rental['nama'] = $_SESSION['nama'];
        $rental['email'] = $_SESSION['email'];
        $rental['harga_sewa_per_hari'] = $rental['total_harga'] / $rental['lama_sewa'];
    } else {
        header("Location: index.php");
        exit;
    }
} else {
    // DB Connected, check status
    if ($rental['status_sewa'] !== 'belum_bayar') {
        header("Location: history.php");
        exit;
    }
}

// 2. Process payment simulation
$payment_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = $_POST['metode_pembayaran'];
    $transaction_id = 'TRX-' . strtoupper(bin2hex(random_bytes(6)));
    $jumlah_bayar = $rental['total_harga'];

    if ($db_connected) {
        try {
            $pdo->beginTransaction();

            // Clear old pending/canceled payment entries for this booking first
            $stmt_del = $pdo->prepare("DELETE FROM pembayaran WHERE id_sewa = ? AND status_pembayaran != 'success'");
            $stmt_del->execute([$id_sewa]);

            // Insert into pembayaran as pending
            $stmt_pay = $pdo->prepare("INSERT INTO pembayaran (id_sewa, metode_pembayaran, transaction_id, jumlah_bayar, status_pembayaran) VALUES (?, ?, ?, ?, 'pending')");
            $stmt_pay->execute([$id_sewa, $metode, $transaction_id, $jumlah_bayar]);

            $pdo->commit();
            $payment_success = true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            die("Kesalahan Pembayaran: " . $e->getMessage());
        }
    } else {
        // Mock session update
        $_SESSION['mock_rentals'][$id_sewa]['status_sewa'] = 'belum_bayar';
        $_SESSION['mock_payments'][$id_sewa] = [
            'id_pembayaran' => count($_SESSION['mock_payments'] ?? []) + 1,
            'id_sewa' => $id_sewa,
            'metode_pembayaran' => $metode,
            'transaction_id' => $transaction_id,
            'jumlah_bayar' => $jumlah_bayar,
            'status_pembayaran' => 'pending',
            'bukti_pembayaran' => null,
            'tanggal_pembayaran' => date('Y-m-d H:i:s')
        ];
        $payment_success = true;
    }

    if ($payment_success) {
        header("Location: payment.php?id_sewa=" . $id_sewa);
        exit;
    }
}

$page_title = "Checkout Transaksi";
require_once 'includes/header.php';
?>

<section class="section-band" style="background-color: var(--color-canvas); padding: var(--spacing-lg) 0;">
    <div class="grid-container" style="max-width: 900px;">
        
        <h2 class="display-lg" style="margin-bottom: var(--spacing-sm); text-transform: uppercase;">Checkout Pembayaran</h2>

        <div class="grid-2-col">
            
            <!-- Rental Details Column -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline);">
                <h3 class="title-sm" style="border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs); text-transform: uppercase;">Rincian Pesanan</h3>
                
                <div style="display: flex; gap: var(--spacing-xs); align-items: center; margin-bottom: var(--spacing-sm);">
                    <img src="assets/img/<?= htmlspecialchars($rental['foto']) ?>" alt="<?= htmlspecialchars($rental['nama_mobil']) ?>" style="width: 100px; height: 60px; object-fit: cover; border: 1px solid var(--color-hairline);">
                    <div>
                        <h4 class="title-sm" style="color: var(--color-ink);"><?= htmlspecialchars($rental['merk'] . ' ' . $rental['nama_mobil']) ?></h4>
                        <span style="font-size: 13px; color: var(--color-muted);">Tarif Sewa: Rp <?= number_format($rental['harga_sewa_per_hari'], 0, ',', '.') ?> / hari</span>
                    </div>
                </div>

                <div style="margin-bottom: var(--spacing-sm); border-top: 1px solid var(--color-hairline); padding-top: var(--spacing-xs); font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-xxxs);">
                        <span>Penyewa</span>
                        <span style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars($rental['nama']) ?> (<?= htmlspecialchars($rental['email']) ?>)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-xxxs);">
                        <span>Tanggal Mulai</span>
                        <span style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars(date('d F Y', strtotime($rental['tanggal_sewa']))) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: var(--spacing-xxxs);">
                        <span>Tanggal Kembali</span>
                        <span style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars(date('d F Y', strtotime($rental['tanggal_kembali']))) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Lama Sewa</span>
                        <span style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars($rental['lama_sewa']) ?> Hari</span>
                    </div>
                </div>

                <div style="border-top: 1px double var(--color-hairline); padding-top: var(--spacing-xs); display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-weight: 600; text-transform: uppercase; font-size: 12px; color: var(--color-ink);">Total Pembayaran</span>
                    <span class="title-md" style="font-size: 24px; color: var(--color-primary); font-weight: 700;">
                        Rp <?= number_format($rental['total_harga'], 0, ',', '.') ?>
                    </span>
                </div>
            </div>

            <!-- Payment Simulation Column -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline);">
                
                <h3 class="title-sm" style="border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs); text-transform: uppercase;">Pembayaran Online</h3>
                
                <p style="font-size: 13px; color: var(--color-body); margin-bottom: var(--spacing-sm);">
                    Pilih salah satu metode pembayaran di bawah untuk mensimulasikan gerbang pembayaran online Midtrans.
                </p>

                <form action="checkout.php?id_sewa=<?= $id_sewa ?>" method="POST">
                    
                    <div style="margin-bottom: var(--spacing-sm);">
                        <label class="form-label-dark" style="margin-bottom: var(--spacing-xs);">Pilih Metode Pembayaran</label>
                        
                        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: var(--spacing-xs); padding: var(--spacing-xs); border: 1px solid var(--color-hairline); cursor: pointer; color: var(--color-ink);">
                            <input type="radio" name="metode_pembayaran" value="Credit Card" checked style="accent-color: var(--color-primary);">
                            <span><i class="fa-solid fa-credit-card"></i> Kartu Kredit / Debit Online</span>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: var(--spacing-xs); padding: var(--spacing-xs); border: 1px solid var(--color-hairline); cursor: pointer; color: var(--color-ink);">
                            <input type="radio" name="metode_pembayaran" value="Bank Transfer" style="accent-color: var(--color-primary);">
                            <span><i class="fa-solid fa-building-columns"></i> Virtual Account / Bank Transfer</span>
                        </label>

                        <label style="display: flex; align-items: center; gap: 8px; padding: var(--spacing-xs); border: 1px solid var(--color-hairline); cursor: pointer; color: var(--color-ink);">
                            <input type="radio" name="metode_pembayaran" value="E-Wallet (QRIS)" style="accent-color: var(--color-primary);">
                            <span><i class="fa-solid fa-qrcode"></i> QRIS / ShopeePay / GoPay</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-primary-ferrari" style="width: 100%;">Bayar Sekarang</button>
                </form>

                <div style="margin-top: var(--spacing-sm); padding-top: var(--spacing-xs); border-top: 1px dashed var(--color-hairline); font-size: 11px; text-align: center; color: var(--color-muted);">
                    <i class="fa-solid fa-shield-halved"></i> Transaksi dienkripsi secara aman melalui simulasi gerbang pembayaran.
                </div>

            </div>

        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
