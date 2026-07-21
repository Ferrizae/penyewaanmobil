<?php
// payment.php
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
    header("Location: history.php");
    exit;
}

$rental = null;
$payment = null;
$db_connected = false;
$success_msg = '';
$error_msg = '';

// 1. Fetch rental and payment details
try {
    if (isset($pdo)) {
        // Fetch rental
        $stmt = $pdo->prepare("
            SELECT s.*, m.nama_mobil, m.merk, m.harga_sewa_per_hari, m.foto, m.plat_nomor, u.nama, u.email
            FROM penyewaan s
            JOIN mobil m ON s.id_mobil = m.id_mobil
            JOIN users u ON s.id_user = u.id_user
            WHERE s.id_sewa = ? AND s.id_user = ?
        ");
        $stmt->execute([$id_sewa, $_SESSION['id_user']]);
        $rental = $stmt->fetch();
        
        if ($rental) {
            $db_connected = true;
            // Fetch payment
            $stmt_pay = $pdo->prepare("SELECT * FROM pembayaran WHERE id_sewa = ? ORDER BY id_pembayaran DESC LIMIT 1");
            $stmt_pay->execute([$id_sewa]);
            $payment = $stmt_pay->fetch();
        }
    }
} catch (PDOException $e) {
    $db_connected = false;
}

// Fallback to session mockup if offline
if (!$db_connected || !$rental) {
    if (isset($_SESSION['mock_rentals'][$id_sewa]) && $_SESSION['mock_rentals'][$id_sewa]['id_user'] == $_SESSION['id_user']) {
        $rental = $_SESSION['mock_rentals'][$id_sewa];
        $rental['merk'] = isset($rental['merk']) ? $rental['merk'] : 'Toyota';
        $rental['foto'] = isset($rental['foto']) ? $rental['foto'] : 'avanza.jpg';
        $rental['plat_nomor'] = 'B ' . rand(10, 999) . ' RM';
        $rental['nama'] = $_SESSION['nama'];
        $rental['email'] = $_SESSION['email'];
        $rental['harga_sewa_per_hari'] = $rental['total_harga'] / $rental['lama_sewa'];
        
        $payment = $_SESSION['mock_payments'][$id_sewa] ?? null;
    } else {
        header("Location: history.php");
        exit;
    }
}

// If rental is already approved/paid, redirect to history with success
if ($rental['status_sewa'] === 'sudah_bayar' || $rental['status_sewa'] === 'diambil' || $rental['status_sewa'] === 'kembali') {
    header("Location: history.php");
    exit;
}

// 2. Handle upload proof of payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_bayar'])) {
    $file = $_FILES['bukti_bayar'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_msg = "Gagal mengunggah file. Silakan coba lagi.";
    } else {
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_name = $file['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $error_msg = "Format file tidak valid. Hanya JPG, JPEG, PNG, dan PDF yang diperbolehkan.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $error_msg = "Ukuran file terlalu besar. Maksimal ukuran file adalah 2MB.";
        } else {
            // Success validation, move file
            $new_filename = 'bukti_sewa_' . $id_sewa . '_' . time() . '.' . $file_ext;
            $upload_path = 'uploads/bukti_pembayaran/' . $new_filename;
            
            // Ensure folder exists (failsafe)
            if (!is_dir('uploads/bukti_pembayaran')) {
                mkdir('uploads/bukti_pembayaran', 0777, true);
            }
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                if ($db_connected) {
                    try {
                        // Update pembayaran
                        $stmt_up = $pdo->prepare("UPDATE pembayaran SET bukti_pembayaran = ? WHERE id_sewa = ? AND status_pembayaran = 'pending'");
                        $stmt_up->execute([$new_filename, $id_sewa]);
                        
                        $success_msg = "Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.";
                        
                        // Re-fetch payment details
                        $stmt_pay = $pdo->prepare("SELECT * FROM pembayaran WHERE id_sewa = ? ORDER BY id_pembayaran DESC LIMIT 1");
                        $stmt_pay->execute([$id_sewa]);
                        $payment = $stmt_pay->fetch();
                    } catch (PDOException $e) {
                        $error_msg = "Gagal menyimpan data ke database: " . $e->getMessage();
                    }
                } else {
                    // Mock session update
                    $_SESSION['mock_payments'][$id_sewa]['bukti_pembayaran'] = $new_filename;
                    $payment = $_SESSION['mock_payments'][$id_sewa];
                    $success_msg = "Mock: Bukti pembayaran berhasil diunggah.";
                }
            } else {
                $error_msg = "Gagal memindahkan file ke direktori server.";
            }
        }
    }
}

$page_title = "Pembayaran Sewa Mobil";
require_once 'includes/header.php';
?>

<section class="section-band" style="background-color: var(--color-canvas); padding: var(--spacing-lg) 0;">
    <div class="grid-container" style="max-width: 900px;">
        
        <!-- Back Navigation Link -->
        <a href="history.php" style="color: var(--color-muted); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 8px; margin-bottom: var(--spacing-md);">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat Sewa
        </a>

        <h2 class="display-lg" style="margin-bottom: var(--spacing-sm); text-transform: uppercase;">Status Pembayaran</h2>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-ferrari success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= htmlspecialchars($success_msg) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-ferrari error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= htmlspecialchars($error_msg) ?></span>
            </div>
        <?php endif; ?>

        <div class="grid-2-col">
            
            <!-- Midtrans Payment Gateway Box -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline); display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <h3 class="title-sm" style="border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs); text-transform: uppercase; display: flex; justify-content: space-between; align-items: center;">
                        <span>Midtrans Payment</span>
                        <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-warning); color: var(--color-semantic-warning); font-size: 11px;">Pending</span>
                    </h3>
                    
                    <div style="margin-bottom: var(--spacing-sm);">
                        <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Gerbang Pembayaran Official</span>
                        <div style="font-size: 16px; font-weight: 600; color: var(--color-ink); display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-bolt" style="color: var(--color-primary);"></i>
                            <?= htmlspecialchars($payment['metode_pembayaran'] ?? 'Midtrans Snap Gateway') ?>
                        </div>
                    </div>

                    <div style="margin-bottom: var(--spacing-md); padding: var(--spacing-xs); background-color: rgba(255, 255, 255, 0.02); border: 1px solid var(--color-hairline);">
                        <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; display: block; margin-bottom: 4px;">Total Tagihan Pembayaran</span>
                        <span style="font-size: 28px; font-weight: 700; color: var(--color-primary);">Rp <?= number_format($rental['total_harga'], 0, ',', '.') ?></span>
                        <?php if (!empty($payment['transaction_id'])): ?>
                            <span style="font-size: 11px; color: var(--color-muted); display: block; margin-top: 4px; font-family: monospace;">Order ID: <?= htmlspecialchars($payment['transaction_id']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div style="font-size: 13px; color: var(--color-body); line-height: 1.6; margin-bottom: var(--spacing-md);">
                        <p style="margin-bottom: var(--spacing-xs);">Anda dapat menyelesaikan pembayaran online secara otomatis menggunakan <strong>Midtrans Payment Gateway</strong> (Virtual Account, QRIS/GoPay, Kartu Kredit).</p>
                        
                        <a href="checkout.php?id_sewa=<?= $id_sewa ?>&auto_pay=1" class="btn-primary-ferrari" style="display: flex; align-items: center; justify-content: center; gap: 10px; padding: 12px; width: 100%; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-credit-card"></i> Buka Midtrans Payment Gateway
                        </a>
                    </div>
                </div>

                <div style="font-size: 11px; color: var(--color-muted); padding-top: var(--spacing-xs); border-top: 1px dashed var(--color-hairline);">
                    <i class="fa-solid fa-clock"></i> Transaksi akan kedaluwarsa secara otomatis jika tidak dibayar dalam 24 jam.
                </div>
            </div>

            <!-- Manual Proof Upload (Alternative) -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline);">
                <h3 class="title-sm" style="border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs); text-transform: uppercase;">Alternatif: Upload Bukti Manual</h3>

                <p style="font-size: 12px; color: var(--color-muted); margin-bottom: var(--spacing-xs);">Jika Anda melakukan pembayaran via transfer bank manual, silakan unggah struk/bukti di bawah ini:</p>

                <!-- Upload Form -->
                <form action="payment.php?id_sewa=<?= $id_sewa ?>" method="POST" enctype="multipart/form-data" style="margin-bottom: var(--spacing-sm);">
                    
                    <div style="margin-bottom: var(--spacing-sm);">
                        <label class="form-label-dark" for="bukti_bayar">Pilih File Bukti Transaksi</label>
                        <input type="file" name="bukti_bayar" id="bukti_bayar" class="form-input-dark" style="padding-top: var(--spacing-xxs); height: auto;" required>
                        <small style="color: var(--color-muted); font-size: 11px; display: block; margin-top: 4px;">Format: JPG, JPEG, PNG, PDF. Maksimal 2MB.</small>
                    </div>

                    <button type="submit" class="btn-outline-dark-ferrari" style="width: 100%; height: 42px;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> &nbsp; Unggah Bukti Manual
                    </button>
                </form>

                <!-- Current Receipt Status / Preview -->
                <div style="border-top: 1px solid var(--color-hairline); padding-top: var(--spacing-sm); margin-top: var(--spacing-sm);">
                    <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: var(--color-ink); display: block; margin-bottom: var(--spacing-xxs);">Status Bukti Upload</span>
                    
                    <?php if (!empty($payment['bukti_pembayaran'])): ?>
                        <div class="badge-pill-ferrari" style="border-color: var(--color-semantic-warning); color: var(--color-semantic-warning); margin-bottom: var(--spacing-xs);">
                            <i class="fa-solid fa-spinner fa-spin"></i> Menunggu Verifikasi Admin
                        </div>

                        <div style="background-color: rgba(0,0,0,0.2); border: 1px solid var(--color-hairline); padding: var(--spacing-xxs); text-align: center;">
                            <span style="font-size: 11px; color: var(--color-muted); display: block; margin-bottom: 4px;">Bukti yang telah diunggah:</span>
                            
                            <?php 
                            $file_ext = strtolower(pathinfo($payment['bukti_pembayaran'], PATHINFO_EXTENSION));
                            if ($file_ext === 'pdf'): ?>
                                <div style="padding: var(--spacing-xs); background: #111; color: var(--color-ink); border: 1px solid var(--color-hairline);">
                                    <i class="fa-solid fa-file-pdf" style="font-size: 32px; color: var(--color-primary); margin-bottom: 4px;"></i>
                                    <span style="display: block; font-size: 12px; font-weight: 500;"><?= htmlspecialchars($payment['bukti_pembayaran']) ?></span>
                                    <a href="uploads/bukti_pembayaran/<?= htmlspecialchars($payment['bukti_pembayaran']) ?>" target="_blank" class="btn-outline-dark-ferrari" style="height: 28px; padding: 0 12px; font-size: 10px; margin-top: var(--spacing-xxs);">Lihat PDF</a>
                                </div>
                            <?php else: ?>
                                <a href="uploads/bukti_pembayaran/<?= htmlspecialchars($payment['bukti_pembayaran']) ?>" target="_blank" title="Klik untuk melihat resolusi penuh">
                                    <img src="uploads/bukti_pembayaran/<?= htmlspecialchars($payment['bukti_pembayaran']) ?>" alt="Bukti Pembayaran" style="max-width: 100%; max-height: 180px; object-fit: contain; border: 1px solid var(--color-hairline);">
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="badge-pill-ferrari" style="border-color: var(--color-muted); color: var(--color-muted);">
                            Belum Ada Bukti Diunggah
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
