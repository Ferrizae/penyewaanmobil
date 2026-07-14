<?php
// includes/footer.php
$base_url = "/penyewaanmobil/";

// Try to fetch categories dynamically if PDO exists
$footer_categories = [];
try {
    if (isset($pdo)) {
        $stmt_footer = $pdo->query("SELECT * FROM kategori_mobil");
        $footer_categories = $stmt_footer->fetchAll();
    }
} catch (Exception $e) {
    // Ignore error, fallback below
}

// Fallback if not loaded
if (empty($footer_categories)) {
    $footer_categories = [
        ['id_kategori' => 1, 'nama_kategori' => 'MPV Keluarga'],
        ['id_kategori' => 2, 'nama_kategori' => 'SUV Tangguh'],
        ['id_kategori' => 3, 'nama_kategori' => 'Hatchback / City Car'],
        ['id_kategori' => 4, 'nama_kategori' => 'Sedan Elegan']
    ];
}
?>
</main>

<footer class="footer-ferrari">
    <div class="grid-container">
        <div class="footer-grid">
            <div>
                <a href="<?= $base_url ?>index.php" class="brand-mark" style="margin-bottom: var(--spacing-xs);">
                    <i class="fa-solid fa-car-side"></i> FAMILY <span class="accent">DRIVE</span>
                </a>
                <p style="margin-top: var(--spacing-xs); font-size: 13px; line-height: 1.6; max-width: 300px;">
                    Sistem penyewaan mobil keluarga dan mobil standar terpercaya. Nikmati perjalanan aman, nyaman, dan berkesan bersama seluruh anggota keluarga Anda.
                </p>
            </div>
            <div>
                <h4 class="footer-col-title">Katalog</h4>
                <ul class="footer-links">
                    <li><a href="<?= $base_url ?>index.php#katalog-armada">Semua Mobil</a></li>
                    <?php foreach ($footer_categories as $cat): ?>
                        <li><a href="<?= $base_url ?>index.php?kategori=<?= $cat['id_kategori'] ?>#katalog-armada"><?= htmlspecialchars($cat['nama_kategori']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div>
                <h4 class="footer-col-title">Tentang</h4>
                <ul class="footer-links">
                    <li><a href="<?= $base_url ?>about.php">Profil Perusahaan</a></li>
                    <li><a href="<?= $base_url ?>services.php">Layanan Kami</a></li>
                    <li><a href="<?= $base_url ?>terms.php">Syarat & Ketentuan</a></li>
                    <li><a href="<?= $base_url ?>privacy.php">Kebijakan Privasi</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-col-title">Hubungi Kami</h4>
                <ul class="footer-links">
                    <li><span style="font-size: 13px;"><i class="fa-solid fa-envelope"></i> info@familydriverental.com</span></li>
                    <li><span style="font-size: 13px;"><i class="fa-solid fa-phone"></i> +62 87 863 664 414</span></li>
                    <li><span style="font-size: 13px;"><i class="fa-solid fa-location-dot"></i> Mataram, NTB, Indonesia</span></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Family Drive. All rights reserved. Solusi Sewa Mobil Keluarga & Standar Terpercaya.</p>
            <div style="display: flex; gap: var(--spacing-xs);">
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-youtube"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
