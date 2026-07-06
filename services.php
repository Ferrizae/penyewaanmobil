<?php
// services.php
$page_title = "Layanan Premium";
require_once 'includes/header.php';
?>

<!-- Banner Section -->
<section class="section-band" style="background-color: var(--color-canvas); border-bottom: 1px solid var(--color-hairline); padding: var(--spacing-xl) 0;">
    <div class="grid-container">
        <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xxs);">Kenyamanan & Keandalan</p>
        <h1 class="display-xl" style="margin-bottom: var(--spacing-sm);">LAYANAN UTAMA</h1>
        <p class="body-md" style="color: var(--color-body); max-width: 800px; font-size: 16px; line-height: 1.6;">
            Di Family Drive, kami menyajikan layanan transportasi terpercaya untuk memenuhi setiap rencana perjalanan Anda. Nikmati fleksibilitas sewa lepas kunci maupun dengan supir profesional yang handal.
        </p>
    </div>
</section>

<!-- Services Grid -->
<section class="section-band" style="background-color: var(--color-canvas); border-bottom: 1px solid var(--color-hairline);">
    <div class="grid-container">
        <div class="grid-2-col">
            <!-- Service Item 1 -->
            <div class="card-photo-ferrari">
                <a href="service_detail.php?type=lepas-kunci" style="display: block; color: inherit;">
                    <div class="card-image-wrapper">
                        <img src="assets/img/avanza.jpg" alt="Sewa Lepas Kunci (Self-Drive)" class="card-image">
                    </div>
                </a>
                <div class="card-info">
                    <span class="caption-uppercase card-category">Bebas & Fleksibel</span>
                    <h3 class="title-md card-title" style="margin-bottom: var(--spacing-xs); font-size: 20px;">
                        <a href="service_detail.php?type=lepas-kunci" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'">Sewa Lepas Kunci (Self-Drive)</a>
                    </h3>
                    <p class="body-md card-desc" style="line-height: 1.6;">
                        Pegang kendali penuh perjalanan Anda. Sewa mobil lepas kunci memberikan privasi maksimal bagi Anda dan keluarga untuk menjelajahi berbagai destinasi wisata dengan waktu yang sangat fleksibel.
                    </p>
                    <div style="margin-top: var(--spacing-xs); padding-top: var(--spacing-xs); border-top: 1px solid var(--color-hairline); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-muted);"><i class="fa-solid fa-check-double" style="color: var(--color-primary); margin-right: 4px;"></i> Termasuk Asuransi</span>
                        <a href="service_detail.php?type=lepas-kunci" class="btn-outline-dark-ferrari" style="height: 36px; padding: 0 var(--spacing-xs); font-size: 11px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">Detail Layanan <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i></a>
                    </div>
                </div>
            </div>

            <!-- Service Item 2 -->
            <div class="card-photo-ferrari">
                <a href="service_detail.php?type=driver" style="display: block; color: inherit;">
                    <div class="card-image-wrapper">
                        <img src="assets/img/veloz.jpg" alt="Layanan Driver Profesional" class="card-image">
                    </div>
                </a>
                <div class="card-info">
                    <span class="caption-uppercase card-category">Kenyamanan Maksimal</span>
                    <h3 class="title-md card-title" style="margin-bottom: var(--spacing-xs); font-size: 20px;">
                        <a href="service_detail.php?type=driver" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'">Layanan Driver Profesional</a>
                    </h3>
                    <p class="body-md card-desc" style="line-height: 1.6;">
                        Duduk santai di kursi penumpang tanpa lelah berkendara. Supir profesional kami yang berpengalaman, ramah, dan menguasai rute jalan siap mengantarkan Anda sekeluarga dengan aman dan tepat waktu.
                    </p>
                    <div style="margin-top: var(--spacing-xs); padding-top: var(--spacing-xs); border-top: 1px solid var(--color-hairline); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-muted);"><i class="fa-solid fa-check-double" style="color: var(--color-primary); margin-right: 4px;"></i> Driver Bersertifikat</span>
                        <a href="service_detail.php?type=driver" class="btn-outline-dark-ferrari" style="height: 36px; padding: 0 var(--spacing-xs); font-size: 11px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">Detail Layanan <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i></a>
                    </div>
                </div>
            </div>

            <!-- Service Item 3 -->
            <div class="card-photo-ferrari">
                <a href="service_detail.php?type=antar-jemput" style="display: block; color: inherit;">
                    <div class="card-image-wrapper">
                        <img src="assets/img/innova.jpg" alt="Antar Jemput Bandara & Hotel" class="card-image">
                    </div>
                </a>
                <div class="card-info">
                    <span class="caption-uppercase card-category">Tepat Waktu & Praktis</span>
                    <h3 class="title-md card-title" style="margin-bottom: var(--spacing-xs); font-size: 20px;">
                        <a href="service_detail.php?type=antar-jemput" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'">Antar Jemput Bandara & Hotel</a>
                    </h3>
                    <p class="body-md card-desc" style="line-height: 1.6;">
                        Kami menghargai efisiensi waktu Anda. Layanan antar jemput langsung ke bandara atau lobi hotel memudahkan mobilitas perjalanan bisnis maupun liburan keluarga begitu Anda tiba di lokasi.
                    </p>
                    <div style="margin-top: var(--spacing-xs); padding-top: var(--spacing-xs); border-top: 1px solid var(--color-hairline); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-muted);"><i class="fa-solid fa-check-double" style="color: var(--color-primary); margin-right: 4px;"></i> Pengiriman Instan</span>
                        <a href="service_detail.php?type=antar-jemput" class="btn-outline-dark-ferrari" style="height: 36px; padding: 0 var(--spacing-xs); font-size: 11px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">Detail Layanan <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i></a>
                    </div>
                </div>
            </div>

            <!-- Service Item 4 -->
            <div class="card-photo-ferrari">
                <a href="service_detail.php?type=korporat" style="display: block; color: inherit;">
                    <div class="card-image-wrapper">
                        <img src="assets/img/xpander.jpg" alt="Sewa Korporat & Acara Keluarga" class="card-image">
                    </div>
                </a>
                <div class="card-info">
                    <span class="caption-uppercase card-category">Solusi Transportasi</span>
                    <h3 class="title-md card-title" style="margin-bottom: var(--spacing-xs); font-size: 20px;">
                        <a href="service_detail.php?type=korporat" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'">Sewa Korporat & Acara Keluarga</a>
                    </h3>
                    <p class="body-md card-desc" style="line-height: 1.6;">
                        Menyediakan paket sewa armada mobil bulanan untuk operasional instansi/perusahaan, serta paket penyewaan khusus rombongan keluarga untuk keperluan pernikahan, wisuda, maupun acara penting lainnya.
                    </p>
                    <div style="margin-top: var(--spacing-xs); padding-top: var(--spacing-xs); border-top: 1px solid var(--color-hairline); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--color-muted);"><i class="fa-solid fa-check-double" style="color: var(--color-primary); margin-right: 4px;"></i> Kustomisasi Paket</span>
                        <a href="service_detail.php?type=korporat" class="btn-outline-dark-ferrari" style="height: 36px; padding: 0 var(--spacing-xs); font-size: 11px; display: inline-flex; align-items: center; justify-content: center; gap: 4px;">Detail Layanan <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Additional VIP Features -->
<section class="section-band" style="background-color: var(--color-canvas-elevated);">
    <div class="grid-container">
        <p class="caption-uppercase" style="color: var(--color-primary); text-align: center; margin-bottom: var(--spacing-xxs);">Benefit Eksklusif</p>
        <h2 class="display-lg" style="text-align: center; margin-bottom: var(--spacing-lg); color: var(--color-ink);">Standar Keunggulan Kami</h2>
        
        <div class="grid-3-col">
            <div style="border-top: 1px solid var(--color-hairline); padding-top: var(--spacing-xs);">
                <span class="caption-uppercase" style="color: var(--color-primary); font-weight: 700;">01. Dukungan Jalan 24/7</span>
                <p class="body-md" style="margin-top: var(--spacing-xxs); color: var(--color-body); font-size: 13px; line-height: 1.6;">
                    Tim bantuan darurat kami selalu siaga kapan pun dan di mana pun Anda membutuhkan dukungan teknis atau bantuan di jalan raya.
                </p>
            </div>
            <div style="border-top: 1px solid var(--color-hairline); padding-top: var(--spacing-xs);">
                <span class="caption-uppercase" style="color: var(--color-primary); font-weight: 700;">02. Kebersihan Higienis</span>
                <p class="body-md" style="margin-top: var(--spacing-xxs); color: var(--color-body); font-size: 13px; line-height: 1.6;">
                    Setiap interior dan eksterior kendaraan melewati proses sanitasi mendalam sebelum keberangkatan untuk menjamin kesegaran kabin.
                </p>
            </div>
            <div style="border-top: 1px solid var(--color-hairline); padding-top: var(--spacing-xs);">
                <span class="caption-uppercase" style="color: var(--color-primary); font-weight: 700;">03. Fleksibilitas Rute</span>
                <p class="body-md" style="margin-top: var(--spacing-xxs); color: var(--color-body); font-size: 13px; line-height: 1.6;">
                    Nikmati kebebasan berkendara melintasi rute-rute wisata terindah dengan peta panduan navigasi khusus yang terintegrasi di kendaraan.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
