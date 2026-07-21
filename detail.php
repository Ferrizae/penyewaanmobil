<?php
// detail.php
require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$car = null;
$db_connected = false;

// Try database loading
try {
    if (isset($pdo) && $id > 0) {
        $stmt = $pdo->prepare("SELECT m.*, k.nama_kategori FROM mobil m JOIN kategori_mobil k ON m.id_kategori = k.id_kategori WHERE m.id_mobil = ?");
        $stmt->execute([$id]);
        $car = $stmt->fetch();
        if ($car) {
            $db_connected = true;
        }
    }
} catch (PDOException $e) {
    $db_connected = false;
}

// Fallback Mock Data if DB offline or car not found
if (!$db_connected || !$car) {
    $mock_cars = [
        1 => [
            'id_mobil' => 1,
            'nama_mobil' => 'Avanza',
            'merk' => 'Toyota',
            'tahun' => 2023,
            'plat_nomor' => 'DR 1902 SA',
            'harga_sewa_per_hari' => 350000.00,
            'status' => 'tersedia',
            'foto' => 'avanza.jpg',
            'deskripsi' => 'Toyota Avanza merupakan mobil MPV keluarga terfavorit di Indonesia, menawarkan kabin lapang dengan 7 kursi penumpang, AC double blower, dan kenyamanan berkendara terbaik untuk seluruh anggota keluarga.',
            'nama_kategori' => 'MPV Keluarga'
        ],
        2 => [
            'id_mobil' => 2,
            'nama_mobil' => 'Xpander',
            'merk' => 'Mitsubishi',
            'tahun' => 2022,
            'plat_nomor' => 'DR 8008 FT',
            'harga_sewa_per_hari' => 450000.00,
            'status' => 'tersedia',
            'foto' => 'xpander.jpg',
            'deskripsi' => 'Mitsubishi Xpander hadir dengan desain eksterior yang gagah, interior mewah senyap, suspensi stabil, serta ruang kabin lega yang ideal untuk petualangan keluarga Anda.',
            'nama_kategori' => 'MPV Keluarga'
        ],
        3 => [
            'id_mobil' => 3,
            'nama_mobil' => 'Kijang Innova',
            'merk' => 'Toyota',
            'tahun' => 2021,
            'plat_nomor' => 'DR 8128 SF',
            'harga_sewa_per_hari' => 650000.00,
            'status' => 'tersedia',
            'foto' => 'innova.jpg',
            'deskripsi' => 'Toyota Kijang Innova Reborn menghadirkan kemewahan berkelas, kenyamanan maksimal dengan ruang kaki luas, performa mesin tangguh, dan sangat cocok untuk perjalanan jarak jauh.',
            'nama_kategori' => 'MPV Keluarga'
        ],
        4 => [
            'id_mobil' => 4,
            'nama_mobil' => 'All New Veloz',
            'merk' => 'Toyota',
            'tahun' => 2023,
            'plat_nomor' => 'DR 2525 RM',
            'harga_sewa_per_hari' => 450000.00,
            'status' => 'tersedia',
            'foto' => 'veloz.jpg',
            'deskripsi' => 'Toyota All New Veloz menyuguhkan fitur keselamatan mutakhir Toyota Safety Sense, desain modern premium, kabin fleksibel, dan kenyamanan prima untuk mobilitas urban keluarga.',
            'nama_kategori' => 'MPV Keluarga'
        ],
        5 => [
            'id_mobil' => 5,
            'nama_mobil' => 'Pajero Sport',
            'merk' => 'Mitsubishi',
            'tahun' => 2022,
            'plat_nomor' => 'DR 1555 PS',
            'harga_sewa_per_hari' => 850000.00,
            'status' => 'tersedia',
            'foto' => 'pajero.jpg',
            'deskripsi' => 'Mitsubishi Pajero Sport adalah SUV premium dengan mesin tangguh, ground clearance tinggi, fitur keselamatan lengkap, cocok untuk menaklukkan segala kondisi jalan dengan gagah.',
            'nama_kategori' => 'SUV Tangguh'
        ],
        6 => [
            'id_mobil' => 6,
            'nama_mobil' => 'Jazz',
            'merk' => 'Honda',
            'tahun' => 2020,
            'plat_nomor' => 'DR 9099 JZ',
            'harga_sewa_per_hari' => 350000.00,
            'status' => 'tersedia',
            'foto' => 'jazz.jpg',
            'deskripsi' => 'Honda Jazz merupakan hatchback lincah nan sporty, kabin fleksibel dengan sistem Ultra Seat, efisiensi bahan bakar tinggi, sangat ideal untuk bermanuver di jalan perkotaan.',
            'nama_kategori' => 'Hatchback / City Car'
        ],
        7 => [
            'id_mobil' => 7,
            'nama_mobil' => 'Civic Turbo',
            'merk' => 'Honda',
            'tahun' => 2023,
            'plat_nomor' => 'DR 1000 CV',
            'harga_sewa_per_hari' => 900000.00,
            'status' => 'tersedia',
            'foto' => 'civic.jpg',
            'deskripsi' => 'Honda Civic Sedan menyajikan desain eksterior agresif premium, kenyamanan berkendara tingkat tinggi, performa mesin turbo yang responsif, mencerminkan prestise dan gaya hidup modern.',
            'nama_kategori' => 'Sedan Elegan'
        ]
    ];
    
    // Default to Avanza if invalid id
    $car = isset($mock_cars[$id]) ? $mock_cars[$id] : $mock_cars[1];
}

// Define specs dynamically from car object (or fallback defaults if empty)
$car_spec = [
    'seats'        => !empty($car['kapasitas_kursi']) ? $car['kapasitas_kursi'] : '7 Kursi',
    'transmission' => !empty($car['transmisi']) ? $car['transmisi'] : 'Manual / Matic',
    'fuel'         => !empty($car['bahan_bakar']) ? $car['bahan_bakar'] : 'Bensin',
    'engine_cap'   => !empty($car['kapasitas_mesin']) ? $car['kapasitas_mesin'] : '1.500 cc'
];

$page_title = (!empty($car['merk']) && stripos($car['nama_mobil'], $car['merk']) === 0) ? $car['nama_mobil'] : $car['merk'] . " " . $car['nama_mobil'];
require_once 'includes/header.php';
?>

<section class="section-band" style="background-color: var(--color-canvas); padding: var(--spacing-lg) 0;">
    <div class="grid-container">
        
        <!-- Back Navigation Link -->
        <a href="index.php" style="color: var(--color-muted); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 8px; margin-bottom: var(--spacing-md);">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog
        </a>

        <div class="grid-2-col">
            
            <!-- Car Image Column -->
            <div>
                <div style="background-color: #0c0c0c; border: 1px solid var(--color-hairline); padding: 0; line-height: 0;">
                    <img src="assets/img/<?= htmlspecialchars($car['foto']) ?>" alt="<?= htmlspecialchars($car['nama_mobil']) ?>" style="width: 100%; object-fit: cover;">
                </div>
                
                <!-- Performance Spec Cells -->
                <div class="spec-grid" style="margin-top: var(--spacing-md);">
                    <div class="spec-cell-ferrari">
                        <div class="spec-value"><?= $car_spec['seats'] ?></div>
                        <div class="spec-label">Kapasitas Kursi</div>
                    </div>
                    <div class="spec-cell-ferrari">
                        <div class="spec-value" style="font-size: 24px; font-weight: 700; line-height: 1.6;"><?= $car_spec['transmission'] ?></div>
                        <div class="spec-label">Tipe Transmisi</div>
                    </div>
                    <div class="spec-cell-ferrari">
                        <div class="spec-value" style="font-size: 24px; font-weight: 700; line-height: 1.6;"><?= $car_spec['fuel'] ?></div>
                        <div class="spec-label">Bahan Bakar</div>
                    </div>
                    <div class="spec-cell-ferrari">
                        <div class="spec-value"><?= $car_spec['engine_cap'] ?></div>
                        <div class="spec-label">Kapasitas Mesin</div>
                    </div>
                </div>
            </div>

            <!-- Car Rental Form Column -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline); height: fit-content;">
                
                <span class="caption-uppercase" style="color: var(--color-primary);"><?= htmlspecialchars($car['nama_kategori']) ?></span>
                <h2 class="display-md" style="margin-top: var(--spacing-xxxs); margin-bottom: var(--spacing-xxs); color: var(--color-ink);">
                    <?= htmlspecialchars((!empty($car['merk']) && stripos($car['nama_mobil'], $car['merk']) === 0) ? $car['nama_mobil'] : $car['merk'] . ' ' . $car['nama_mobil']) ?>
                </h2>
                
                <div style="display: flex; gap: var(--spacing-xs); align-items: center; margin-bottom: var(--spacing-sm);">
                    <span class="display-md" style="color: var(--color-ink); font-weight: 700; font-size: 24px;">
                        Rp <?= number_format($car['harga_sewa_per_hari'], 0, ',', '.') ?>
                    </span>
                    <span style="color: var(--color-muted);">/ Hari</span>
                    
                    <?php if ($car['status'] === 'tersedia'): ?>
                        <span class="badge-pill-ferrari available" style="margin-left: auto;">Tersedia</span>
                    <?php elseif ($car['status'] === 'disewa'): ?>
                        <span class="badge-pill-ferrari rented" style="margin-left: auto;">Sedang Disewa</span>
                    <?php else: ?>
                        <span class="badge-pill-ferrari maintenance" style="margin-left: auto;">Dalam Perbaikan</span>
                    <?php endif; ?>
                </div>

                <p class="body-md" style="color: var(--color-body); margin-bottom: var(--spacing-md); line-height: 1.6; border-top: 1px solid var(--color-hairline); padding-top: var(--spacing-sm);">
                    <?= htmlspecialchars($car['deskripsi']) ?>
                </p>

                <!-- Booking Panel -->
                <div style="background-color: var(--color-canvas); padding: var(--spacing-sm); border: 1px solid var(--color-hairline); margin-top: var(--spacing-md);">
                    <h3 class="title-sm" style="margin-bottom: var(--spacing-xs); text-transform: uppercase;">Formulir Penyewaan</h3>
                    
                    <?php if ($car['status'] !== 'tersedia'): ?>
                        <div class="alert-ferrari error" style="margin-bottom: 0;">
                            <i class="fa-solid fa-ban"></i>
                            <span>Maaf, kendaraan saat ini tidak dapat disewa.</span>
                        </div>
                    <?php elseif (isset($_SESSION['role'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <div class="alert-ferrari error" style="margin-bottom: 0;">
                                <i class="fa-solid fa-user-gear"></i>
                                <span>Akun admin tidak dapat melakukan penyewaan mobil. Silakan gunakan akun customer.</span>
                            </div>
                        <?php else: ?>
                            <form action="rent.php" method="POST">
                                <input type="hidden" name="id_mobil" value="<?= $car['id_mobil'] ?>">
                                
                                <div style="margin-bottom: var(--spacing-xs);">
                                    <label class="form-label-dark" for="tanggal_sewa">Tanggal Pengambilan</label>
                                    <input type="date" name="tanggal_sewa" id="tanggal_sewa" class="form-input-dark" min="<?= date('Y-m-d') ?>" required>
                                </div>
                                
                                <div style="margin-bottom: var(--spacing-sm);">
                                    <label class="form-label-dark" for="tanggal_kembali">Tanggal Pengembalian</label>
                                    <input type="date" name="tanggal_kembali" id="tanggal_kembali" class="form-input-dark" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                </div>

                                <button type="submit" class="btn-primary-ferrari" style="width: 100%;">Mulai Sewa Mobil</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="font-size: 13px; color: var(--color-muted); margin-bottom: var(--spacing-xs);">Anda harus masuk ke akun pelanggan untuk memesan kendaraan.</p>
                        <a href="login.php" class="btn-primary-ferrari" style="width: 100%;">Masuk Akun</a>
                        <a href="register.php" class="btn-outline-dark-ferrari" style="width: 100%; margin-top: var(--spacing-xxs);">Daftar Akun Baru</a>
                    <?php endif; ?>
                </div>

                <div style="margin-top: var(--spacing-sm); font-size: 12px; color: var(--color-muted);">
                    <p><i class="fa-solid fa-circle-info"></i> Minimal durasi sewa adalah 1 hari.</p>
                    <p><i class="fa-solid fa-id-card"></i> Wajib menyertakan SIM A yang aktif pada profil Anda.</p>
                </div>

            </div>

        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
