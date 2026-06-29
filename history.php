<?php
// history.php
require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure logged in and is user
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$rentals = [];
$db_connected = false;

// 1. Fetch rental list
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("
            SELECT s.*, m.nama_mobil, m.merk, m.foto, m.plat_nomor, 
                   p.transaction_id, p.metode_pembayaran, p.status_pembayaran, p.bukti_pembayaran
            FROM penyewaan s
            JOIN mobil m ON s.id_mobil = m.id_mobil
            LEFT JOIN (
                SELECT p1.* FROM pembayaran p1
                INNER JOIN (
                    SELECT MAX(id_pembayaran) as max_id FROM pembayaran GROUP BY id_sewa
                ) p2 ON p1.id_pembayaran = p2.max_id
            ) p ON s.id_sewa = p.id_sewa
            WHERE s.id_user = ?
            ORDER BY s.id_sewa DESC
        ");
        $stmt->execute([$id_user]);
        $rentals = $stmt->fetchAll();
        $db_connected = true;
    }
} catch (PDOException $e) {
    $db_connected = false;
}

// Fallback Mock Data if DB offline
if (!$db_connected) {
    if (isset($_SESSION['mock_rentals'])) {
        // Filter session rentals by current user
        $rentals = array_filter($_SESSION['mock_rentals'], function($r) use ($id_user) {
            return $r['id_user'] == $id_user;
        });
        // Sort DESC
        usort($rentals, function($a, $b) {
            return $b['id_sewa'] - $a['id_sewa'];
        });
        // Enrich keys
        foreach ($rentals as &$r) {
            $r['merk'] = isset($r['merk']) ? $r['merk'] : 'Toyota';
            $r['foto'] = isset($r['foto']) ? $r['foto'] : 'avanza.jpg';
            $r['plat_nomor'] = 'DR ' . rand(1000, 9999) . ' SA';
            $mock_pay = $_SESSION['mock_payments'][$r['id_sewa']] ?? null;
            $r['transaction_id'] = $mock_pay ? $mock_pay['transaction_id'] : '-';
            $r['status_pembayaran'] = $mock_pay ? $mock_pay['status_pembayaran'] : null;
            $r['bukti_pembayaran'] = $mock_pay ? $mock_pay['bukti_pembayaran'] : null;
        }
        unset($r); // unset reference
    }
}

$page_title = "Riwayat Penyewaan Anda";
require_once 'includes/header.php';
?>

<section class="section-band" style="background-color: var(--color-canvas); min-height: 80vh; padding: var(--spacing-lg) 0;">
    <div class="grid-container">
        
        <?php if (isset($_GET['pay_success'])): ?>
            <div class="alert-ferrari success">
                <i class="fa-solid fa-circle-check"></i>
                <span>Pembayaran berhasil diproses! Transaksi Anda sedang kami konfirmasi. Silakan menunggu pengambilan mobil.</span>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['upload_success'])): ?>
            <div class="alert-ferrari success">
                <i class="fa-solid fa-circle-check"></i>
                <span>Bukti pembayaran berhasil diunggah! Transaksi Anda sedang dalam verifikasi admin.</span>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: var(--spacing-md);">
            <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xxxs);">Portal Pelanggan</p>
            <h2 class="display-lg">Riwayat Penyewaan Anda</h2>
        </div>

        <?php if (empty($rentals)): ?>
            <div style="text-align: center; padding: var(--spacing-xl) 0; border: 1px dashed var(--color-hairline); background-color: var(--color-canvas-elevated);">
                <i class="fa-solid fa-receipt" style="font-size: 48px; color: var(--color-muted); margin-bottom: var(--spacing-xs);"></i>
                <h3 class="title-sm" style="color: var(--color-ink); margin-bottom: var(--spacing-xxxs);">Belum Ada Transaksi</h3>
                <p style="color: var(--color-muted);">Anda belum pernah melakukan pemesanan sewa mobil.</p>
                <a href="index.php" class="btn-primary-ferrari" style="margin-top: var(--spacing-sm);">Sewa Mobil Sekarang</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto; background-color: var(--color-canvas-elevated); border: 1px solid var(--color-hairline);">
                <table class="table-ferrari">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Mobil</th>
                            <th>Jadwal Sewa</th>
                            <th>Durasi</th>
                            <th>Total Biaya</th>
                            <th>Kode TRX</th>
                            <th>Status Transaksi</th>
                            <th style="text-align: center;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rentals as $rent): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--color-ink);">#SR-<?= str_pad($rent['id_sewa'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
                                        <img src="assets/img/<?= htmlspecialchars($rent['foto']) ?>" alt="<?= htmlspecialchars($rent['nama_mobil']) ?>" style="width: 60px; height: 36px; object-fit: cover; border: 1px solid var(--color-hairline);">
                                        <div>
                                            <div style="font-weight: 600; color: var(--color-ink);"><?= htmlspecialchars($rent['merk'] . ' ' . $rent['nama_mobil']) ?></div>
                                            <span style="font-size: 11px; color: var(--color-muted);"><?= htmlspecialchars($rent['plat_nomor']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size: 13px;">
                                    <div style="color: var(--color-ink);"><?= date('d M Y', strtotime($rent['tanggal_sewa'])) ?></div>
                                    <span style="color: var(--color-muted); font-size: 11px;">s/d <?= date('d M Y', strtotime($rent['tanggal_kembali'])) ?></span>
                                </td>
                                <td style="color: var(--color-body);"><?= htmlspecialchars($rent['lama_sewa']) ?> Hari</td>
                                <td style="font-weight: 600; color: var(--color-ink);">Rp <?= number_format($rent['total_harga'], 0, ',', '.') ?></td>
                                <td style="font-family: monospace; font-size: 12px; color: var(--color-muted);"><?= !empty($rent['transaction_id']) ? htmlspecialchars($rent['transaction_id']) : '-' ?></td>
                                <td>
                                    <?php if ($rent['status_sewa'] === 'belum_bayar'): ?>
                                        <?php if (!empty($rent['bukti_pembayaran']) && $rent['status_pembayaran'] === 'pending'): ?>
                                            <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-info); color: var(--color-semantic-info);">Menunggu Verifikasi</span>
                                        <?php elseif (empty($rent['bukti_pembayaran']) && $rent['status_pembayaran'] === 'pending'): ?>
                                            <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-warning); color: var(--color-semantic-warning);">Belum Bayar (Pending)</span>
                                        <?php elseif ($rent['status_pembayaran'] === 'cancel'): ?>
                                            <span class="badge-pill-ferrari" style="border-color: var(--color-primary); color: var(--color-primary);">Pembayaran Ditolak</span>
                                        <?php else: ?>
                                            <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-warning); color: var(--color-semantic-warning);">Belum Bayar</span>
                                        <?php endif; ?>
                                    <?php elseif ($rent['status_sewa'] === 'sudah_bayar'): ?>
                                        <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-success); color: var(--color-semantic-success);">Sudah Bayar</span>
                                    <?php elseif ($rent['status_sewa'] === 'diambil'): ?>
                                        <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-info); color: var(--color-semantic-info);">Mobil Diambil</span>
                                    <?php elseif ($rent['status_sewa'] === 'kembali'): ?>
                                        <span class="badge-pill-ferrari" style="border-color: var(--color-body); color: var(--color-body);">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge-pill-ferrari" style="border-color: var(--color-muted); color: var(--color-muted);">Dibatalkan</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($rent['status_sewa'] === 'belum_bayar'): ?>
                                        <?php if (!empty($rent['bukti_pembayaran']) && $rent['status_pembayaran'] === 'pending'): ?>
                                            <a href="payment.php?id_sewa=<?= $rent['id_sewa'] ?>" class="btn-outline-dark-ferrari" style="height: 32px; padding: 0 var(--spacing-xs); font-size: 11px; border-color: var(--color-semantic-info); color: var(--color-semantic-info);">Detail Bukti</a>
                                        <?php elseif (empty($rent['bukti_pembayaran']) && $rent['status_pembayaran'] === 'pending'): ?>
                                            <a href="payment.php?id_sewa=<?= $rent['id_sewa'] ?>" class="btn-primary-ferrari" style="height: 32px; padding: 0 var(--spacing-xs); font-size: 11px;">Upload Bukti</a>
                                        <?php elseif ($rent['status_pembayaran'] === 'cancel'): ?>
                                            <a href="checkout.php?id_sewa=<?= $rent['id_sewa'] ?>" class="btn-primary-ferrari" style="height: 32px; padding: 0 var(--spacing-xs); font-size: 11px; background-color: var(--color-primary);">Bayar Ulang</a>
                                        <?php else: ?>
                                            <a href="checkout.php?id_sewa=<?= $rent['id_sewa'] ?>" class="btn-primary-ferrari" style="height: 32px; padding: 0 var(--spacing-xs); font-size: 11px;">Bayar</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--color-muted);"><i class="fa-solid fa-circle-check"></i> Terkonfirmasi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
