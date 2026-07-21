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
            ['id_mobil' => 1, 'id_kategori' => 1, 'nama_mobil' => 'Avanza', 'merk' => 'Toyota', 'tahun' => 2023, 'plat_nomor' => 'DR 1902 SA', 'harga_sewa_per_hari' => 350000.00, 'status' => 'tersedia', 'foto' => 'avanza.jpg', 'deskripsi' => 'Toyota Avanza MPV Keluarga terfavorit.', 'kapasitas_kursi' => '7 Kursi', 'transmisi' => 'Manual / Matic', 'bahan_bakar' => 'Bensin', 'kapasitas_mesin' => '1.500 cc', 'nama_kategori' => 'MPV Keluarga'],
            ['id_mobil' => 2, 'id_kategori' => 1, 'nama_mobil' => 'Xpander', 'merk' => 'Mitsubishi', 'tahun' => 2022, 'plat_nomor' => 'DR 8008 FT', 'harga_sewa_per_hari' => 450000.00, 'status' => 'tersedia', 'foto' => 'xpander.jpg', 'deskripsi' => 'Mitsubishi Xpander desain gagah kabin senyap.', 'kapasitas_kursi' => '7 Kursi', 'transmisi' => 'CVT Otomatis', 'bahan_bakar' => 'Bensin', 'kapasitas_mesin' => '1.500 cc', 'nama_kategori' => 'MPV Keluarga'],
            ['id_mobil' => 3, 'id_kategori' => 1, 'nama_mobil' => 'Kijang Innova', 'merk' => 'Toyota', 'tahun' => 2021, 'plat_nomor' => 'DR 8128 SF', 'harga_sewa_per_hari' => 650000.00, 'status' => 'tersedia', 'foto' => 'innova.jpg', 'deskripsi' => 'Innova Reborn kenyamanan kelas premium.', 'kapasitas_kursi' => '7 Kursi', 'transmisi' => 'Manual / Matic', 'bahan_bakar' => 'Diesel / Bensin', 'kapasitas_mesin' => '2.400 cc', 'nama_kategori' => 'MPV Keluarga'],
            ['id_mobil' => 4, 'id_kategori' => 1, 'nama_mobil' => 'All New Veloz', 'merk' => 'Toyota', 'tahun' => 2023, 'plat_nomor' => 'DR 2525 RM', 'harga_sewa_per_hari' => 450000.00, 'status' => 'tersedia', 'foto' => 'veloz.jpg', 'deskripsi' => 'Toyota All New Veloz dengan fitur TSS.', 'kapasitas_kursi' => '7 Kursi', 'transmisi' => 'CVT Otomatis', 'bahan_bakar' => 'Bensin', 'kapasitas_mesin' => '1.500 cc', 'nama_kategori' => 'MPV Keluarga'],
            ['id_mobil' => 5, 'id_kategori' => 2, 'nama_mobil' => 'Pajero Sport', 'merk' => 'Mitsubishi', 'tahun' => 2022, 'plat_nomor' => 'DR 1555 PS', 'harga_sewa_per_hari' => 850000.00, 'status' => 'tersedia', 'foto' => 'pajero.jpg', 'deskripsi' => 'SUV Tangguh Mitsubishi Pajero Sport.', 'kapasitas_kursi' => '7 Kursi', 'transmisi' => '8-Speed Matic', 'bahan_bakar' => 'Diesel (Solar)', 'kapasitas_mesin' => '2.400 cc', 'nama_kategori' => 'SUV Tangguh'],
            ['id_mobil' => 6, 'id_kategori' => 3, 'nama_mobil' => 'Jazz', 'merk' => 'Honda', 'tahun' => 2020, 'plat_nomor' => 'DR 9099 JZ', 'harga_sewa_per_hari' => 350000.00, 'status' => 'tersedia', 'foto' => 'jazz.jpg', 'deskripsi' => 'Honda Jazz Hatchback lincah sporty.', 'kapasitas_kursi' => '5 Kursi', 'transmisi' => 'Manual / Matic', 'bahan_bakar' => 'Bensin', 'kapasitas_mesin' => '1.500 cc', 'nama_kategori' => 'Hatchback / City Car'],
            ['id_mobil' => 7, 'id_kategori' => 4, 'nama_mobil' => 'Civic Turbo', 'merk' => 'Honda', 'tahun' => 2023, 'plat_nomor' => 'DR 1000 CV', 'harga_sewa_per_hari' => 900000.00, 'status' => 'tersedia', 'foto' => 'civic.jpg', 'deskripsi' => 'Sedan mewah performa turbo responsif.', 'kapasitas_kursi' => '5 Kursi', 'transmisi' => 'CVT Otomatis', 'bahan_bakar' => 'Bensin Turbo', 'kapasitas_mesin' => '1.500 cc', 'nama_kategori' => 'Sedan Elegan']
        ];
    }
    $cars = $_SESSION['mock_cars'];
}

// Helper to reload car list
function reloadCarsData($pdo, $db_connected) {
    if ($db_connected && isset($pdo)) {
        $stmt = $pdo->query("SELECT m.*, k.nama_kategori FROM mobil m JOIN kategori_mobil k ON m.id_kategori = k.id_kategori ORDER BY m.id_mobil DESC");
        return $stmt->fetchAll();
    }
    return $_SESSION['mock_cars'] ?? [];
}

// 1. Process Insert Car
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama_mobil = trim($_POST['nama_mobil']);
    $merk = trim($_POST['merk']);
    
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
    $kapasitas_kursi = !empty($_POST['kapasitas_kursi']) ? trim($_POST['kapasitas_kursi']) : '7 Kursi';
    $transmisi = !empty($_POST['transmisi']) ? trim($_POST['transmisi']) : 'Manual / Matic';
    $bahan_bakar = !empty($_POST['bahan_bakar']) ? trim($_POST['bahan_bakar']) : 'Bensin';
    $kapasitas_mesin = !empty($_POST['kapasitas_mesin']) ? trim($_POST['kapasitas_mesin']) : '1.500 cc';
    
    $foto = 'roma.jpg';
    if (!empty($_POST['foto_name'])) {
        $foto = trim($_POST['foto_name']);
    }

    if (empty($nama_mobil) || empty($plat_nomor) || $harga_sewa_per_hari <= 0 || $id_kategori <= 0) {
        $error_msg = "Isian wajib tidak boleh kosong atau tidak valid.";
    } else {
        if ($db_connected) {
            try {
                $stmt = $pdo->prepare("INSERT INTO mobil (id_kategori, nama_mobil, merk, tahun, plat_nomor, harga_sewa_per_hari, status, foto, deskripsi, kapasitas_kursi, transmisi, bahan_bakar, kapasitas_mesin) VALUES (?, ?, ?, ?, ?, ?, 'tersedia', ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_kategori, $nama_mobil, $merk, $tahun, $plat_nomor, $harga_sewa_per_hari, $foto, $deskripsi, $kapasitas_kursi, $transmisi, $bahan_bakar, $kapasitas_mesin]);
                $success_msg = "Mobil baru berhasil ditambahkan ke armada.";
                $cars = reloadCarsData($pdo, true);
            } catch (PDOException $e) {
                $error_msg = "Terjadi kesalahan database: " . $e->getMessage();
            }
        } else {
            // Mock Insert
            $new_id = count($_SESSION['mock_cars']) + 1;
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
                'kapasitas_kursi' => $kapasitas_kursi,
                'transmisi' => $transmisi,
                'bahan_bakar' => $bahan_bakar,
                'kapasitas_mesin' => $kapasitas_mesin,
                'nama_kategori' => $cat_name
            ];

            $_SESSION['mock_cars'][] = $new_car;
            $cars = $_SESSION['mock_cars'];
            $success_msg = "Mock: Mobil baru berhasil ditambahkan.";
        }
    }
}

// 2. Process Edit Car
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id_mobil = (int)$_POST['id_mobil'];
    $nama_mobil = trim($_POST['nama_mobil']);
    $merk = trim($_POST['merk']);
    
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
    $status = $_POST['status'];
    $deskripsi = trim($_POST['deskripsi']);
    $kapasitas_kursi = !empty($_POST['kapasitas_kursi']) ? trim($_POST['kapasitas_kursi']) : '7 Kursi';
    $transmisi = !empty($_POST['transmisi']) ? trim($_POST['transmisi']) : 'Manual / Matic';
    $bahan_bakar = !empty($_POST['bahan_bakar']) ? trim($_POST['bahan_bakar']) : 'Bensin';
    $kapasitas_mesin = !empty($_POST['kapasitas_mesin']) ? trim($_POST['kapasitas_mesin']) : '1.500 cc';
    $foto = trim($_POST['foto_name']);

    if ($id_mobil <= 0 || empty($nama_mobil) || empty($plat_nomor) || $harga_sewa_per_hari <= 0 || $id_kategori <= 0) {
        $error_msg = "Isian formulir edit tidak valid.";
    } else {
        if ($db_connected) {
            try {
                $stmt = $pdo->prepare("UPDATE mobil SET id_kategori = ?, nama_mobil = ?, merk = ?, tahun = ?, plat_nomor = ?, harga_sewa_per_hari = ?, status = ?, foto = ?, deskripsi = ?, kapasitas_kursi = ?, transmisi = ?, bahan_bakar = ?, kapasitas_mesin = ? WHERE id_mobil = ?");
                $stmt->execute([$id_kategori, $nama_mobil, $merk, $tahun, $plat_nomor, $harga_sewa_per_hari, $status, $foto, $deskripsi, $kapasitas_kursi, $transmisi, $bahan_bakar, $kapasitas_mesin, $id_mobil]);
                $success_msg = "Data mobil berhasil diperbarui.";
                $cars = reloadCarsData($pdo, true);
            } catch (PDOException $e) {
                $error_msg = "Gagal memperbarui data: " . $e->getMessage();
            }
        } else {
            // Mock Edit
            $cat_name = 'Custom Category';
            foreach ($categories as $cat) {
                if ($cat['id_kategori'] == $id_kategori) {
                    $cat_name = $cat['nama_kategori'];
                }
            }

            foreach ($_SESSION['mock_cars'] as &$car) {
                if ($car['id_mobil'] === $id_mobil) {
                    $car['id_kategori'] = $id_kategori;
                    $car['nama_mobil'] = $nama_mobil;
                    $car['merk'] = $merk;
                    $car['tahun'] = $tahun;
                    $car['plat_nomor'] = $plat_nomor;
                    $car['harga_sewa_per_hari'] = $harga_sewa_per_hari;
                    $car['status'] = $status;
                    $car['foto'] = $foto;
                    $car['deskripsi'] = $deskripsi;
                    $car['kapasitas_kursi'] = $kapasitas_kursi;
                    $car['transmisi'] = $transmisi;
                    $car['bahan_bakar'] = $bahan_bakar;
                    $car['kapasitas_mesin'] = $kapasitas_mesin;
                    $car['nama_kategori'] = $cat_name;
                }
            }
            unset($car);
            $cars = $_SESSION['mock_cars'];
            $success_msg = "Mock: Data mobil berhasil diperbarui.";
        }
    }
}

// 3. Process Delete Car
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($db_connected) {
        try {
            $stmt = $pdo->prepare("DELETE FROM mobil WHERE id_mobil = ?");
            $stmt->execute([$del_id]);
            $success_msg = "Mobil berhasil dihapus dari armada.";
            $cars = reloadCarsData($pdo, true);
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

// 4. Process Quick Status Update
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $up_id = (int)$_POST['id_mobil'];
    $status = $_POST['status'];
    
    if ($db_connected) {
        try {
            $stmt = $pdo->prepare("UPDATE mobil SET status = ? WHERE id_mobil = ?");
            $stmt->execute([$status, $up_id]);
            $success_msg = "Status mobil berhasil diubah.";
            $cars = reloadCarsData($pdo, true);
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
                                <th>Mobil & Spesifikasi</th>
                                <th>Nopol & Tahun</th>
                                <th>Harga Sewa</th>
                                <th>Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $c): 
                                $kursi = $c['kapasitas_kursi'] ?? '7 Kursi';
                                $trans = $c['transmisi'] ?? 'Manual / Matic';
                                $bb = $c['bahan_bakar'] ?? 'Bensin';
                                $cc = $c['kapasitas_mesin'] ?? '1.500 cc';
                            ?>
                                <tr>
                                    <td>
                                        <img src="../assets/img/<?= htmlspecialchars($c['foto']) ?>" alt="<?= htmlspecialchars($c['nama_mobil']) ?>" style="width: 75px; height: 45px; object-fit: cover; border: 1px solid var(--color-hairline); border-radius: 2px;">
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--color-ink);"><?= htmlspecialchars((!empty($c['merk']) && stripos($c['nama_mobil'], $c['merk']) === 0) ? $c['nama_mobil'] : $c['merk'] . ' ' . $c['nama_mobil']) ?></div>
                                        <span style="font-size: 11px; color: var(--color-primary); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;"><?= htmlspecialchars($c['nama_kategori']) ?></span>
                                        
                                        <!-- Specs summary pills -->
                                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                            <span style="font-size: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--color-hairline); padding: 1px 5px; color: var(--color-muted);"><i class="fa-solid fa-users"></i> <?= htmlspecialchars($kursi) ?></span>
                                            <span style="font-size: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--color-hairline); padding: 1px 5px; color: var(--color-muted);"><i class="fa-solid fa-gear"></i> <?= htmlspecialchars($trans) ?></span>
                                            <span style="font-size: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--color-hairline); padding: 1px 5px; color: var(--color-muted);"><i class="fa-solid fa-gas-pump"></i> <?= htmlspecialchars($bb) ?></span>
                                            <span style="font-size: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--color-hairline); padding: 1px 5px; color: var(--color-muted);"><i class="fa-solid fa-gauge-high"></i> <?= htmlspecialchars($cc) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="color: var(--color-ink); font-weight: 500; font-family: monospace;"><?= htmlspecialchars($c['plat_nomor']) ?></div>
                                        <span style="font-size: 11px; color: var(--color-muted);"><?= htmlspecialchars($c['tahun']) ?></span>
                                    </td>
                                    <td style="font-weight: 600; color: var(--color-ink); white-space: nowrap;">Rp <?= number_format($c['harga_sewa_per_hari'], 0, ',', '.') ?></td>
                                    <td>
                                        <form action="manage_cars.php" method="POST" style="margin: 0;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="id_mobil" value="<?= $c['id_mobil'] ?>">
                                            <select name="status" onchange="this.form.submit()" class="form-input-dark" style="height: 32px; padding: 2px 8px; font-size: 12px; width: 105px;">
                                                <option value="tersedia" <?= $c['status'] === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                                <option value="disewa" <?= $c['status'] === 'disewa' ? 'selected' : '' ?>>Disewa</option>
                                                <option value="perbaikan" <?= $c['status'] === 'perbaikan' ? 'selected' : '' ?>>Perbaikan</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td style="text-align: center; white-space: nowrap;">
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                onclick='openEditModal(<?= json_encode($c) ?>)' 
                                                class="btn-outline-dark-ferrari" 
                                                style="height: 32px; padding: 0 10px; font-size: 11px; margin-right: 4px; border-color: var(--color-primary); color: var(--color-primary);">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>

                                        <!-- Delete Button -->
                                        <a href="manage_cars.php?delete_id=<?= $c['id_mobil'] ?>" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus mobil ini dari katalog?')" 
                                           class="btn-outline-dark-ferrari" 
                                           style="height: 32px; padding: 0 10px; font-size: 11px; border-color: var(--color-semantic-warning); color: var(--color-semantic-warning);">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Car Form Column -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-sm); border: 1px solid var(--color-hairline);">
                <h3 class="title-sm" style="text-transform: uppercase; border-bottom: 1px solid var(--color-hairline); padding-bottom: var(--spacing-xxs); margin-bottom: var(--spacing-xs);">Tambah Mobil Baru</h3>
                
                <form action="manage_cars.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="nama_mobil">Nama Model Mobil</label>
                        <input type="text" name="nama_mobil" id="nama_mobil" class="form-input-dark" placeholder="Contoh: Avanza" required>
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: var(--spacing-xs);">
                        <div>
                            <label class="form-label-dark" for="tahun">Tahun Produksi</label>
                            <input type="number" name="tahun" id="tahun" class="form-input-dark" value="<?= date('Y') ?>" required>
                        </div>
                        <div>
                            <label class="form-label-dark" for="plat_nomor">Plat Nomor</label>
                            <input type="text" name="plat_nomor" id="plat_nomor" class="form-input-dark" placeholder="Contoh: DR 1902 SA" required>
                        </div>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="harga_sewa_per_hari">Harga Sewa / Hari (Rupiah)</label>
                        <input type="number" name="harga_sewa_per_hari" id="harga_sewa_per_hari" class="form-input-dark" placeholder="Contoh: 350000" required>
                    </div>

                    <!-- Specification Fields -->
                    <div style="border-top: 1px dashed var(--color-hairline); padding-top: var(--spacing-xs); margin-top: var(--spacing-xs); margin-bottom: var(--spacing-xs);">
                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--color-primary); display: block; margin-bottom: 8px;">Spesifikasi Kendaraan</span>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 8px;">
                            <div>
                                <label class="form-label-dark" for="kapasitas_kursi">Kapasitas Kursi</label>
                                <input type="text" name="kapasitas_kursi" id="kapasitas_kursi" class="form-input-dark" value="7 Kursi" placeholder="Contoh: 7 Kursi">
                            </div>
                            <div>
                                <label class="form-label-dark" for="transmisi">Tipe Transmisi</label>
                                <input type="text" name="transmisi" id="transmisi" class="form-input-dark" value="Manual / Matic" placeholder="Contoh: Manual / Matic">
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div>
                                <label class="form-label-dark" for="bahan_bakar">Bahan Bakar</label>
                                <input type="text" name="bahan_bakar" id="bahan_bakar" class="form-input-dark" value="Bensin" placeholder="Contoh: Bensin">
                            </div>
                            <div>
                                <label class="form-label-dark" for="kapasitas_mesin">Kapasitas Mesin (CC)</label>
                                <input type="text" name="kapasitas_mesin" id="kapasitas_mesin" class="form-input-dark" value="1.500 cc" placeholder="Contoh: 1.500 cc">
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="foto_name">Nama File Foto</label>
                        <input type="text" name="foto_name" id="foto_name" class="form-input-dark" value="avanza.jpg" placeholder="Contoh: avanza.jpg">
                        <span style="font-size: 11px; color: var(--color-muted);">Pilihan foto: avanza.jpg, veloz.jpg, xpander.jpg, innova.jpg, pajero.jpg, jazz.jpg, civic.jpg</span>
                    </div>

                    <div style="margin-bottom: var(--spacing-sm);">
                        <label class="form-label-dark" for="deskripsi">Deskripsi Singkat</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-input-dark" style="height: 70px; resize: none; padding-top: var(--spacing-xxs);"></textarea>
                    </div>

                    <button type="submit" class="btn-primary-ferrari" style="width: 100%;">
                        <i class="fa-solid fa-plus"></i> Simpan Kendaraan Baru
                    </button>
                </form>
            </div>

        </div>

    </div>
</section>

<!-- Edit Car Modal Dialog -->
<div id="edit-car-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #1e1e1e; border: 1px solid var(--color-hairline); border-radius: 8px; width: 100%; max-width: 600px; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.8);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-hairline); padding-bottom: 12px; margin-bottom: 20px;">
            <h3 class="title-sm" style="text-transform: uppercase; color: var(--color-primary); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-pen-to-square"></i> Edit Spesifikasi & Data Mobil
            </h3>
            <button onclick="closeEditModal()" style="background: none; border: none; color: var(--color-muted); font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="manage_cars.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_mobil" id="edit_id_mobil">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div>
                    <label class="form-label-dark" for="edit_nama_mobil">Nama Model Mobil</label>
                    <input type="text" name="nama_mobil" id="edit_nama_mobil" class="form-input-dark" required>
                </div>
                <div>
                    <label class="form-label-dark" for="edit_merk">Merk Pabrikan</label>
                    <input type="text" name="merk" id="edit_merk" class="form-input-dark" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div>
                    <label class="form-label-dark" for="edit_id_kategori">Kategori</label>
                    <select name="id_kategori" id="edit_id_kategori" class="form-input-dark" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label-dark" for="edit_status">Status Mobil</label>
                    <select name="status" id="edit_status" class="form-input-dark" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="disewa">Disewa</option>
                        <option value="perbaikan">Perbaikan</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div>
                    <label class="form-label-dark" for="edit_tahun">Tahun Produksi</label>
                    <input type="number" name="tahun" id="edit_tahun" class="form-input-dark" required>
                </div>
                <div>
                    <label class="form-label-dark" for="edit_plat_nomor">Plat Nomor</label>
                    <input type="text" name="plat_nomor" id="edit_plat_nomor" class="form-input-dark" required>
                </div>
                <div>
                    <label class="form-label-dark" for="edit_harga_sewa_per_hari">Harga/Hari (Rp)</label>
                    <input type="number" name="harga_sewa_per_hari" id="edit_harga_sewa_per_hari" class="form-input-dark" required>
                </div>
            </div>

            <!-- Edit Specs Section -->
            <div style="border-top: 1px dashed var(--color-hairline); border-bottom: 1px dashed var(--color-hairline); padding: 12px 0; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--color-primary); display: block; margin-bottom: 8px;">Edit Spesifikasi Kendaraan</span>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 8px;">
                    <div>
                        <label class="form-label-dark" for="edit_kapasitas_kursi">Kapasitas Kursi</label>
                        <input type="text" name="kapasitas_kursi" id="edit_kapasitas_kursi" class="form-input-dark" placeholder="Contoh: 7 Kursi">
                    </div>
                    <div>
                        <label class="form-label-dark" for="edit_transmisi">Tipe Transmisi</label>
                        <input type="text" name="transmisi" id="edit_transmisi" class="form-input-dark" placeholder="Contoh: Manual / Matic">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="form-label-dark" for="edit_bahan_bakar">Bahan Bakar</label>
                        <input type="text" name="bahan_bakar" id="edit_bahan_bakar" class="form-input-dark" placeholder="Contoh: Bensin">
                    </div>
                    <div>
                        <label class="form-label-dark" for="edit_kapasitas_mesin">Kapasitas Mesin (CC)</label>
                        <input type="text" name="kapasitas_mesin" id="edit_kapasitas_mesin" class="form-input-dark" placeholder="Contoh: 1.500 cc">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label class="form-label-dark" for="edit_foto_name">Nama File Foto</label>
                <input type="text" name="foto_name" id="edit_foto_name" class="form-input-dark" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label-dark" for="edit_deskripsi">Deskripsi Mobil</label>
                <textarea name="deskripsi" id="edit_deskripsi" class="form-input-dark" style="height: 70px; resize: none; padding-top: var(--spacing-xxs);"></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeEditModal()" class="btn-outline-dark-ferrari" style="height: 38px; padding: 0 16px;">Batal</button>
                <button type="submit" class="btn-primary-ferrari" style="height: 38px; padding: 0 20px;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    function openEditModal(car) {
        document.getElementById('edit_id_mobil').value = car.id_mobil;
        document.getElementById('edit_nama_mobil').value = car.nama_mobil;
        document.getElementById('edit_merk').value = car.merk;
        document.getElementById('edit_id_kategori').value = car.id_kategori;
        document.getElementById('edit_status').value = car.status;
        document.getElementById('edit_tahun').value = car.tahun;
        document.getElementById('edit_plat_nomor').value = car.plat_nomor;
        document.getElementById('edit_harga_sewa_per_hari').value = car.harga_sewa_per_hari;
        
        document.getElementById('edit_kapasitas_kursi').value = car.kapasitas_kursi || '7 Kursi';
        document.getElementById('edit_transmisi').value = car.transmisi || 'Manual / Matic';
        document.getElementById('edit_bahan_bakar').value = car.bahan_bakar || 'Bensin';
        document.getElementById('edit_kapasitas_mesin').value = car.kapasitas_mesin || '1.500 cc';
        
        document.getElementById('edit_foto_name').value = car.foto;
        document.getElementById('edit_deskripsi').value = car.deskripsi || '';

        document.getElementById('edit-car-modal').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('edit-car-modal').style.display = 'none';
    }
</script>

<?php 
$base_url = "../";
require_once '../includes/footer.php'; 
?>
