<?php
// includes/footer.php
$base_url = "/penyewaanmobil/";
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
                    <li><a href="<?= $base_url ?>index.php">Semua Mobil</a></li>
                    <li><a href="<?= $base_url ?>index.php?kategori=1">MPV Keluarga</a></li>
                    <li><a href="<?= $base_url ?>index.php?kategori=2">SUV Tangguh</a></li>
                    <li><a href="<?= $base_url ?>index.php?kategori=3">Hatchback / City Car</a></li>
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
