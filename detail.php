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
            'nama_mobil' => 'SF90 Stradale',
            'merk' => 'Ferrari',
            'tahun' => 2023,
            'plat_nomor' => 'B 90 SF',
            'harga_sewa_per_hari' => 15000000.00,
            'status' => 'tersedia',
            'foto' => 'sf90.jpg',
            'deskripsi' => 'Plug-in Hybrid supercar featuring a twin-turbo V8 engine and three electric motors, generating a total of 1000 cv (986 hp). Pure performance redrawn for the future.',
            'nama_kategori' => 'Hybrid Hypercar'
        ],
        2 => [
            'id_mobil' => 2,
            'nama_mobil' => 'F8 Tributo',
            'merk' => 'Ferrari',
            'tahun' => 2022,
            'plat_nomor' => 'B 8 FT',
            'harga_sewa_per_hari' => 10000000.00,
            'status' => 'tersedia',
            'foto' => 'f8.jpg',
            'deskripsi' => 'The tribute to the ultimate V8 engine. Delivering 720 cv of instant power without turbo lag, offering unmatched driver involvement on road and track.',
            'nama_kategori' => 'Supercar V8'
        ],
        3 => [
            'id_mobil' => 3,
            'nama_mobil' => '812 Superfast',
            'merk' => 'Ferrari',
            'tahun' => 2021,
            'plat_nomor' => 'B 812 SF',
            'harga_sewa_per_hari' => 12000000.00,
            'status' => 'tersedia',
            'foto' => '812.jpg',
            'deskripsi' => 'Front mid-mounted 6.5-liter naturally aspirated V12 engine. The fastest and most powerful road-going Ferrari of its era, outputting 800 cv of symphonic power.',
            'nama_kategori' => 'V12 Grand Tourer'
        ],
        4 => [
            'id_mobil' => 4,
            'nama_mobil' => 'Roma',
            'merk' => 'Ferrari',
            'tahun' => 2023,
            'plat_nomor' => 'B 25 RM',
            'harga_sewa_per_hari' => 8000000.00,
            'status' => 'tersedia',
            'foto' => 'roma.jpg',
            'deskripsi' => 'La Nuova Dolce Vita. A timeless, elegant, and minimal front-engined V8 coupe designed to represent the carefree, pleasurable way of life in Rome during the 1950s and 60s.',
            'nama_kategori' => 'V12 Grand Tourer'
        ]
    ];
    
    // Default to SF90 if invalid id
    $car = isset($mock_cars[$id]) ? $mock_cars[$id] : $mock_cars[1];
}

// Define performance specs dynamically based on model for the "Ferrari-feeling" spec grid
$specs = [
    'SF90 Stradale' => ['power' => '1000 CV', 'top_speed' => '340 km/h', 'acceleration' => '2.5s', 'engine' => 'V8 Hybrid'],
    'F8 Tributo'    => ['power' => '720 CV', 'top_speed' => '340 km/h', 'acceleration' => '2.9s', 'engine' => 'V8 Turbo'],
    '812 Superfast' => ['power' => '800 CV', 'top_speed' => '340 km/h', 'acceleration' => '2.9s', 'engine' => 'V12 NA'],
    'Roma'          => ['power' => '620 CV', 'top_speed' => '320 km/h', 'acceleration' => '3.4s', 'engine' => 'V8 Turbo']
];

$car_spec = isset($specs[$car['nama_mobil']]) ? $specs[$car['nama_mobil']] : ['power' => '600+ CV', 'top_speed' => '300+ km/h', 'acceleration' => 'under 4s', 'engine' => 'Ferrari Engine'];

$page_title = $car['merk'] . " " . $car['nama_mobil'];
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
                        <div class="spec-value"><?= $car_spec['power'] ?></div>
                        <div class="spec-label">Tenaga Maksimum</div>
                    </div>
                    <div class="spec-cell-ferrari">
                        <div class="spec-value"><?= $car_spec['top_speed'] ?></div>
                        <div class="spec-label">Kecepatan Puncak</div>
                    </div>
                    <div class="spec-cell-ferrari">
                        <div class="spec-value"><?= $car_spec['acceleration'] ?></div>
                        <div class="spec-label">0 - 100 km/h</div>
                    </div>
                    <div class="spec-cell-ferrari">
                        <div class="spec-value"><?= $car_spec['engine'] ?></div>
                        <div class="spec-label">Tipe Mesin</div>
                    </div>
                </div>
            </div>

            <!-- Car Rental Form Column -->
            <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline); height: fit-content;">
                
                <span class="caption-uppercase" style="color: var(--color-primary);"><?= htmlspecialchars($car['nama_kategori']) ?></span>
                <h2 class="display-md" style="margin-top: var(--spacing-xxxs); margin-bottom: var(--spacing-xxs); color: var(--color-ink);">
                    <?= htmlspecialchars($car['merk'] . ' ' . $car['nama_mobil']) ?>
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
