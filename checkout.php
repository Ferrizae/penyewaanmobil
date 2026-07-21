<?php
// checkout.php
require_once 'config/db.php';
require_once 'config/midtrans.php';

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
            SELECT s.*, m.nama_mobil, m.merk, m.harga_sewa_per_hari, m.foto, u.nama, u.email, u.no_hp
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
        $rental['merk'] = isset($rental['merk']) ? $rental['merk'] : 'Toyota';
        $rental['foto'] = isset($rental['foto']) ? $rental['foto'] : 'avanza.jpg';
        $rental['nama'] = $_SESSION['nama'] ?? 'Pelanggan';
        $rental['email'] = $_SESSION['email'] ?? 'pelanggan@example.com';
        $rental['no_hp'] = '08123456789';
        $rental['harga_sewa_per_hari'] = $rental['total_harga'] / $rental['lama_sewa'];
    } else {
        header("Location: index.php");
        exit;
    }
} else {
    if ($rental['status_sewa'] !== 'belum_bayar') {
        header("Location: history.php");
        exit;
    }
}

// 2. Prepare Transaction ID & Midtrans Snap Token
$transaction_id = 'TRX-MID-' . str_pad($id_sewa, 5, '0', STR_PAD_LEFT) . '-' . time();
$snap_token = null;

// Get or create Snap Token
$order_data = [
    'order_id' => $transaction_id,
    'gross_amount' => $rental['total_harga'],
    'customer_name' => $rental['nama'],
    'customer_email' => $rental['email'],
    'customer_phone' => $rental['no_hp'] ?? '08123456789',
    'item_id' => 'SEWA-' . $rental['id_sewa'],
    'item_price' => $rental['total_harga'],
    'item_quantity' => 1,
    'item_name' => 'Sewa ' . $rental['merk'] . ' ' . $rental['nama_mobil'] . ' (' . $rental['lama_sewa'] . ' Hari)'
];

$snap_token = get_midtrans_snap_token($order_data);

// Store/update pending payment record in DB or session
if ($db_connected) {
    try {
        $stmt_del = $pdo->prepare("DELETE FROM pembayaran WHERE id_sewa = ? AND status_pembayaran != 'success'");
        $stmt_del->execute([$id_sewa]);

        $stmt_pay = $pdo->prepare("INSERT INTO pembayaran (id_sewa, metode_pembayaran, transaction_id, snap_token, jumlah_bayar, status_pembayaran) VALUES (?, 'Midtrans Snap', ?, ?, ?, 'pending')");
        $stmt_pay->execute([$id_sewa, $transaction_id, $snap_token, $rental['total_harga']]);
    } catch (PDOException $e) {
        // Handle error silently
    }
} else {
    $_SESSION['mock_payments'][$id_sewa] = [
        'id_pembayaran' => count($_SESSION['mock_payments'] ?? []) + 1,
        'id_sewa' => $id_sewa,
        'metode_pembayaran' => 'Midtrans Snap',
        'transaction_id' => $transaction_id,
        'snap_token' => $snap_token,
        'jumlah_bayar' => $rental['total_harga'],
        'status_pembayaran' => 'pending',
        'bukti_pembayaran' => null,
        'tanggal_pembayaran' => date('Y-m-d H:i:s')
    ];
}

$page_title = "Checkout Pembayaran Midtrans";
require_once 'includes/header.php';
?>

<!-- Midtrans Snap JS SDK -->
<script type="text/javascript" src="<?= MIDTRANS_SNAP_JS_URL ?>" data-client-key="<?= MIDTRANS_CLIENT_KEY ?>"></script>

<style>
/* Custom Midtrans Snap Modal Styles - Adapted to Dark Ferrari Theme */
.midtrans-modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(8px);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: fadeIn 0.25s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.midtrans-card {
    background: #181818;
    width: 100%;
    max-width: 440px;
    border: 1px solid var(--color-hairline);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0,0,0,0.9), 0 0 20px rgba(255,40,0,0.15);
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--color-ink);
}

.midtrans-header {
    background: #0f0f0f;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-hairline);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.midtrans-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.5px;
}

.midtrans-close {
    background: transparent;
    border: none;
    color: var(--color-muted);
    font-size: 20px;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
}

.midtrans-close:hover {
    color: var(--color-ink);
}

.midtrans-total-box {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--color-hairline);
    border-radius: 8px;
    padding: 14px 18px;
    margin: 16px 20px 0 20px;
}

.midtrans-timer {
    font-size: 11px;
    color: var(--color-muted);
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}

.midtrans-timer span.time-badge {
    color: #2563eb;
    font-weight: 700;
}

.midtrans-amount {
    font-size: 22px;
    font-weight: 700;
    color: var(--color-ink);
}

.midtrans-order-id {
    font-size: 11px;
    color: var(--color-muted);
    font-family: monospace;
}

.midtrans-content {
    padding: 20px;
}

.midtrans-screen {
    display: none;
}

.midtrans-screen.active {
    display: block;
    animation: slideIn 0.25s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.midtrans-section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-ink);
    margin-bottom: 12px;
}

.midtrans-nav-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-ink);
    cursor: pointer;
    margin-bottom: 16px;
    transition: opacity 0.2s;
}

.midtrans-nav-back:hover {
    opacity: 0.8;
}

.midtrans-option-item {
    background: #222222;
    border: 1px solid var(--color-hairline);
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.2s ease;
}

.midtrans-option-item:hover {
    border-color: var(--color-primary);
    background: #282828;
    transform: translateY(-1px);
}

.midtrans-option-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.midtrans-option-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-ink);
}

.midtrans-logos {
    display: flex;
    gap: 6px;
    align-items: center;
}

.midtrans-logo-pill {
    background: #ffffff;
    color: #111;
    font-size: 9px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 3px;
    letter-spacing: 0.5px;
    display: inline-block;
}

.midtrans-form-group {
    margin-bottom: 14px;
}

.midtrans-form-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--color-muted);
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
}

.midtrans-input {
    width: 100%;
    background: #111111;
    border: 1px solid var(--color-hairline);
    border-radius: 6px;
    padding: 12px 14px;
    color: var(--color-ink);
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.midtrans-input:focus {
    border-color: var(--color-primary);
}

.midtrans-btn {
    width: 100%;
    background: var(--color-primary);
    color: #ffffff;
    border: none;
    border-radius: 6px;
    padding: 14px;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.midtrans-btn:hover {
    background: var(--color-primary-hover);
}

.midtrans-btn:active {
    transform: scale(0.99);
}

.qr-box-wrapper {
    background: #ffffff;
    padding: 16px;
    border-radius: 8px;
    text-align: center;
    display: inline-block;
    border: 2px solid var(--color-primary);
    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    margin: 10px 0 16px 0;
}

.va-copy-box {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--color-hairline);
    border-radius: 6px;
    padding: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
</style>

<section class="section-band" style="background-color: var(--color-canvas); padding: var(--spacing-lg) 0;">
    <div class="grid-container" style="max-width: 960px;">
        
        <div style="margin-bottom: var(--spacing-md); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <span class="caption-uppercase" style="color: var(--color-primary);">Gerbang Pembayaran</span>
                <h2 class="display-lg" style="margin-top: 4px;">Midtrans Payment Gateway</h2>
            </div>
            <img src="https://midtrans.com/assets/img/midtrans-logo-white.svg" alt="Midtrans Logo" style="height: 32px; opacity: 0.85;" onerror="this.onerror=null; this.style.display='none';">
        </div>

        <div class="grid-2-col" style="gap: var(--spacing-md);">
            
            <!-- Rental Details Column -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline);">
                <h3 class="title-sm" style="border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs); text-transform: uppercase; color: var(--color-primary);">Rincian Transaksi</h3>
                
                <div style="display: flex; gap: var(--spacing-xs); align-items: center; margin-bottom: var(--spacing-sm);">
                    <img src="assets/img/<?= htmlspecialchars($rental['foto']) ?>" alt="<?= htmlspecialchars($rental['nama_mobil']) ?>" style="width: 110px; height: 68px; object-fit: cover; border: 1px solid var(--color-hairline); border-radius: 2px;">
                    <div>
                        <h4 class="title-sm" style="color: var(--color-ink);"><?= htmlspecialchars((!empty($rental['merk']) && stripos($rental['nama_mobil'], $rental['merk']) === 0) ? $rental['nama_mobil'] : $rental['merk'] . ' ' . $rental['nama_mobil']) ?></h4>
                        <span style="font-size: 13px; color: var(--color-muted);">Tarif Sewa: Rp <?= number_format($rental['harga_sewa_per_hari'], 0, ',', '.') ?> / hari</span>
                    </div>
                </div>

                <div style="margin-bottom: var(--spacing-sm); border-top: 1px solid var(--color-hairline); padding-top: var(--spacing-xs); font-size: 13px; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-muted);">ID Transaksi</span>
                        <span style="color: var(--color-ink); font-weight: 600; font-family: monospace;"><?= htmlspecialchars($transaction_id) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-muted);">Nama Penyewa</span>
                        <span style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars($rental['nama']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-muted);">Email</span>
                        <span style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars($rental['email']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--color-muted);">Periode Sewa</span>
                        <span style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars(date('d M Y', strtotime($rental['tanggal_sewa']))) ?> - <?= htmlspecialchars(date('d M Y', strtotime($rental['tanggal_kembali']))) ?> (<?= htmlspecialchars($rental['lama_sewa']) ?> Hari)</span>
                    </div>
                </div>

                <div style="border-top: 1px double var(--color-hairline); padding-top: var(--spacing-xs); display: flex; justify-content: space-between; align-items: baseline;">
                    <span style="font-weight: 600; text-transform: uppercase; font-size: 12px; color: var(--color-ink);">Total Tagihan</span>
                    <span class="title-md" style="font-size: 26px; color: var(--color-primary); font-weight: 700;">
                        Rp <?= number_format($rental['total_harga'], 0, ',', '.') ?>
                    </span>
                </div>
            </div>

            <!-- Midtrans Payment Options Column -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 class="title-sm" style="border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs); text-transform: uppercase; display: flex; align-items: center; justify-content: space-between;">
                        <span>Metode Pembayaran</span>
                        <span class="badge-pill-ferrari" style="font-size: 10px; border-color: var(--color-primary); color: var(--color-primary);">Midtrans Snap</span>
                    </h3>

                    <p style="font-size: 13px; color: var(--color-body); margin-bottom: var(--spacing-sm); line-height: 1.5;">
                        Selesaikan pembayaran secara instan dan aman melalui kanal pembayaran resmi yang didukung Midtrans:
                    </p>

                    <!-- Kanal Pembayaran Supported Cards -->
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: var(--spacing-md);">
                        <div style="padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid var(--color-hairline); border-radius: 4px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-credit-card" style="font-size: 20px; color: #4285F4;"></i>
                            <div>
                                <strong style="font-size: 12px; color: var(--color-ink); display: block;">Kartu Kredit / Debit</strong>
                                <span style="font-size: 10px; color: var(--color-muted);">Visa, MasterCard, JCB</span>
                            </div>
                        </div>

                        <div style="padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid var(--color-hairline); border-radius: 4px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-building-columns" style="font-size: 20px; color: #00A65A;"></i>
                            <div>
                                <strong style="font-size: 12px; color: var(--color-ink); display: block;">Virtual Account</strong>
                                <span style="font-size: 10px; color: var(--color-muted);">BCA, Mandiri, BNI, BRI</span>
                            </div>
                        </div>

                        <div style="padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid var(--color-hairline); border-radius: 4px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-qrcode" style="font-size: 20px; color: #EE4D2D;"></i>
                            <div>
                                <strong style="font-size: 12px; color: var(--color-ink); display: block;">QRIS / E-Wallet</strong>
                                <span style="font-size: 10px; color: var(--color-muted);">GoPay, ShopeePay, QRIS</span>
                            </div>
                        </div>

                        <div style="padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid var(--color-hairline); border-radius: 4px; display: flex; align-items: center; gap: 10px;">
                            <i class="fa-solid fa-store" style="font-size: 20px; color: #F4B400;"></i>
                            <div>
                                <strong style="font-size: 12px; color: var(--color-ink); display: block;">Gerai Retail</strong>
                                <span style="font-size: 10px; color: var(--color-muted);">Indomaret, Alfamart</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="button" id="pay-button" class="btn-primary-ferrari" style="width: 100%; padding: 14px; font-size: 15px; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <i class="fa-solid fa-lock"></i> Bayar Sekarang via Midtrans
                    </button>

                    <div style="margin-top: var(--spacing-sm); padding-top: var(--spacing-xs); border-top: 1px dashed var(--color-hairline); font-size: 11px; text-align: center; color: var(--color-muted); display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="fa-solid fa-shield-halved" style="color: var(--color-primary);"></i> Transaksi diproses aman 256-bit SSL oleh Midtrans
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- Custom Multi-Screen Midtrans Snap Modal -->
<div id="midtrans-custom-modal" class="midtrans-modal-overlay">
    <div class="midtrans-card">
        
        <!-- Modal Top Brand Bar -->
        <div class="midtrans-header">
            <div class="midtrans-brand">
                <i class="fa-solid fa-car-side" style="color: var(--color-primary);"></i>
                <span>FAMILY <span style="color: var(--color-primary);">DRIVE</span></span>
            </div>
            <button class="midtrans-close" onclick="closeMidtransModal()">&times;</button>
        </div>

        <!-- Total & Countdown Card -->
        <div class="midtrans-total-box">
            <div class="midtrans-timer">
                <span>Total</span>
                <span>Bayar dalam <span id="countdown-timer" class="time-badge">00:59:59</span></span>
            </div>
            <div class="midtrans-amount">Rp <?= number_format($rental['total_harga'], 0, ',', '.') ?></div>
            <div class="midtrans-order-id">Order ID #<?= htmlspecialchars($transaction_id) ?></div>
        </div>

        <!-- Modal Content Screens Container -->
        <div class="midtrans-content">
            
            <!-- SCREEN 1: Main Payment Menu -->
            <div id="screen-menu" class="midtrans-screen active">
                <div class="midtrans-section-title">Metode pembayaran</div>
                
                <!-- Option 1: Transfer Bank -->
                <div class="midtrans-option-item" onclick="switchMidtransScreen('screen-banks')">
                    <div class="midtrans-option-left">
                        <div class="midtrans-option-title">Transfer bank</div>
                        <div class="midtrans-logos">
                            <span class="midtrans-logo-pill" style="color: #00569e;">BCA</span>
                            <span class="midtrans-logo-pill" style="color: #002d62;">Mandiri</span>
                            <span class="midtrans-logo-pill" style="color: #f15a24;">BNI</span>
                            <span class="midtrans-logo-pill" style="color: #00529c;">BRI</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--color-muted); font-size: 12px;"></i>
                </div>

                <!-- Option 2: Kartu kredit / debit -->
                <div class="midtrans-option-item" onclick="switchMidtransScreen('screen-card')">
                    <div class="midtrans-option-left">
                        <div class="midtrans-option-title">Kartu kredit/debit</div>
                        <div class="midtrans-logos">
                            <span class="midtrans-logo-pill" style="color: #1a1f71;">VISA</span>
                            <span class="midtrans-logo-pill" style="color: #eb001b;">MasterCard</span>
                            <span class="midtrans-logo-pill" style="color: #007bbf;">JCB</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--color-muted); font-size: 12px;"></i>
                </div>

                <!-- Option 3: GoPay / QRIS -->
                <div class="midtrans-option-item" onclick="openQrisScreen('GoPay / QRIS')">
                    <div class="midtrans-option-left">
                        <div class="midtrans-option-title">Gopay / QRIS</div>
                        <div class="midtrans-logos">
                            <span class="midtrans-logo-pill" style="color: #00a5cf;">GoPay</span>
                            <span class="midtrans-logo-pill" style="color: #e31837;">QRIS</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--color-muted); font-size: 12px;"></i>
                </div>

                <!-- Option 4: ShopeePay / E-Wallet -->
                <div class="midtrans-option-item" onclick="openQrisScreen('ShopeePay')">
                    <div class="midtrans-option-left">
                        <div class="midtrans-option-title">ShopeePay / E-Wallet</div>
                        <div class="midtrans-logos">
                            <span class="midtrans-logo-pill" style="color: #ee4d2d;">ShopeePay</span>
                            <span class="midtrans-logo-pill" style="color: #4c2a86;">OVO</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--color-muted); font-size: 12px;"></i>
                </div>
            </div>

            <!-- SCREEN 2: Bank Selection List -->
            <div id="screen-banks" class="midtrans-screen">
                <div class="midtrans-nav-back" onclick="switchMidtransScreen('screen-menu')">
                    <i class="fa-solid fa-arrow-left"></i> Transfer bank
                </div>

                <div class="midtrans-option-item" onclick="openVaScreen('BCA Virtual Account', '801099887766')">
                    <div class="midtrans-option-left">
                        <div class="midtrans-option-title">Bank BCA</div>
                        <span style="font-size: 11px; color: var(--color-muted);">Virtual Account</span>
                    </div>
                    <span class="midtrans-logo-pill" style="color: #00569e;">BCA</span>
                </div>

                <div class="midtrans-option-item" onclick="openVaScreen('Mandiri Bill Payment', '1370099887766')">
                    <div class="midtrans-option-left">
                        <div class="midtrans-option-title">Bank Mandiri</div>
                        <span style="font-size: 11px; color: var(--color-muted);">Livin' Bill Key</span>
                    </div>
                    <span class="midtrans-logo-pill" style="color: #002d62;">Mandiri</span>
                </div>

                <div class="midtrans-option-item" onclick="openVaScreen('BNI Virtual Account', '988010998877')">
                    <div class="midtrans-option-left">
                        <div class="midtrans-option-title">Bank BNI</div>
                        <span style="font-size: 11px; color: var(--color-muted);">Virtual Account</span>
                    </div>
                    <span class="midtrans-logo-pill" style="color: #f15a24;">BNI</span>
                </div>

                <div class="midtrans-option-item" onclick="openVaScreen('BRI Virtual Account', '888100998877')">
                    <div class="midtrans-option-left">
                        <div class="midtrans-option-title">Bank BRI</div>
                        <span style="font-size: 11px; color: var(--color-muted);">BRIVA</span>
                    </div>
                    <span class="midtrans-logo-pill" style="color: #00529c;">BRI</span>
                </div>
            </div>

            <!-- SCREEN 3: Card Details Form -->
            <div id="screen-card" class="midtrans-screen">
                <div class="midtrans-nav-back" onclick="switchMidtransScreen('screen-menu')">
                    <i class="fa-solid fa-arrow-left"></i> Kartu kredit/debit
                </div>

                <div class="midtrans-form-group">
                    <label class="midtrans-form-label">Nomor kartu</label>
                    <input type="text" id="card-num" class="midtrans-input" placeholder="0000 - 0000 - 0000 - 0000" maxlength="19" oninput="formatCardNum(this)">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;" class="midtrans-form-group">
                    <div>
                        <label class="midtrans-form-label">Masa berlaku</label>
                        <input type="text" id="card-exp" class="midtrans-input" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div>
                        <label class="midtrans-form-label">CVV</label>
                        <input type="password" id="card-cvv" class="midtrans-input" placeholder="***" maxlength="4">
                    </div>
                </div>

                <div style="margin-bottom: 16px; display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--color-muted);">
                    <input type="checkbox" id="save-card" checked style="accent-color: var(--color-primary);">
                    <label for="save-card" style="cursor: pointer;">Simpan kartu ini untuk transaksi berikutnya</label>
                </div>

                <div style="font-size: 10px; color: var(--color-muted); text-align: center; margin-bottom: 16px;">
                    <i class="fa-solid fa-lock" style="color: var(--color-primary);"></i> Secure payments powered by Midtrans 3D-Secure
                </div>

                <button type="button" class="midtrans-btn" onclick="submitCustomPayment('Credit Card')">
                    Bayar sekarang
                </button>
            </div>

            <!-- SCREEN 4: QRIS / GoPay Display -->
            <div id="screen-qris" class="midtrans-screen">
                <div class="midtrans-nav-back" onclick="switchMidtransScreen('screen-menu')">
                    <i class="fa-solid fa-arrow-left"></i> <span id="qris-header-title">GoPay / QRIS</span>
                </div>

                <div style="text-align: center;">
                    <p style="font-size: 12px; color: var(--color-muted); margin-bottom: 8px;">Scan kode QRIS menggunakan GoPay, ShopeePay, atau Bank App:</p>
                    
                    <div class="qr-box-wrapper">
                        <div style="color: #111; font-weight: 800; font-size: 11px; margin-bottom: 4px; letter-spacing: 1px;">QRIS OFFICIAL FAMILY DRIVE</div>
                        <div style="font-size: 110px; line-height: 1; color: #111; margin: 4px 0;">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <div style="color: #666; font-size: 9px; font-weight: 700;">NMID: ID1020304050607</div>
                    </div>

                    <div style="font-size: 12px; color: var(--color-primary); cursor: pointer; margin-bottom: 16px;" onclick="toggleHowToPay()">
                        <i class="fa-solid fa-circle-question"></i> Cara bayar
                    </div>

                    <div id="how-to-pay-box" style="display: none; background: rgba(255,255,255,0.03); border: 1px solid var(--color-hairline); border-radius: 6px; padding: 10px; font-size: 11px; text-align: left; margin-bottom: 16px; color: var(--color-muted);">
                        1. Buka aplikasi e-wallet (GoPay/ShopeePay) atau M-Banking Anda.<br>
                        2. Pilih menu <strong>Scan QRIS</strong>.<br>
                        3. Arahkan kamera ke QR di atas.<br>
                        4. Periksa nominal <strong>Rp <?= number_format($rental['total_harga'], 0, ',', '.') ?></strong> dan tekan Konfirmasi.
                    </div>
                </div>

                <button type="button" class="midtrans-btn" onclick="submitCustomPayment('Gopay/QRIS')">
                    Saya sudah bayar
                </button>
            </div>

            <!-- SCREEN 5: Virtual Account Display -->
            <div id="screen-va" class="midtrans-screen">
                <div class="midtrans-nav-back" onclick="switchMidtransScreen('screen-banks')">
                    <i class="fa-solid fa-arrow-left"></i> <span id="va-header-title">Virtual Account</span>
                </div>

                <div style="font-size: 12px; color: var(--color-muted); margin-bottom: 8px;">Nomor Virtual Account Anda:</div>

                <div class="va-copy-box">
                    <div>
                        <strong id="va-number-text" style="font-size: 18px; font-family: monospace; color: var(--color-ink); letter-spacing: 1px;">8010 9988 7766</strong>
                        <span id="va-bank-name" style="display: block; font-size: 10px; color: var(--color-muted); margin-top: 2px;">Bank BCA</span>
                    </div>
                    <button type="button" onclick="copyVaNumber()" style="background: var(--color-primary); color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">
                        Salin
                    </button>
                </div>

                <div style="font-size: 12px; color: var(--color-body); line-height: 1.5; margin-bottom: 16px;">
                    Transfer persis sesuai tagihan ke nomor Virtual Account di atas melalui M-Banking atau ATM. Transaksi diverifikasi otomatis.
                </div>

                <button type="button" class="midtrans-btn" onclick="submitCustomPayment('Virtual Account')">
                    Saya sudah bayar
                </button>
            </div>

        </div>

    </div>
</div>

<script type="text/javascript">
    const snapToken = "<?= htmlspecialchars($snap_token ?? '') ?>";
    const idSewa = <?= $id_sewa ?>;
    const transactionId = "<?= htmlspecialchars($transaction_id) ?>";

    // Timer logic
    let timerSeconds = 3599;
    setInterval(() => {
        if (timerSeconds > 0) {
            timerSeconds--;
            const m = String(Math.floor(timerSeconds / 60)).padStart(2, '0');
            const s = String(timerSeconds % 60).padStart(2, '0');
            const el = document.getElementById('countdown-timer');
            if (el) el.textContent = `00:${m}:${s}`;
        }
    }, 1000);

    document.getElementById('pay-button').onclick = function() {
        if (typeof snap !== 'undefined' && snap.pay && snapToken && !snapToken.startsWith('SNAP-MOCK-')) {
            snap.pay(snapToken, {
                onSuccess: function(result) {
                    window.location.href = 'checkout_process.php?action=success&id_sewa=' + idSewa + '&transaction_id=' + (result.order_id || transactionId) + '&metode=' + encodeURIComponent(result.payment_type || 'Midtrans Snap');
                },
                onPending: function(result) {
                    window.location.href = 'checkout_process.php?action=pending&id_sewa=' + idSewa + '&metode=' + encodeURIComponent(result.payment_type || 'Midtrans Snap');
                },
                onError: function(result) {
                    alert('Pembayaran Midtrans gagal atau dibatalkan.');
                },
                onClose: function() {
                    window.location.href = 'payment.php?id_sewa=' + idSewa;
                }
            });
        } else {
            // Open custom Ferrari-styled Midtrans Multi-Screen Modal
            document.getElementById('midtrans-custom-modal').style.display = 'flex';
            switchMidtransScreen('screen-menu');
        }
    };

    function closeMidtransModal() {
        document.getElementById('midtrans-custom-modal').style.display = 'none';
    }

    function switchMidtransScreen(screenId) {
        document.querySelectorAll('.midtrans-screen').forEach(scr => scr.classList.remove('active'));
        const target = document.getElementById(screenId);
        if (target) target.classList.add('active');
    }

    function openQrisScreen(title) {
        document.getElementById('qris-header-title').textContent = title;
        switchMidtransScreen('screen-qris');
    }

    function openVaScreen(bankTitle, vaNum) {
        document.getElementById('va-header-title').textContent = bankTitle;
        document.getElementById('va-bank-name').textContent = bankTitle;
        document.getElementById('va-number-text').textContent = vaNum.replace(/(\d{4})/g, '$1 ').trim();
        switchMidtransScreen('screen-va');
    }

    function toggleHowToPay() {
        const box = document.getElementById('how-to-pay-box');
        if (box) box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }

    function formatCardNum(input) {
        let val = input.value.replace(/\D/g, '');
        val = val.replace(/(\d{4})/g, '$1 - ').trim();
        if (val.endsWith(' -')) val = val.slice(0, -3);
        input.value = val;
    }

    function copyVaNumber() {
        const num = document.getElementById('va-number-text').textContent.replace(/\s/g, '');
        navigator.clipboard.writeText(num);
        alert('Nomor Virtual Account berhasil disalin: ' + num);
    }

    function submitCustomPayment(methodName) {
        window.location.href = 'checkout_process.php?action=success&id_sewa=' + idSewa + '&transaction_id=' + encodeURIComponent(transactionId) + '&metode=' + encodeURIComponent('Midtrans (' + methodName + ')');
    }

    // Auto trigger payment modal if requested in URL
    if (window.location.search.includes('auto_pay=1')) {
        setTimeout(() => {
            const payBtn = document.getElementById('pay-button');
            if (payBtn) payBtn.click();
        }, 300);
    }
</script>

<?php require_once 'includes/footer.php'; ?>
