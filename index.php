<?php
// index.php
$page_title = "Katalog Supercar Premium";
require_once 'config/db.php';

$cars = [];
$categories = [];
$db_connected = false;

// Try to fetch from database
try {
    if (isset($pdo)) {
        // Fetch categories
        $stmt_cat = $pdo->query("SELECT * FROM kategori_mobil");
        $categories = $stmt_cat->fetchAll();

        // Fetch cars with category name
        $query = "SELECT m.*, k.nama_kategori FROM mobil m JOIN kategori_mobil k ON m.id_kategori = k.id_kategori";
        
        // Filter by category if set
        if (isset($_GET['kategori']) && is_numeric($_GET['kategori'])) {
            $query .= " WHERE m.id_kategori = :kategori";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['kategori' => $_GET['kategori']]);
        } else {
            $stmt = $pdo->query($query);
        }
        $cars = $stmt->fetchAll();
        $db_connected = true;
    }
} catch (PDOException $e) {
    // Database connection or table fetch failed (MySQL might be turned off in XAMPP)
    // We fall back to mock data matching our seeds so the interface is still fully functional for preview!
    $db_connected = false;
}

// Fallback Mock Data if database is offline
if (!$db_connected) {
    $categories = [
        ['id_kategori' => 1, 'nama_kategori' => 'Supercar V8'],
        ['id_kategori' => 2, 'nama_kategori' => 'V12 Grand Tourer'],
        ['id_kategori' => 3, 'nama_kategori' => 'Hybrid Hypercar'],
        ['id_kategori' => 4, 'nama_kategori' => 'Luxury SUV']
    ];

    $cars = [
        [
            'id_mobil' => 1,
            'id_kategori' => 3,
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
        [
            'id_mobil' => 2,
            'id_kategori' => 1,
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
        [
            'id_mobil' => 3,
            'id_kategori' => 2,
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
        [
            'id_mobil' => 4,
            'id_kategori' => 2,
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

    // Simple manual filtering for the mock array
    if (isset($_GET['kategori']) && is_numeric($_GET['kategori'])) {
        $selected_cat = (int)$_GET['kategori'];
        $cars = array_filter($cars, function($car) use ($selected_cat) {
            return $car['id_kategori'] === $selected_cat;
        });
    }
}

require_once 'includes/header.php';
?>

<!-- Cinematic Hero Section -->
<section class="hero-viewport">
    <img src="assets/img/hero_bg.jpg" alt="Ferrari racetrack sunset" class="hero-image-bg">
    <div class="hero-content">
        <div class="hero-text-container">
            <p class="caption-uppercase hero-tagline">Premium Car Rental Experience</p>
            <h1 class="display-mega">RACING IS IN OUR DNA</h1>
            <p class="body-md" style="margin-top: var(--spacing-xs); color: rgba(255,255,255,0.7); font-size: 16px; line-height: 1.6;">
                Nikmati kekuatan sejati supercar legendaris. Scuderia Rental menyewakan kendaraan dengan performa maksimal dan presisi sempurna untuk perjalanan istimewa Anda.
            </p>
            <div class="hero-buttons">
                <a href="#katalog-armada" class="btn-primary-ferrari">Lihat Katalog</a>
                <a href="register.php" class="btn-outline-dark-ferrari">Daftar Akun</a>
            </div>
        </div>
    </div>
</section>

<!-- Livery Band (Accent Red Band) -->
<section class="section-band livery" style="padding: var(--spacing-md) 0; text-align: center;">
    <div class="grid-container">
        <p class="caption-uppercase" style="letter-spacing: 2px; font-weight: 700;">
            <i class="fa-solid fa-gauge-high" style="margin-right: 8px;"></i>
            Koleksi Terbatas Supercar Eksklusif Hanya di Scuderia Rental
        </p>
    </div>
</section>

<!-- Main Catalog Section -->
<section class="section-band" id="katalog-armada" style="background-color: var(--color-canvas);">
    <div class="grid-container">
        
        <?php if (!$db_connected): ?>
            <div class="alert-ferrari error" style="margin-bottom: var(--spacing-lg);">
                <i class="fa-solid fa-database"></i>
                <div>
                    <span style="font-weight: 600;">Catatan Offline:</span> Database MySQL lokal Anda tidak terdeteksi. Sistem secara otomatis menampilkan data demonstrasi (mockup) agar tampilan visual tetap berfungsi penuh.
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--spacing-lg);">
            <div>
                <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xxxs);">Pilihan Eksklusif</p>
                <h2 class="display-lg">Jelajahi Armada Kami</h2>
            </div>
            
            <!-- Category Filter Links -->
            <div style="display: flex; gap: var(--spacing-xs); flex-wrap: wrap;">
                <a href="index.php#katalog-armada" class="badge-pill-ferrari <?= !isset($_GET['kategori']) ? 'available' : '' ?>" style="padding: 8px 16px;">Semua</a>
                <?php foreach ($categories as $cat): ?>
                    <?php 
                        $is_active = (isset($_GET['kategori']) && $_GET['kategori'] == $cat['id_kategori']);
                    ?>
                    <a href="index.php?kategori=<?= $cat['id_kategori'] ?>#katalog-armada" 
                       class="badge-pill-ferrari <?= $is_active ? 'available' : '' ?>" 
                       style="padding: 8px 16px;">
                        <?= htmlspecialchars($cat['nama_kategori']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Car Fleet Grid -->
        <?php if (empty($cars)): ?>
            <div style="text-align: center; padding: var(--spacing-xl) 0; border: 1px dashed var(--color-hairline);">
                <i class="fa-solid fa-car-side" style="font-size: 48px; color: var(--color-muted); margin-bottom: var(--spacing-xs);"></i>
                <p style="color: var(--color-muted);">Tidak ada kendaraan yang tersedia pada kategori ini.</p>
            </div>
        <?php else: ?>
            <div class="grid-3-col">
                <?php foreach ($cars as $car): ?>
                    <div class="card-photo-ferrari">
                        <div class="card-image-wrapper">
                            <img src="assets/img/<?= htmlspecialchars($car['foto']) ?>" alt="<?= htmlspecialchars($car['nama_mobil']) ?>" class="card-image">
                        </div>
                        <div class="card-info">
                            <span class="caption-uppercase card-category"><?= htmlspecialchars($car['nama_kategori']) ?></span>
                            <div class="card-title-row">
                                <h3 class="title-md card-title"><?= htmlspecialchars($car['merk'] . ' ' . $car['nama_mobil']) ?></h3>
                                <span class="card-price">Rp <?= number_format($car['harga_sewa_per_hari'], 0, ',', '.') ?><span style="font-size: 11px; font-weight: 400; color: var(--color-body);">/hari</span></span>
                            </div>
                            <p class="body-md card-desc"><?= htmlspecialchars($car['deskripsi']) ?></p>
                            
                            <div class="card-actions">
                                <div>
                                    <?php if ($car['status'] === 'tersedia'): ?>
                                        <span class="badge-pill-ferrari available">Tersedia</span>
                                    <?php elseif ($car['status'] === 'disewa'): ?>
                                        <span class="badge-pill-ferrari rented">Disewa</span>
                                    <?php else: ?>
                                        <span class="badge-pill-ferrari maintenance">Perbaikan</span>
                                    <?php endif; ?>
                                </div>
                                <a href="detail.php?id=<?= $car['id_mobil'] ?>" class="btn-outline-dark-ferrari" style="height: 38px; padding: 0 var(--spacing-xs); font-size: 12px;">Detail & Sewa</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<!-- Call To Action Band -->
<section class="section-band" style="background-color: var(--color-canvas-elevated); border-top: 1px solid var(--color-hairline); border-bottom: 1px solid var(--color-hairline);">
    <div class="grid-container" style="text-align: center; max-width: 800px;">
        <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xxs);">Pengalaman Berkendara Terbaik</p>
        <h2 class="display-lg" style="margin-bottom: var(--spacing-sm);">Siap Merasakan Sensasi Kecepatan Sejati?</h2>
        <p class="body-md" style="margin-bottom: var(--spacing-md); color: var(--color-body); line-height: 1.6;">
            Daftar sekarang untuk memesan kendaraan impian Anda. Layanan pelanggan 24/7 kami siap membantu mempersiapkan perjalanan premium Anda.
        </p>
        <a href="register.php" class="btn-primary-ferrari">Daftar Akun Sekarang</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
