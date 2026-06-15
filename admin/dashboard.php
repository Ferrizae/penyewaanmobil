<?php
// admin/dashboard.php
require_once '../config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Must be admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$stats = [
    'total_cars' => 0,
    'active_rentals' => 0,
    'unpaid_rentals' => 0,
    'total_revenue' => 0.00
];
$recent_rentals = [];
$db_connected = false;

// 1. Fetch real statistics
try {
    if (isset($pdo)) {
        // Cars count
        $stats['total_cars'] = $pdo->query("SELECT COUNT(*) FROM mobil")->fetchColumn();
        
        // Active rentals count (sudah_bayar, diambil)
        $stmt_act = $pdo->query("SELECT COUNT(*) FROM penyewaan WHERE status_sewa IN ('sudah_bayar', 'diambil')");
        $stats['active_rentals'] = $stmt_act->fetchColumn();

        // Unpaid rentals
        $stmt_unp = $pdo->query("SELECT COUNT(*) FROM penyewaan WHERE status_sewa = 'belum_bayar'");
        $stats['unpaid_rentals'] = $stmt_unp->fetchColumn();

        // Total revenue
        $stmt_rev = $pdo->query("SELECT SUM(jumlah_bayar) FROM pembayaran WHERE status_pembayaran = 'success'");
        $stats['total_revenue'] = (float)$stmt_rev->fetchColumn();

        // Recent rentals
        $stmt_rec = $pdo->query("
            SELECT s.*, m.nama_mobil, m.merk, u.nama as nama_pelanggan
            FROM penyewaan s
            JOIN mobil m ON s.id_mobil = m.id_mobil
            JOIN users u ON s.id_user = u.id_user
            ORDER BY s.id_sewa DESC LIMIT 5
        ");
        $recent_rentals = $stmt_rec->fetchAll();
        $db_connected = true;
    }
} catch (PDOException $e) {
    $db_connected = false;
}

// Fallback Mock Data if DB offline
if (!$db_connected) {
    $stats['total_cars'] = 4;
    
    // Simulate from mock session rentals
    $mock_rentals = $_SESSION['mock_rentals'] ?? [];
    foreach ($mock_rentals as $mr) {
        if (in_array($mr['status_sewa'], ['sudah_bayar', 'diambil'])) {
            $stats['active_rentals']++;
        }
        if ($mr['status_sewa'] === 'belum_bayar') {
            $stats['unpaid_rentals']++;
        }
        if ($mr['status_sewa'] === 'sudah_bayar') {
            $stats['total_revenue'] += $mr['total_harga'];
        }
        
        $recent_rentals[] = [
            'id_sewa' => $mr['id_sewa'],
            'nama_mobil' => $mr['nama_mobil'],
            'merk' => 'Ferrari',
            'nama_pelanggan' => $_SESSION['nama'] ?? 'Customer Demo',
            'tanggal_sewa' => $mr['tanggal_sewa'],
            'tanggal_kembali' => $mr['tanggal_kembali'],
            'total_harga' => $mr['total_harga'],
            'status_sewa' => $mr['status_sewa']
        ];
    }
    
    // Limit to recent 5
    usort($recent_rentals, function($a, $b) {
        return $b['id_sewa'] - $a['id_sewa'];
    });
    $recent_rentals = array_slice($recent_rentals, 0, 5);
}

// Setup Page Headers (we can adjust base_url because admin subfolder shifts paths)
$page_title = "Admin Dashboard";
// Let's load the header, temporarily overriding $base_url
$base_url = "../";
require_once '../includes/header.php';
?>

<section class="section-band" style="background-color: var(--color-canvas); min-height: 80vh; padding: var(--spacing-lg) 0;">
    <div class="grid-container">
        
        <?php if (!$db_connected): ?>
            <div class="alert-ferrari error" style="margin-bottom: var(--spacing-lg);">
                <i class="fa-solid fa-database"></i>
                <div>
                    <span style="font-weight: 600;">Catatan Offline:</span> Dashboard admin memuat data demonstrasi (mockup) karena database MySQL lokal Anda tidak terdeteksi aktif.
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: var(--spacing-md); display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xxxs);">Kondisi Sistem Real-Time</p>
                <h2 class="display-lg">Dashboard Admin</h2>
            </div>
            <span style="font-size: 13px; color: var(--color-muted);"><i class="fa-solid fa-clock"></i> Last updated: Just now</span>
        </div>

        <!-- Admin Stats Row -->
        <div class="grid-4-col" style="margin-bottom: var(--spacing-lg);">
            <!-- Stat 1 -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-sm); border: 1px solid var(--color-hairline);">
                <div class="spec-value" style="font-size: 48px; color: var(--color-ink);"><?= $stats['total_cars'] ?></div>
                <div class="spec-label" style="font-size: 11px;">Total Armada Mobil</div>
            </div>
            <!-- Stat 2 -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-sm); border: 1px solid var(--color-hairline); border-left: 3px solid var(--color-primary);">
                <div class="spec-value" style="font-size: 48px; color: var(--color-primary);"><?= $stats['active_rentals'] ?></div>
                <div class="spec-label" style="font-size: 11px;">Penyewaan Aktif</div>
            </div>
            <!-- Stat 3 -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-sm); border: 1px solid var(--color-hairline);">
                <div class="spec-value" style="font-size: 48px; color: var(--color-semantic-warning);"><?= $stats['unpaid_rentals'] ?></div>
                <div class="spec-label" style="font-size: 11px;">Belum Dibayar</div>
            </div>
            <!-- Stat 4 -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-sm); border: 1px solid var(--color-hairline);">
                <div class="spec-value" style="font-size: 48px; color: var(--color-semantic-success);">Rp <?= number_format($stats['total_revenue'], 0, ',', '.') ?></div>
                <div class="spec-label" style="font-size: 11px;">Total Pendapatan</div>
            </div>
        </div>

        <div class="grid-2-col" style="grid-template-columns: 2fr 1fr; gap: var(--spacing-md);">
            
            <!-- Recent Rentals Table -->
            <div style="background-color: var(--color-canvas-elevated); border: 1px solid var(--color-hairline); padding: var(--spacing-sm);">
                <h3 class="title-sm" style="text-transform: uppercase; border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs);">Aktivitas Penyewaan Terbaru</h3>
                
                <?php if (empty($recent_rentals)): ?>
                    <p style="color: var(--color-muted); text-align: center; padding: var(--spacing-md) 0;">Belum ada aktivitas penyewaan saat ini.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table-ferrari" style="margin-top: 0;">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Pelanggan</th>
                                    <th>Mobil</th>
                                    <th>Total Biaya</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_rentals as $rec): ?>
                                    <tr>
                                        <td style="font-weight: 600;">#SR-<?= str_pad($rec['id_sewa'], 3, '0', STR_PAD_LEFT) ?></td>
                                        <td style="color: var(--color-ink);"><?= htmlspecialchars($rec['nama_pelanggan']) ?></td>
                                        <td><?= htmlspecialchars($rec['merk'] . ' ' . $rec['nama_mobil']) ?></td>
                                        <td style="font-weight: 600;">Rp <?= number_format($rec['total_harga'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if ($rec['status_sewa'] === 'belum_bayar'): ?>
                                                <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-warning); color: var(--color-semantic-warning); padding: 2px 8px; font-size: 9px;">Unpaid</span>
                                            <?php elseif ($rec['status_sewa'] === 'sudah_bayar'): ?>
                                                <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-success); color: var(--color-semantic-success); padding: 2px 8px; font-size: 9px;">Paid</span>
                                            <?php elseif ($rec['status_sewa'] === 'diambil'): ?>
                                                <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-info); color: var(--color-semantic-info); padding: 2px 8px; font-size: 9px;">Active</span>
                                            <?php elseif ($rec['status_sewa'] === 'kembali'): ?>
                                                <span class="badge-pill-ferrari" style="border-color: var(--color-body); color: var(--color-body); padding: 2px 8px; font-size: 9px;">Done</span>
                                            <?php else: ?>
                                                <span class="badge-pill-ferrari" style="border-color: var(--color-muted); color: var(--color-muted); padding: 2px 8px; font-size: 9px;">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Admin Panel Controls -->
            <div style="background-color: var(--color-canvas-elevated); border: 1px solid var(--color-hairline); padding: var(--spacing-sm); height: fit-content;">
                <h3 class="title-sm" style="text-transform: uppercase; border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs);">Akses Cepat Pengelolaan</h3>
                
                <div style="display: flex; flex-direction: column; gap: var(--spacing-xs); margin-top: var(--spacing-xs);">
                    <a href="manage_cars.php" class="btn-outline-dark-ferrari" style="width: 100%; text-align: center; font-size: 12px; justify-content: center; height: 40px; display: inline-flex; align-items: center;">
                        <i class="fa-solid fa-car-side" style="margin-right: 8px;"></i> Kelola Armada Mobil
                    </a>
                    
                    <a href="manage_rentals.php" class="btn-outline-dark-ferrari" style="width: 100%; text-align: center; font-size: 12px; justify-content: center; height: 40px; display: inline-flex; align-items: center;">
                        <i class="fa-solid fa-handshake" style="margin-right: 8px;"></i> Konfirmasi Handover & Kembali
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<?php 
// Restore base_url and include footer
$base_url = "../";
require_once '../includes/footer.php'; 
?>
