<?php
// admin/manage_cars.php
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
$cars = [];
$categories = [];
$success_msg = '';
$error_msg = '';

// Check database connection and load data
try {
    if (isset($pdo)) {
        // Load categories
        $stmt_cat = $pdo->query("SELECT * FROM kategori_mobil");
        $categories = $stmt_cat->fetchAll();
        
        // Load cars
        $stmt_cars = $pdo->query("SELECT m.*, k.nama_kategori FROM mobil m JOIN kategori_mobil k ON m.id_kategori = k.id_kategori ORDER BY m.id_mobil DESC");
        $cars = $stmt_cars->fetchAll();
        $db_connected = true;
    }
} catch (PDOException $e) {
    $db_connected = false;
}

// Fallback Mock Data if DB offline
if (!$db_connected) {
    $categories = [
        ['id_kategori' => 1, 'nama_kategori' => 'MPV Keluarga'],
        ['id_kategori' => 2, 'nama_kategori' => 'SUV Tangguh'],
        ['id_kategori' => 3, 'nama_kategori' => 'Hatchback / City Car'],
        ['id_kategori' => 4, 'nama_kategori' => 'Sedan Elegan']
    ];

    if (!isset($_SESSION['mock_cars'])) {
        $_SESSION['mock_cars'] = [
            ['id_mobil' => 1, 'id_kategori' => 1, 'nama_mobil' => 'Avanza', 'merk' => 'Toyota', 'tahun' => 2023, 'plat_nomor' => 'DR 1902 SA', 'harga_sewa_per_hari' => 350000.00, 'status' => 'tersedia', 'foto' => 'avanza.jpg', 'deskripsi' => 'Toyota Avanza MPV Keluarga terfavorit.', 'nama_kategori' => 'MPV Keluarga'],
            ['id_mobil' => 2, 'id_kategori' => 1, 'nama_mobil' => 'Xpander', 'merk' => 'Mitsubishi', 'tahun' => 2022, 'plat_nomor' => 'DR 8008 FT', 'harga_sewa_per_hari' => 450000.00, 'status' => 'tersedia', 'foto' => 'xpander.jpg', 'deskripsi' => 'Mitsubishi Xpander desain gagah kabin senyap.', 'nama_kategori' => 'MPV Keluarga'],
            ['id_mobil' => 3, 'id_kategori' => 1, 'nama_mobil' => 'Kijang Innova', 'merk' => 'Toyota', 'tahun' => 2021, 'plat_nomor' => 'DR 8128 SF', 'harga_sewa_per_hari' => 650000.00, 'status' => 'tersedia', 'foto' => 'innova.jpg', 'deskripsi' => 'Innova Reborn kenyamanan kelas premium.', 'nama_kategori' => 'MPV Keluarga'],
            ['id_mobil' => 4, 'id_kategori' => 1, 'nama_mobil' => 'All New Veloz', 'merk' => 'Toyota', 'tahun' => 2023, 'plat_nomor' => 'DR 2525 RM', 'harga_sewa_per_hari' => 450000.00, 'status' => 'tersedia', 'foto' => 'veloz.jpg', 'deskripsi' => 'Toyota All New Veloz dengan fitur TSS.', 'nama_kategori' => 'MPV Keluarga'],
            ['id_mobil' => 5, 'id_kategori' => 2, 'nama_mobil' => 'Pajero Sport', 'merk' => 'Mitsubishi', 'tahun' => 2022, 'plat_nomor' => 'DR 1555 PS', 'harga_sewa_per_hari' => 850000.00, 'status' => 'tersedia', 'foto' => 'pajero.jpg', 'deskripsi' => 'SUV Tangguh Mitsubishi Pajero Sport.', 'nama_kategori' => 'SUV Tangguh'],
            ['id_mobil' => 6, 'id_kategori' => 3, 'nama_mobil' => 'Jazz', 'merk' => 'Honda', 'tahun' => 2020, 'plat_nomor' => 'DR 9099 JZ', 'harga_sewa_per_hari' => 350000.00, 'status' => 'tersedia', 'foto' => 'jazz.jpg', 'deskripsi' => 'Honda Jazz Hatchback lincah sporty.', 'nama_kategori' => 'Hatchback / City Car'],
            ['id_mobil' => 7, 'id_kategori' => 4, 'nama_mobil' => 'Civic Turbo', 'merk' => 'Honda', 'tahun' => 2023, 'plat_nomor' => 'DR 1000 CV', 'harga_sewa_per_hari' => 900000.00, 'status' => 'tersedia', 'foto' => 'civic.jpg', 'deskripsi' => 'Sedan mewah performa turbo responsif.', 'nama_kategori' => 'Sedan Elegan']
        ];
    }
    $cars = $_SESSION['mock_cars'];
}

// 1. Process Insert Car
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_mobil = trim($_POST['nama_mobil']);
    $merk = trim($_POST['merk']);
    
    // Normalize: if the user included the brand at the beginning of the model name, strip it to prevent duplicate rendering
    if (!empty($merk) && stripos($nama_mobil, $merk) === 0) {
        $stripped_name = trim(substr($nama_mobil, strlen($merk)));
        if ($stripped_name !== '') {
            $nama_mobil = $stripped_name;
        }
    }
    $tahun = (int)$_POST['tahun'];
    $plat_nomor = trim($_POST['plat_nomor']);
    $harga_sewa_per_hari = (float)$_POST['harga_sewa_per_hari'];
    $id_kategori = (int)$_POST['id_kategori'];
    $deskripsi = trim($_POST['deskripsi']);
    
    // File upload or placeholder image
    $foto = 'roma.jpg'; // default placeholder
    if (!empty($_POST['foto_name'])) {
        $foto = trim($_POST['foto_name']);
    }

    if (empty($nama_mobil) || empty($plat_nomor) || $harga_sewa_per_hari <= 0 || $id_kategori <= 0) {
        $error_msg = "Isian wajib tidak boleh kosong atau tidak valid.";
    } else {
        if ($db_connected) {
            try {
                $stmt = $pdo->prepare("INSERT INTO mobil (id_kategori, nama_mobil, merk, tahun, plat_nomor, harga_sewa_per_hari, status, foto, deskripsi) VALUES (?, ?, ?, ?, ?, ?, 'tersedia', ?, ?)");
                $stmt->execute([$id_kategori, $nama_mobil, $merk, $tahun, $plat_nomor, $harga_sewa_per_hari, $foto, $deskripsi]);
                $success_msg = "Mobil baru berhasil ditambahkan ke armada.";
                
                // Reload cars
                $stmt_cars = $pdo->query("SELECT m.*, k.nama_kategori FROM mobil m JOIN kategori_mobil k ON m.id_kategori = k.id_kategori ORDER BY m.id_mobil DESC");
                $cars = $stmt_cars->fetchAll();
            } catch (PDOException $e) {
                $error_msg = "Terjadi kesalahan database: " . $e->getMessage();
            }
        } else {
            // Mock Insert
            $new_id = count($_SESSION['mock_cars']) + 1;
            
            // Get category name
            $cat_name = 'Custom Category';
            foreach ($categories as $cat) {
                if ($cat['id_kategori'] == $id_kategori) {
                    $cat_name = $cat['nama_kategori'];
                }
            }

            $new_car = [
                'id_mobil' => $new_id,
                'id_kategori' => $id_kategori,
                'nama_mobil' => $nama_mobil,
                'merk' => $merk,
                'tahun' => $tahun,
                'plat_nomor' => $plat_nomor,
                'harga_sewa_per_hari' => $harga_sewa_per_hari,
                'status' => 'tersedia',
                'foto' => $foto,
                'deskripsi' => $deskripsi,
                'nama_kategori' => $cat_name
            ];

            $_SESSION['mock_cars'][] = $new_car;
            $cars = $_SESSION['mock_cars'];
            $success_msg = "Mock: Mobil baru berhasil ditambahkan.";
        }
    }
}

// 2. Process Delete Car
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($db_connected) {
        try {
            $stmt = $pdo->prepare("DELETE FROM mobil WHERE id_mobil = ?");
            $stmt->execute([$del_id]);
            $success_msg = "Mobil berhasil dihapus dari armada.";
            
            // Reload cars
            $stmt_cars = $pdo->query("SELECT m.*, k.nama_kategori FROM mobil m JOIN kategori_mobil k ON m.id_kategori = k.id_kategori ORDER BY m.id_mobil DESC");
            $cars = $stmt_cars->fetchAll();
        } catch (PDOException $e) {
            $error_msg = "Gagal menghapus mobil: " . $e->getMessage();
        }
    } else {
        // Mock Delete
        $_SESSION['mock_cars'] = array_filter($_SESSION['mock_cars'], function($car) use ($del_id) {
            return $car['id_mobil'] !== $del_id;
        });
        $cars = $_SESSION['mock_cars'];
        $success_msg = "Mock: Mobil berhasil dihapus.";
    }
}

// 3. Process Status Update
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $up_id = (int)$_POST['id_mobil'];
    $status = $_POST['status'];
    
    if ($db_connected) {
        try {
            $stmt = $pdo->prepare("UPDATE mobil SET status = ? WHERE id_mobil = ?");
            $stmt->execute([$status, $up_id]);
            $success_msg = "Status mobil berhasil diubah.";
            
            // Reload cars
            $stmt_cars = $pdo->query("SELECT m.*, k.nama_kategori FROM mobil m JOIN kategori_mobil k ON m.id_kategori = k.id_kategori ORDER BY m.id_mobil DESC");
            $cars = $stmt_cars->fetchAll();
        } catch (PDOException $e) {
            $error_msg = "Gagal mengubah status: " . $e->getMessage();
        }
    } else {
        // Mock Update
        foreach ($_SESSION['mock_cars'] as &$car) {
            if ($car['id_mobil'] === $up_id) {
                $car['status'] = $status;
            }
        }
        unset($car);
        $cars = $_SESSION['mock_cars'];
        $success_msg = "Mock: Status mobil berhasil diubah.";
    }
}

$page_title = "Manajemen Armada Mobil";
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
            <h2 class="display-lg">Manajemen Armada Mobil</h2>
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

        <div class="grid-2-col" style="grid-template-columns: 2fr 1fr; align-items: start; gap: var(--spacing-md);">
            
            <!-- Fleet List -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-sm); border: 1px solid var(--color-hairline);">
                <h3 class="title-sm" style="text-transform: uppercase; border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs);">Daftar Armada Mobil</h3>
                
                <div style="overflow-x: auto;">
                    <table class="table-ferrari" style="margin-top: 0;">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Mobil</th>
                                <th>Nopol & Tahun</th>
                                <th>Harga Sewa</th>
                                <th>Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $c): ?>
                                <tr>
                                    <td>
                                        <img src="../assets/img/<?= htmlspecialchars($c['foto']) ?>" alt="<?= htmlspecialchars($c['nama_mobil']) ?>" style="width: 70px; height: 40px; object-fit: cover; border: 1px solid var(--color-hairline);">
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--color-ink);"><?= htmlspecialchars((!empty($c['merk']) && stripos($c['nama_mobil'], $c['merk']) === 0) ? $c['nama_mobil'] : $c['merk'] . ' ' . $c['nama_mobil']) ?></div>
                                        <span style="font-size: 11px; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($c['nama_kategori']) ?></span>
                                    </td>
                                    <td>
                                        <div style="color: var(--color-ink); font-weight: 500;"><?= htmlspecialchars($c['plat_nomor']) ?></div>
                                        <span style="font-size: 11px; color: var(--color-muted);"><?= htmlspecialchars($c['tahun']) ?></span>
                                    </td>
                                    <td style="font-weight: 600; color: var(--color-ink);">Rp <?= number_format($c['harga_sewa_per_hari'], 0, ',', '.') ?></td>
                                    <td>
                                        <form action="manage_cars.php" method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id_mobil" value="<?= $c['id_mobil'] ?>">
                                            <select name="status" onchange="this.form.submit()" class="form-input-dark" style="height: 32px; padding: 2px 8px; font-size: 12px; width: 110px;">
                                                <option value="tersedia" <?= $c['status'] === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                                <option value="disewa" <?= $c['status'] === 'disewa' ? 'selected' : '' ?>>Disewa</option>
                                                <option value="perbaikan" <?= $c['status'] === 'perbaikan' ? 'selected' : '' ?>>Perbaikan</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="manage_cars.php?delete_id=<?= $c['id_mobil'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus mobil ini dari katalog?')" class="btn-outline-dark-ferrari" style="height: 32px; padding: 0 12px; font-size: 11px; border-color: var(--color-semantic-warning); color: var(--color-semantic-warning);">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Car Form -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-sm); border: 1px solid var(--color-hairline);">
                <h3 class="title-sm" style="text-transform: uppercase; border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs);">Tambah Mobil Baru</h3>
                
                <form action="manage_cars.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="nama_mobil">Nama Model Mobil</label>
                        <input type="text" name="nama_mobil" id="nama_mobil" class="form-input-dark" placeholder="Contoh: Roma" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="merk">Merk Pabrikan</label>
                        <input type="text" name="merk" id="merk" class="form-input-dark" value="Toyota" placeholder="Contoh: Toyota" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="id_kategori">Kategori</label>
                        <select name="id_kategori" id="id_kategori" class="form-input-dark" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="tahun">Tahun Produksi</label>
                        <input type="number" name="tahun" id="tahun" class="form-input-dark" value="<?= date('Y') ?>" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="plat_nomor">Plat Nomor</label>
                        <input type="text" name="plat_nomor" id="plat_nomor" class="form-input-dark" placeholder="Contoh: B 25 RM" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="harga_sewa_per_hari">Harga Sewa / Hari (Rupiah)</label>
                        <input type="number" name="harga_sewa_per_hari" id="harga_sewa_per_hari" class="form-input-dark" placeholder="Contoh: 8000000" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="foto_name">Nama File Foto</label>
                        <input type="text" name="foto_name" id="foto_name" class="form-input-dark" value="roma.jpg" placeholder="Contoh: roma.jpg">
                        <span style="font-size: 11px; color: var(--color-muted);">Gunakan: avanza.jpg, veloz.jpg, xpander.jpg, innova.jpg, pajero.jpg, jazz.jpg, atau civic.jpg</span>
                    </div>

                    <div style="margin-bottom: var(--spacing-sm);">
                        <label class="form-label-dark" for="deskripsi">Deskripsi Singkat</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-input-dark" style="height: 80px; resize: none; padding-top: var(--spacing-xxs);"></textarea>
                    </div>

                    <button type="submit" class="btn-primary-ferrari" style="width: 100%;">Simpan Kendaraan</button>
                </form>
            </div>

        </div>

    </div>
</section>

<?php 
$base_url = "../";
require_once '../includes/footer.php'; 
?>
