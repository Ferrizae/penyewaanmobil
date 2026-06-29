<?php
// index.php
$page_title = "Sewa Mobil Keluarga & Standar Terpercaya";
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
        ['id_kategori' => 1, 'nama_kategori' => 'MPV Keluarga'],
        ['id_kategori' => 2, 'nama_kategori' => 'SUV Tangguh'],
        ['id_kategori' => 3, 'nama_kategori' => 'Hatchback / City Car'],
        ['id_kategori' => 4, 'nama_kategori' => 'Sedan Elegan']
    ];

    $cars = [
        [
            'id_mobil' => 1,
            'id_kategori' => 1,
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
        [
            'id_mobil' => 2,
            'id_kategori' => 1,
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
        [
            'id_mobil' => 3,
            'id_kategori' => 1,
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
        [
            'id_mobil' => 4,
            'id_kategori' => 1,
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
        [
            'id_mobil' => 5,
            'id_kategori' => 2,
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
        [
            'id_mobil' => 6,
            'id_kategori' => 3,
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
        [
            'id_mobil' => 7,
            'id_kategori' => 4,
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
    <img src="assets/img/hero_bg.jpg" alt="Family road trip" class="hero-image-bg">
    <div class="hero-content">
        <div class="hero-text-container">
            <p class="caption-uppercase hero-tagline">Sewa Mobil Keluarga & Standar Terpercaya</p>
            <h1 class="display-mega">PERJALANAN NYAMAN BERSAMA KELUARGA</h1>
            <p class="body-md" style="margin-top: var(--spacing-xs); color: rgba(255,255,255,0.7); font-size: 16px; line-height: 1.6;">
                Kenyamanan dan keamanan perjalanan Anda serta keluarga adalah prioritas utama kami. Family Drive menyediakan berbagai pilihan mobil MPV, SUV, dan sedan prima untuk liburan maupun kebutuhan harian Anda.
            </p>
            <div class="hero-buttons">
                <a href="#katalog-armada" class="btn-primary-ferrari">Lihat Katalog</a>
                <a href="register.php" class="btn-outline-dark-ferrari">Daftar Akun</a>
            </div>
        </div>
    </div>
</section>

<!-- Livery Band (Accent Blue Band) -->
<section class="section-band livery" style="padding: var(--spacing-md) 0; text-align: center;">
    <div class="grid-container">
        <p class="caption-uppercase" style="letter-spacing: 2px; font-weight: 700;">
            <i class="fa-solid fa-car-side" style="margin-right: 8px;"></i>
            Solusi Sewa Mobil Keluarga Nyaman & Murah Hanya di Family Drive
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
        <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xxs);">Perjalanan Terbaik Bersama Kami</p>
        <h2 class="display-lg" style="margin-bottom: var(--spacing-sm);">Siap Memulai Perjalanan Nyaman Bersama Keluarga?</h2>
        <p class="body-md" style="margin-bottom: var(--spacing-md); color: var(--color-body); line-height: 1.6;">
            Daftar sekarang untuk memesan kendaraan keluarga pilihan Anda. Layanan pelanggan 24/7 kami siap membantu mempersiapkan perjalanan terbaik untuk Anda dan keluarga.
        </p>
        <a href="register.php" class="btn-primary-ferrari">Daftar Akun Sekarang</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
