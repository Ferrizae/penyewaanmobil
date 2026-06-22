<?php
// admin/manage_rentals.php
require_once '../config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Must be admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$db_connected = false;
$rentals = [];
$success_msg = '';
$error_msg = '';

$query_rentals = "
    SELECT s.*, m.nama_mobil, m.merk, m.plat_nomor, u.nama as nama_pelanggan, u.no_hp,
           p.id_pembayaran, p.metode_pembayaran, p.bukti_pembayaran, p.status_pembayaran, p.jumlah_bayar, p.tanggal_pembayaran
    FROM penyewaan s
    JOIN mobil m ON s.id_mobil = m.id_mobil
    JOIN users u ON s.id_user = u.id_user
    LEFT JOIN (
        SELECT p1.* FROM pembayaran p1
        INNER JOIN (
            SELECT MAX(id_pembayaran) as max_id FROM pembayaran GROUP BY id_sewa
        ) p2 ON p1.id_pembayaran = p2.max_id
    ) p ON s.id_sewa = p.id_sewa
    ORDER BY s.id_sewa DESC
";

// Load rentals
try {
    if (isset($pdo)) {
        $stmt = $pdo->query($query_rentals);
        $rentals = $stmt->fetchAll();
        $db_connected = true;
    }
} catch (PDOException $e) {
    $db_connected = false;
}

// Fallback Mock Data if DB offline
if (!$db_connected) {
    $rentals = $_SESSION['mock_rentals'] ?? [];
    foreach ($rentals as &$r) {
        $r['merk'] = 'Ferrari';
        $r['plat_nomor'] = 'B ' . rand(10, 999) . ' RM';
        $r['nama_pelanggan'] = $_SESSION['nama'] ?? 'Customer Demo';
        $r['no_hp'] = '081234567890';
        
        $mock_pay = $_SESSION['mock_payments'][$r['id_sewa']] ?? null;
        if ($mock_pay) {
            $r['id_pembayaran'] = $mock_pay['id_pembayaran'];
            $r['metode_pembayaran'] = $mock_pay['metode_pembayaran'];
            $r['bukti_pembayaran'] = $mock_pay['bukti_pembayaran'];
            $r['status_pembayaran'] = $mock_pay['status_pembayaran'];
            $r['jumlah_bayar'] = $mock_pay['jumlah_bayar'];
            $r['tanggal_pembayaran'] = $mock_pay['tanggal_pembayaran'];
        } else {
            $r['id_pembayaran'] = null;
            $r['metode_pembayaran'] = null;
            $r['bukti_pembayaran'] = null;
            $r['status_pembayaran'] = null;
            $r['jumlah_bayar'] = null;
            $r['tanggal_pembayaran'] = null;
        }
    }
    unset($r);
}

// 1. Process Handover (belum_bayar -> sudah_bayar (if manual payment verify) OR sudah_bayar -> diambil)
if (isset($_GET['action']) && $_GET['action'] === 'handover') {
    $id_sewa = (int)$_GET['id_sewa'];
    
    if ($db_connected) {
        try {
            $stmt = $pdo->prepare("UPDATE penyewaan SET status_sewa = 'diambil' WHERE id_sewa = ?");
            $stmt->execute([$id_sewa]);
            $success_msg = "Mobil berhasil diserahkan kepada pelanggan.";
            
            // Reload
            $stmt = $pdo->query($query_rentals);
            $rentals = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error_msg = "Gagal memproses penyerahan: " . $e->getMessage();
        }
    } else {
        // Mock Update
        foreach ($_SESSION['mock_rentals'] as &$mr) {
            if ($mr['id_sewa'] === $id_sewa) {
                $mr['status_sewa'] = 'diambil';
            }
        }
        unset($mr);
        $rentals = $_SESSION['mock_rentals'] ?? [];
        $success_msg = "Mock: Mobil berhasil diserahkan.";
    }
}

// 2. Process Return and Denda (diambil -> kembali)
if (isset($_POST['action']) && $_POST['action'] === 'return') {
    $id_sewa = (int)$_POST['id_sewa'];
    $kondisi = $_POST['kondisi_mobil'];
    $denda = (float)$_POST['denda'];
    $catatan = trim($_POST['catatan']);
    $tanggal_kembali = date('Y-m-d');

    if ($db_connected) {
        try {
            $pdo->beginTransaction();

            // Insert into pengembalian
            $stmt_ret = $pdo->prepare("INSERT INTO pengembalian (id_sewa, tanggal_pengembalian, kondisi_mobil, denda, catatan) VALUES (?, ?, ?, ?, ?)");
            $stmt_ret->execute([$id_sewa, $tanggal_kembali, $kondisi, $denda, $catatan]);

            // Update rental status
            $stmt_rent = $pdo->prepare("UPDATE penyewaan SET status_sewa = 'kembali' WHERE id_sewa = ?");
            $stmt_rent->execute([$id_sewa]);

            // Get id_mobil to update car availability back to 'tersedia'
            $stmt_car_id = $pdo->prepare("SELECT id_mobil FROM penyewaan WHERE id_sewa = ?");
            $stmt_car_id->execute([$id_sewa]);
            $id_mobil = $stmt_car_id->fetchColumn();

            $stmt_car = $pdo->prepare("UPDATE mobil SET status = 'tersedia' WHERE id_mobil = ?");
            $stmt_car->execute([$id_mobil]);

            $pdo->commit();
            $success_msg = "Proses pengembalian mobil selesai.";
            
            // Reload
            $stmt = $pdo->query($query_rentals);
            $rentals = $stmt->fetchAll();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_msg = "Gagal memproses pengembalian: " . $e->getMessage();
        }
    } else {
        // Mock Update
        foreach ($_SESSION['mock_rentals'] as &$mr) {
            if ($mr['id_sewa'] === $id_sewa) {
                $mr['status_sewa'] = 'kembali';
                // Reset mock car availability if matches
                if (isset($_SESSION['mock_cars'])) {
                    foreach ($_SESSION['mock_cars'] as &$mc) {
                        if ($mc['id_mobil'] === $mr['id_mobil']) {
                            $mc['status'] = 'tersedia';
                        }
                    }
                    unset($mc);
                }
            }
        }
        unset($mr);

        // Store mock return info
        $_SESSION['mock_returns'][$id_sewa] = [
            'id_pengembalian' => count($_SESSION['mock_returns'] ?? []) + 1,
            'id_sewa' => $id_sewa,
            'tanggal_pengembalian' => $tanggal_kembali,
            'kondisi_mobil' => $kondisi,
            'denda' => $denda,
            'catatan' => $catatan
        ];

        $rentals = $_SESSION['mock_rentals'] ?? [];
        $success_msg = "Mock: Pengembalian mobil selesai diproses.";
    }
}

// 3. Process Payment Verification (Approve or Reject)
if (isset($_POST['action']) && $_POST['action'] === 'verify_payment') {
    $id_sewa = (int)$_POST['id_sewa'];
    $verification_status = $_POST['status_verifikasi']; // 'approve' or 'reject'
    
    if ($db_connected) {
        try {
            $pdo->beginTransaction();
            
            if ($verification_status === 'approve') {
                // Update pembayaran to success
                $stmt_pay = $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'success' WHERE id_sewa = ? AND status_pembayaran = 'pending'");
                $stmt_pay->execute([$id_sewa]);
                
                // Update rental status to sudah_bayar
                $stmt_rent = $pdo->prepare("UPDATE penyewaan SET status_sewa = 'sudah_bayar' WHERE id_sewa = ?");
                $stmt_rent->execute([$id_sewa]);
                
                $success_msg = "Pembayaran sewa berhasil disetujui.";
            } else {
                // Find filename of proof
                $stmt_file = $pdo->prepare("SELECT bukti_pembayaran FROM pembayaran WHERE id_sewa = ? AND status_pembayaran = 'pending'");
                $stmt_file->execute([$id_sewa]);
                $filename = $stmt_file->fetchColumn();
                if (!empty($filename) && file_exists('../uploads/bukti_pembayaran/' . $filename)) {
                    @unlink('../uploads/bukti_pembayaran/' . $filename);
                }

                // Update pembayaran status to cancel and null the proof
                $stmt_pay = $pdo->prepare("UPDATE pembayaran SET status_pembayaran = 'cancel', bukti_pembayaran = NULL WHERE id_sewa = ? AND status_pembayaran = 'pending'");
                $stmt_pay->execute([$id_sewa]);
                
                $success_msg = "Pembayaran sewa berhasil ditolak dan bukti transfer dihapus.";
            }
            
            $pdo->commit();
            
            // Reload
            $stmt = $pdo->query($query_rentals);
            $rentals = $stmt->fetchAll();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_msg = "Gagal memproses verifikasi pembayaran: " . $e->getMessage();
        }
    } else {
        // Mock Update
        if ($verification_status === 'approve') {
            $_SESSION['mock_payments'][$id_sewa]['status_pembayaran'] = 'success';
            foreach ($_SESSION['mock_rentals'] as &$mr) {
                if ($mr['id_sewa'] === $id_sewa) {
                    $mr['status_sewa'] = 'sudah_bayar';
                }
            }
            unset($mr);
            $success_msg = "Mock: Pembayaran sewa berhasil disetujui.";
        } else {
            // Reject
            $filename = $_SESSION['mock_payments'][$id_sewa]['bukti_pembayaran'] ?? '';
            if (!empty($filename) && file_exists('../uploads/bukti_pembayaran/' . $filename)) {
                @unlink('../uploads/bukti_pembayaran/' . $filename);
            }
            $_SESSION['mock_payments'][$id_sewa]['status_pembayaran'] = 'cancel';
            $_SESSION['mock_payments'][$id_sewa]['bukti_pembayaran'] = null;
            $success_msg = "Mock: Pembayaran sewa berhasil ditolak.";
        }
        
        // Re-enrich mock rentals
        $rentals = $_SESSION['mock_rentals'] ?? [];
        foreach ($rentals as &$r) {
            $r['merk'] = 'Ferrari';
            $r['plat_nomor'] = 'B ' . rand(10, 999) . ' RM';
            $r['nama_pelanggan'] = $_SESSION['nama'] ?? 'Customer Demo';
            $r['no_hp'] = '081234567890';
            
            $mock_pay = $_SESSION['mock_payments'][$r['id_sewa']] ?? null;
            if ($mock_pay) {
                $r['id_pembayaran'] = $mock_pay['id_pembayaran'];
                $r['metode_pembayaran'] = $mock_pay['metode_pembayaran'];
                $r['bukti_pembayaran'] = $mock_pay['bukti_pembayaran'];
                $r['status_pembayaran'] = $mock_pay['status_pembayaran'];
                $r['jumlah_bayar'] = $mock_pay['jumlah_bayar'];
                $r['tanggal_pembayaran'] = $mock_pay['tanggal_pembayaran'];
            } else {
                $r['id_pembayaran'] = null;
                $r['metode_pembayaran'] = null;
                $r['bukti_pembayaran'] = null;
                $r['status_pembayaran'] = null;
                $r['jumlah_bayar'] = null;
                $r['tanggal_pembayaran'] = null;
            }
        }
        unset($r);
    }
}

$page_title = "Manajemen Transaksi Penyewaan";
$base_url = "../";
require_once '../includes/header.php';
?>

<section class="section-band" style="background-color: var(--color-canvas); min-height: 80vh; padding: var(--spacing-lg) 0;">
    <div class="grid-container">
        
        <!-- Back Navigation Link -->
        <a href="dashboard.php" style="color: var(--color-muted); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 8px; margin-bottom: var(--spacing-md);">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>

        <div style="margin-bottom: var(--spacing-md);">
            <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xxxs);">Portal Administrasi</p>
            <h2 class="display-lg">Manajemen Transaksi Penyewaan</h2>
        </div>

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

        <!-- Active Verification Modal/Overlay simulation -->
        <?php if (isset($_GET['verify_id'])): 
            $verify_id = (int)$_GET['verify_id'];
            $selected_rental = null;
            foreach ($rentals as $r) {
                if ($r['id_sewa'] === $verify_id) {
                    $selected_rental = $r;
                }
            }
        ?>
            <?php if ($selected_rental && !empty($selected_rental['bukti_pembayaran'])): ?>
                <div id="verifikasi" style="background-color: var(--color-canvas-elevated); border: 1px solid var(--color-semantic-info); padding: var(--spacing-md); margin-bottom: var(--spacing-md);">
                    <h3 class="title-sm" style="text-transform: uppercase; color: var(--color-semantic-info); margin-bottom: var(--spacing-xs);">Verifikasi Pembayaran #SR-<?= str_pad($selected_rental['id_sewa'], 4, '0', STR_PAD_LEFT) ?></h3>
                    
                    <div class="grid-2-col" style="margin-bottom: var(--spacing-sm);">
                        <!-- Payment details info -->
                        <div>
                            <div style="margin-bottom: var(--spacing-xs);">
                                <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; display: block;">Pelanggan</span>
                                <span style="color: var(--color-ink); font-weight: 600;"><?= htmlspecialchars($selected_rental['nama_pelanggan']) ?> (<?= htmlspecialchars($selected_rental['no_hp']) ?>)</span>
                            </div>
                            <div style="margin-bottom: var(--spacing-xs);">
                                <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; display: block;">Mobil</span>
                                <span style="color: var(--color-ink); font-weight: 600;"><?= htmlspecialchars($selected_rental['merk'] . ' ' . $selected_rental['nama_mobil']) ?> (<?= htmlspecialchars($selected_rental['plat_nomor']) ?>)</span>
                            </div>
                            <div style="margin-bottom: var(--spacing-xs);">
                                <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; display: block;">Metode Pembayaran</span>
                                <span style="color: var(--color-ink); font-weight: 600;"><?= htmlspecialchars($selected_rental['metode_pembayaran']) ?></span>
                            </div>
                            <div style="margin-bottom: var(--spacing-xs);">
                                <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; display: block;">Nominal Transfer</span>
                                <span style="color: var(--color-primary); font-weight: 700; font-size: 18px;">Rp <?= number_format($selected_rental['jumlah_bayar'], 0, ',', '.') ?></span>
                            </div>
                            
                            <form action="manage_rentals.php" method="POST" style="margin-top: var(--spacing-md);">
                                <input type="hidden" name="action" value="verify_payment">
                                <input type="hidden" name="id_sewa" value="<?= $selected_rental['id_sewa'] ?>">
                                
                                <div style="display: flex; gap: var(--spacing-xs);">
                                    <button type="submit" name="status_verifikasi" value="approve" class="btn-primary-ferrari" style="background-color: var(--color-semantic-success); height: 38px; padding: 0 16px; font-size: 11px; letter-spacing: 0.5px;">Setujui Pembayaran</button>
                                    <button type="submit" name="status_verifikasi" value="reject" class="btn-primary-ferrari" style="background-color: var(--color-primary); height: 38px; padding: 0 16px; font-size: 11px; letter-spacing: 0.5px;">Tolak Pembayaran</button>
                                    <a href="manage_rentals.php" class="btn-outline-dark-ferrari" style="height: 38px; padding: 0 16px; font-size: 11px; letter-spacing: 0.5px;">Batal</a>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Uploaded Proof Preview -->
                        <div style="text-align: center; border-left: 1px solid var(--color-hairline); padding-left: var(--spacing-md);">
                            <span style="font-size: 11px; color: var(--color-muted); text-transform: uppercase; display: block; margin-bottom: var(--spacing-xxs);">Bukti Transfer Pelanggan</span>
                            
                            <?php 
                            $file_ext = strtolower(pathinfo($selected_rental['bukti_pembayaran'], PATHINFO_EXTENSION));
                            if ($file_ext === 'pdf'): ?>
                                <div style="padding: var(--spacing-md); background: #111; color: var(--color-ink); border: 1px solid var(--color-hairline); max-width: 250px; margin: 0 auto;">
                                    <i class="fa-solid fa-file-pdf" style="font-size: 48px; color: #da291c; margin-bottom: 8px;"></i>
                                    <span style="display: block; font-size: 12px; font-weight: 600; margin-bottom: var(--spacing-xs);"><?= htmlspecialchars($selected_rental['bukti_pembayaran']) ?></span>
                                    <a href="../uploads/bukti_pembayaran/<?= htmlspecialchars($selected_rental['bukti_pembayaran']) ?>" target="_blank" class="btn-outline-dark-ferrari" style="height: 36px; padding: 0 16px; font-size: 12px; border-color: var(--color-primary); color: var(--color-primary);">Buka PDF</a>
                                </div>
                            <?php else: ?>
                                <a href="../uploads/bukti_pembayaran/<?= htmlspecialchars($selected_rental['bukti_pembayaran']) ?>" target="_blank" title="Klik untuk melihat ukuran penuh">
                                    <img src="../uploads/bukti_pembayaran/<?= htmlspecialchars($selected_rental['bukti_pembayaran']) ?>" alt="Bukti Transfer" style="max-width: 100%; max-height: 250px; object-fit: contain; border: 1px solid var(--color-hairline); background-color: #222; padding: 4px;">
                                </a>
                                <small style="display: block; color: var(--color-muted); font-size: 11px; margin-top: 4px;">Klik gambar untuk melihat resolusi penuh</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Active Return Modal/Overlay simulation (shown if trigger action) -->
        <?php if (isset($_GET['return_id'])): 
            $ret_id = (int)$_GET['return_id'];
            $selected_rental = null;
            foreach ($rentals as $r) {
                if ($r['id_sewa'] === $ret_id) {
                    $selected_rental = $r;
                }
            }
        ?>
            <?php if ($selected_rental): ?>
                <div style="background-color: var(--color-canvas-elevated); border: 1px solid var(--color-primary); padding: var(--spacing-md); margin-bottom: var(--spacing-md);">
                    <h3 class="title-sm" style="text-transform: uppercase; color: var(--color-primary); margin-bottom: var(--spacing-xs);">Formulir Pengembalian Mobil #SR-<?= str_pad($selected_rental['id_sewa'], 4, '0', STR_PAD_LEFT) ?></h3>
                    
                    <form action="manage_rentals.php" method="POST">
                        <input type="hidden" name="action" value="return">
                        <input type="hidden" name="id_sewa" value="<?= $selected_rental['id_sewa'] ?>">
                        
                        <div class="grid-3-col" style="margin-bottom: var(--spacing-xs);">
                            <div>
                                <label class="form-label-dark">Mobil</label>
                                <div style="color: var(--color-ink); font-weight: 600; padding: 10px 0;"><?= htmlspecialchars($selected_rental['merk'] . ' ' . $selected_rental['nama_mobil']) ?></div>
                            </div>
                            <div>
                                <label class="form-label-dark">Penyewa</label>
                                <div style="color: var(--color-ink); font-weight: 600; padding: 10px 0;"><?= htmlspecialchars($selected_rental['nama_pelanggan']) ?></div>
                            </div>
                            <div>
                                <label class="form-label-dark">Tanggal Sewa</label>
                                <div style="color: var(--color-ink); font-weight: 600; padding: 10px 0;"><?= $selected_rental['tanggal_sewa'] ?> s/d <?= $selected_rental['tanggal_kembali'] ?></div>
                            </div>
                        </div>

                        <div class="grid-2-col">
                            <div style="margin-bottom: var(--spacing-xs);">
                                <label class="form-label-dark" for="kondisi_mobil">Kondisi Fisik Mobil</label>
                                <select name="kondisi_mobil" id="kondisi_mobil" class="form-input-dark" required>
                                    <option value="Sangat Baik">Sangat Baik / Mulus</option>
                                    <option value="Baret Ringan">Baret Ringan</option>
                                    <option value="Rusak Sedang">Rusak Sedang</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>

                            <div style="margin-bottom: var(--spacing-xs);">
                                <label class="form-label-dark" for="denda">Denda Keterlambatan / Kerusakan (Rupiah)</label>
                                <input type="number" name="denda" id="denda" class="form-input-dark" value="0" min="0">
                            </div>
                        </div>

                        <div style="margin-bottom: var(--spacing-sm);">
                            <label class="form-label-dark" for="catatan">Catatan Tambahan</label>
                            <textarea name="catatan" id="catatan" class="form-input-dark" style="height: 60px; resize: none; padding-top: var(--spacing-xxs);"></textarea>
                        </div>

                        <div style="display: flex; gap: var(--spacing-xs);">
                            <button type="submit" class="btn-primary-ferrari">Proses Selesai</button>
                            <a href="manage_rentals.php" class="btn-outline-dark-ferrari">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Rentals List Table -->
        <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-sm); border: 1px solid var(--color-hairline);">
            <h3 class="title-sm" style="text-transform: uppercase; border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs);">Transaksi Penyewaan Terkini</h3>
            
            <?php if (empty($rentals)): ?>
                <p style="color: var(--color-muted); text-align: center; padding: var(--spacing-lg) 0;">Tidak ada catatan transaksi sewa.</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table-ferrari" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Pelanggan</th>
                                <th>Mobil</th>
                                <th>Periode Sewa</th>
                                <th>Durasi</th>
                                <th>Total Biaya</th>
                                <th>Status Sewa</th>
                                <th style="text-align: center;">Tindakan Administrasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rentals as $r): ?>
                                <tr>
                                    <td style="font-weight: 600; color: var(--color-ink);">#SR-<?= str_pad($r['id_sewa'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--color-ink);"><?= htmlspecialchars($r['nama_pelanggan']) ?></div>
                                        <span style="font-size: 11px; color: var(--color-muted);"><?= htmlspecialchars($r['no_hp']) ?></span>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--color-ink);"><?= htmlspecialchars($r['merk'] . ' ' . $r['nama_mobil']) ?></div>
                                        <span style="font-size: 11px; color: var(--color-muted);"><?= htmlspecialchars($r['plat_nomor']) ?></span>
                                    </td>
                                    <td style="font-size: 13px;">
                                        <div><?= date('d M Y', strtotime($r['tanggal_sewa'])) ?></div>
                                        <span style="font-size: 11px; color: var(--color-muted);">s/d <?= date('d M Y', strtotime($r['tanggal_kembali'])) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($r['lama_sewa']) ?> Hari</td>
                                    <td style="font-weight: 600; color: var(--color-ink);">Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if ($r['status_sewa'] === 'belum_bayar'): ?>
                                            <?php if (!empty($r['bukti_pembayaran']) && $r['status_pembayaran'] === 'pending'): ?>
                                                <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-info); color: var(--color-semantic-info); font-size: 10px;">Butuh Verifikasi</span>
                                            <?php elseif ($r['status_pembayaran'] === 'cancel'): ?>
                                                <span class="badge-pill-ferrari" style="border-color: var(--color-primary); color: var(--color-primary); font-size: 10px;">Ditolak</span>
                                            <?php else: ?>
                                                <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-warning); color: var(--color-semantic-warning); font-size: 10px;">Belum Bayar</span>
                                            <?php endif; ?>
                                        <?php elseif ($r['status_sewa'] === 'sudah_bayar'): ?>
                                            <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-success); color: var(--color-semantic-success); font-size: 10px;">Lunas / Paid</span>
                                        <?php elseif ($r['status_sewa'] === 'diambil'): ?>
                                            <span class="badge-pill-ferrari" style="border-color: var(--color-semantic-info); color: var(--color-semantic-info); font-size: 10px;">Sedang Disewa</span>
                                        <?php elseif ($r['status_sewa'] === 'kembali'): ?>
                                            <span class="badge-pill-ferrari" style="border-color: var(--color-body); color: var(--color-body); font-size: 10px;">Kembali (Selesai)</span>
                                        <?php else: ?>
                                            <span class="badge-pill-ferrari" style="border-color: var(--color-muted); color: var(--color-muted); font-size: 10px;">Batal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($r['status_sewa'] === 'sudah_bayar'): ?>
                                            <a href="manage_rentals.php?action=handover&id_sewa=<?= $r['id_sewa'] ?>" class="btn-primary-ferrari" style="height: 32px; padding: 0 16px; font-size: 11px;">Serahkan Mobil</a>
                                        <?php elseif ($r['status_sewa'] === 'diambil'): ?>
                                            <a href="manage_rentals.php?return_id=<?= $r['id_sewa'] ?>#formulir" class="btn-outline-dark-ferrari" style="height: 32px; padding: 0 16px; font-size: 11px; border-color: var(--color-primary); color: var(--color-primary);">Terima Kembali</a>
                                        <?php elseif ($r['status_sewa'] === 'belum_bayar'): ?>
                                            <?php if (!empty($r['bukti_pembayaran']) && $r['status_pembayaran'] === 'pending'): ?>
                                                <a href="manage_rentals.php?verify_id=<?= $r['id_sewa'] ?>#verifikasi" class="btn-primary-ferrari" style="height: 32px; padding: 0 12px; font-size: 11px; background-color: var(--color-semantic-info);">Verifikasi Bayar</a>
                                            <?php else: ?>
                                                <span style="font-size: 12px; color: var(--color-muted);"><i class="fa-solid fa-hourglass-start"></i> Menunggu Bayar</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: var(--color-muted);"><i class="fa-solid fa-circle-check"></i> Selesai / Arsip</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php 
$base_url = "../";
require_once '../includes/footer.php'; 
?>
