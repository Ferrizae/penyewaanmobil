<?php
// service_detail.php
require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$type = isset($_GET['type']) ? trim($_GET['type']) : 'lepas-kunci';

// Service detailed data dictionary
$services_data = [
    'lepas-kunci' => [
        'title' => 'Sewa Lepas Kunci (Self-Drive)',
        'category' => 'Bebas & Fleksibel',
        'subtitle' => 'Pegang kendali penuh atas petualangan dan privasi perjalanan Anda.',
        'image' => 'assets/img/avanza.jpg',
        'desc_1' => 'Dapatkan kebebasan mutlak dalam merencanakan rute dan waktu perjalanan Anda dengan layanan Sewa Lepas Kunci dari Family Drive. Cocok untuk Anda yang menginginkan privasi maksimal bersama keluarga, pasangan, atau untuk keperluan bisnis tanpa gangguan. Anda bebas menyetir sendiri ke destinasi mana pun dengan kenyamanan penuh layaknya menggunakan kendaraan pribadi.',
        'desc_2' => 'Seluruh armada kami, mulai dari MPV keluarga yang hemat bahan bakar hingga SUV tangguh untuk medan menantang, selalu melalui inspeksi multi-titik sebelum diserahterimakan. Kami memastikan kebersihan kabin yang higienis melalui proses disinfeksi menyeluruh demi keselamatan dan kenyamanan Anda.',
        'features' => [
            ['icon' => 'fa-clock', 'title' => 'Durasi Sewa 24 Jam', 'desc' => 'Masa sewa dihitung 24 jam penuh sejak waktu serah terima kendaraan.'],
            ['icon' => 'fa-shield-halved', 'title' => 'Proteksi Asuransi', 'desc' => 'Setiap unit dilindungi oleh asuransi komprehensif untuk meminimalkan risiko perjalanan.'],
            ['icon' => 'fa-gears', 'title' => 'Pilihan Transmisi', 'desc' => 'Tersedia pilihan transmisi Manual maupun Otomatis (CVT) sesuai kenyamanan berkendara Anda.'],
            ['icon' => 'fa-sparkles', 'title' => 'Sanitasi Kabin Higienis', 'desc' => 'Kendaraan dicuci bersih dan disterilkan secara menyeluruh sebelum digunakan.'],
        ],
        'faqs' => [
            ['q' => 'Apa saja persyaratan untuk sewa lepas kunci?', 'a' => 'Penyewa wajib menunjukkan dokumen identitas asli berupa KTP, Surat Izin Mengemudi (SIM A) yang masih aktif, serta dokumen pendukung lain seperti Kartu Keluarga atau bukti penginap jika Anda sedang berlibur.'],
            ['q' => 'Apakah ada biaya jaminan deposit?', 'a' => 'Ya, kami menerapkan sistem uang jaminan (deposit) yang nominalnya disesuaikan dengan jenis kendaraan. Deposit ini akan dikembalikan penuh setelah masa sewa berakhir jika kendaraan kembali dalam kondisi baik.'],
            ['q' => 'Bagaimana jika terjadi keterlambatan pengembalian?', 'a' => 'Keterlambatan pengembalian dikenakan biaya tambahan per jam (overtime) sebesar 10% dari tarif sewa harian. Keterlambatan lebih dari 3 jam akan dihitung sebagai sewa 1 hari penuh.'],
        ],
        'cta_text' => 'Lihat Katalog Mobil',
        'cta_link' => 'index.php#katalog-armada',
        'wa_message' => 'Halo Family Drive, saya ingin menyewa mobil lepas kunci.'
    ],
    'driver' => [
        'title' => 'Layanan Driver Profesional',
        'category' => 'Kenyamanan Maksimal',
        'subtitle' => 'Duduk santai dan nikmati perjalanan dengan supir berpengalaman.',
        'image' => 'assets/img/veloz.jpg',
        'desc_1' => 'Nikmati perjalanan tanpa lelah dan stres kemacetan jalan dengan Layanan Driver Profesional kami. Kami menghadirkan driver handal yang siap mengantarkan Anda sekeluarga dengan aman, nyaman, dan tepat waktu. Layanan ini sangat cocok untuk perjalanan liburan keluarga, perjalanan dinas kantor, maupun menghadiri acara formal di mana kenyamanan menjadi prioritas utama Anda.',
        'desc_2' => 'Driver kami bukan sekadar pengemudi biasa. Mereka telah melewati seleksi ketat, memiliki SIM yang valid, terlatih dalam etika pelayanan, serta menguasai rute jalan raya dan destinasi wisata lokal secara mendalam. Anda dapat menghemat energi untuk menikmati momen-momen berharga bersama keluarga di sepanjang jalan.',
        'features' => [
            ['icon' => 'fa-user-tie', 'title' => 'Driver Bersertifikat', 'desc' => 'Supir yang ramah, sopan, berpenampilan rapi, dan mengutamakan keselamatan berkendara.'],
            ['icon' => 'fa-map-location-dot', 'title' => 'Penguasaan Rute Lokal', 'desc' => 'Driver menguasai jalan alternatif terbaik untuk menghindari kemacetan parah.'],
            ['icon' => 'fa-heart', 'title' => 'Bebas Kelelahan', 'desc' => 'Duduk santai di kursi penumpang tanpa perlu memikirkan parkir atau navigasi.'],
            ['icon' => 'fa-clock-rotate-left', 'title' => 'Waktu Fleksibel', 'desc' => 'Layanan driver siap menyesuaikan dengan jadwal perjalanan harian Anda.'],
        ],
        'faqs' => [
            ['q' => 'Apakah tarif sewa sudah termasuk BBM, Tol, dan Parkir?', 'a' => 'Tarif dasar yang tertera adalah untuk jasa sewa mobil dan driver saja. Biaya bahan bakar (BBM), tol, parkir, dan uang makan driver dapat ditanggung oleh penyewa atau dipilih dalam paket All-In.'],
            ['q' => 'Apakah melayani sewa luar kota?', 'a' => 'Ya, kami melayani rute perjalanan luar kota. Untuk perjalanan luar kota yang mengharuskan driver menginap, penyewa wajib menyediakan atau menanggung biaya penginapan untuk driver.'],
            ['q' => 'Berapa jam batas pemakaian per hari?', 'a' => 'Pemakaian standar dalam kota adalah maksimal 12 jam per hari. Kelebihan waktu pemakaian (overtime) akan dikenakan biaya tambahan sesuai ketentuan tarif jasa driver.'],
        ],
        'cta_text' => 'Pesan Dengan Driver',
        'cta_link' => 'index.php',
        'wa_message' => 'Halo Family Drive, saya ingin memesan sewa mobil dengan supir.'
    ],
    'antar-jemput' => [
        'title' => 'Antar Jemput Bandara & Hotel',
        'category' => 'Tepat Waktu & Praktis',
        'subtitle' => 'Sambutan hangat dan transportasi langsung tanpa perlu menunggu lama.',
        'image' => 'assets/img/innova.jpg',
        'desc_1' => 'Awali dan akhiri perjalanan Anda di kota ini dengan kenyamanan tinggi melalui layanan Antar Jemput Bandara & Hotel (Airport Transfer). Lupakan antrean panjang taksi bandara atau kekhawatiran membawa barang bawaan banyak. Driver kami akan bersiap menyambut Anda tepat di pintu kedatangan bandara atau lobi hotel Anda dengan ramah.',
        'desc_2' => 'Kami memahami bahwa waktu Anda sangat berharga. Oleh karena itu, tim kami selalu memantau jadwal penerbangan Anda secara real-time untuk mengantisipasi jika terjadi perubahan jadwal (delay). Begitu Anda mendarat, mobil yang nyaman dan bersih sudah siap menunggu untuk mengantarkan Anda langsung ke tujuan.',
        'features' => [
            ['icon' => 'fa-plane-arrival', 'title' => 'Flight Tracking', 'desc' => 'Jadwal penjemputan disesuaikan secara otomatis jika terjadi keterlambatan penerbangan.'],
            ['icon' => 'fa-suitcase-rolling', 'title' => 'Penanganan Bagasi', 'desc' => 'Driver siap membantu mengangkat dan menata barang bawaan Anda ke dalam bagasi.'],
            ['icon' => 'fa-signature', 'title' => 'Penyambutan Nama', 'desc' => 'Driver akan memegang papan nama Anda di area kedatangan untuk memudahkan pencarian.'],
            ['icon' => 'fa-circle-dollar-to-slot', 'title' => 'Tarif Flat Transparan', 'desc' => 'Tarif antar-jemput bersifat flat dan transparan tanpa ada biaya tersembunyi.'],
        ],
        'faqs' => [
            ['q' => 'Bagaimana saya menemukan driver di bandara?', 'a' => 'Driver kami akan menunggu Anda di dekat gerbang kedatangan penumpang dengan memegang papan nama yang tertera nama Anda. Kami juga akan mengirimkan kontak nomor driver beberapa jam sebelum kedatangan.'],
            ['q' => 'Bagaimana jika pesawat saya mengalami delay yang cukup lama?', 'a' => 'Tidak perlu khawatir. Kami selalu memantau nomor penerbangan Anda. Driver akan menyesuaikan waktu penjemputan berdasarkan waktu mendarat aktual tanpa tambahan biaya.'],
            ['q' => 'Apakah saya bisa memilih tipe mobil untuk penjemputan?', 'a' => 'Tentu. Anda dapat memilih tipe mobil (seperti Avanza, Innova, atau Pajero Sport) sesuai dengan jumlah penumpang dan volume bagasi yang Anda bawa.'],
        ],
        'cta_text' => 'Pesan Antar Jemput',
        'cta_link' => 'index.php',
        'wa_message' => 'Halo Family Drive, saya ingin memesan layanan antar jemput bandara.'
    ],
    'korporat' => [
        'title' => 'Sewa Korporat & Acara Keluarga',
        'category' => 'Solusi Transportasi',
        'subtitle' => 'Paket sewa fleksibel untuk kebutuhan bisnis instansi dan acara spesial.',
        'image' => 'assets/img/xpander.jpg',
        'desc_1' => 'Family Drive menghadirkan solusi transportasi komprehensif untuk kebutuhan operasional perusahaan (corporate lease) maupun acara penting keluarga seperti pernikahan, wisuda, ziarah, dan mudik bersama. Kami menyediakan pilihan durasi sewa jangka pendek hingga kontrak bulanan atau tahunan dengan penawaran harga yang kompetitif dan efisien.',
        'desc_2' => 'Bagi sektor korporasi, sewa kendaraan bulanan/tahunan merupakan solusi cerdas untuk menghemat pengeluaran investasi aset perusahaan. Kami menangani seluruh aspek pemeliharaan berkala, perpanjangan pajak, asuransi kendaraan, hingga penyediaan unit cadangan (back-up car) jika terjadi kendala teknis, sehingga operasional bisnis Anda tetap berjalan lancar.',
        'features' => [
            ['icon' => 'fa-building-shield', 'title' => 'Kontrak Sewa Jangka Panjang', 'desc' => 'Tersedia paket sewa bulanan dan tahunan dengan harga khusus korporasi.'],
            ['icon' => 'fa-screwdriver-wrench', 'title' => 'Pemeliharaan Rutin Ditanggung', 'desc' => 'Perawatan rutin berkala dan servis mesin sepenuhnya menjadi tanggung jawab kami.'],
            ['icon' => 'fa-car-burst', 'title' => 'Mobil Pengganti (Back-up Car)', 'desc' => 'Kami menyediakan unit pengganti segera jika mobil operasional mengalami kendala.'],
            ['icon' => 'fa-award', 'title' => 'Armada VIP / Pernikahan', 'desc' => 'Tersedia armada premium kelas VIP untuk mobil pengantin atau tamu kehormatan.'],
        ],
        'faqs' => [
            ['q' => 'Bagaimana cara mengajukan sewa mobil bulanan untuk perusahaan?', 'a' => 'Anda dapat mengirimkan email permintaan penawaran atau menghubungi kontak WhatsApp kami. Tim marketing kami akan segera mengirimkan proposal kerjasama resmi dengan harga sewa khusus korporasi.'],
            ['q' => 'Apakah pemeliharaan rutin mobil ditanggung oleh penyewa?', 'a' => 'Tidak. Biaya pemeliharaan rutin seperti ganti oli, servis berkala, dan penggantian suku cadang akibat pemakaian normal sepenuhnya ditanggung oleh Family Drive Rental.'],
            ['q' => 'Apakah tersedia layanan beserta supir untuk sewa jangka panjang?', 'a' => 'Ya, kami menyediakan paket lengkap sewa mobil jangka panjang sekaligus supir bulanan yang profesional, terlatih, dan siap menunjang mobilitas direksi atau operasional staf Anda.'],
        ],
        'cta_text' => 'Ajukan Penawaran',
        'cta_link' => 'index.php',
        'wa_message' => 'Halo Family Drive, saya ingin bertanya tentang sewa korporat / acara keluarga.'
    ]
];

// Fallback to lepas-kunci if type is invalid
if (!array_key_exists($type, $services_data)) {
    $type = 'lepas-kunci';
}

$service = $services_data[$type];
$page_title = $service['title'];

require_once 'includes/header.php';
?>

<!-- Custom CSS for FAQ Accordion and visual tweaks -->
<style>
    .faq-item {
        border-bottom: 1px solid var(--color-hairline);
        padding: var(--spacing-xs) 0;
    }
    .faq-question {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-weight: 600;
        color: var(--color-ink);
        padding: var(--spacing-xxs) 0;
        user-select: none;
    }
    .faq-question:hover {
        color: var(--color-primary);
    }
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        color: var(--color-body);
        font-size: 13px;
        line-height: 1.6;
        padding-right: var(--spacing-md);
    }
    .faq-item.active .faq-answer {
        max-height: 350px;
        margin-top: var(--spacing-xxs);
        margin-bottom: var(--spacing-xs);
    }
    .faq-icon {
        transition: transform 0.3s ease;
        color: var(--color-muted);
    }
    .faq-item.active .faq-icon {
        transform: rotate(45deg);
        color: var(--color-primary);
    }
    .feature-icon-wrapper {
        width: 48px;
        height: 48px;
        background-color: rgba(218, 41, 28, 0.08); /* Rosso Corsa light bg */
        border: 1px solid rgba(218, 41, 28, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: var(--spacing-xs);
        color: var(--color-primary);
        font-size: 20px;
    }
    .cta-card {
        background-color: var(--color-canvas-elevated);
        border: 1px solid var(--color-hairline);
        padding: var(--spacing-sm);
        position: sticky;
        top: 100px;
    }
</style>

<!-- Banner/Hero Section -->
<section class="section-band" style="background-color: var(--color-canvas); border-bottom: 1px solid var(--color-hairline); padding: var(--spacing-xl) 0; position: relative; overflow: hidden;">
    <!-- Visual background accent -->
    <div style="position: absolute; right: -10%; top: -30%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(218, 41, 28, 0.05) 0%, rgba(0,0,0,0) 70%); z-index: 1; pointer-events: none;"></div>
    
    <div class="grid-container" style="position: relative; z-index: 2;">
        <!-- Back Navigation Link -->
        <a href="services.php" style="color: var(--color-muted); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 8px; margin-bottom: var(--spacing-md); transition: color 0.2s;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='var(--color-muted)'">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Layanan Kami
        </a>
        <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xxs);"><?= htmlspecialchars($service['category']) ?></p>
        <h1 class="display-xl" style="margin-bottom: var(--spacing-xxs); text-transform: uppercase;"><?= htmlspecialchars($service['title']) ?></h1>
        <p class="body-md" style="color: var(--color-muted-soft); max-width: 800px; font-size: 16px; line-height: 1.6;">
            <?= htmlspecialchars($service['subtitle']) ?>
        </p>
    </div>
</section>

<!-- Content Grid Section -->
<section class="section-band" style="background-color: var(--color-canvas); border-bottom: 1px solid var(--color-hairline); padding: var(--spacing-lg) 0;">
    <div class="grid-container">
        <div class="grid-2-col" style="grid-template-columns: 2fr 1fr; gap: var(--spacing-lg);">
            
            <!-- Left Info Block -->
            <div>
                <!-- Featured Image -->
                <div style="border: 1px solid var(--color-hairline); background-color: #0c0c0c; overflow: hidden; line-height: 0; margin-bottom: var(--spacing-md);">
                    <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['title']) ?>" style="width: 100%; height: auto; max-height: 450px; object-fit: cover;">
                </div>
                
                <!-- Editorial Descriptions -->
                <div style="margin-bottom: var(--spacing-lg);">
                    <h2 class="display-md" style="margin-bottom: var(--spacing-xs); color: var(--color-ink);">Tentang Layanan</h2>
                    <p class="body-md" style="color: var(--color-body); line-height: 1.8; font-size: 15px; margin-bottom: var(--spacing-xs);">
                        <?= htmlspecialchars($service['desc_1']) ?>
                    </p>
                    <p class="body-md" style="color: var(--color-body); line-height: 1.8; font-size: 15px;">
                        <?= htmlspecialchars($service['desc_2']) ?>
                    </p>
                </div>

                <!-- Core Features/Includes Grid -->
                <div style="margin-bottom: var(--spacing-lg);">
                    <h2 class="display-md" style="margin-bottom: var(--spacing-sm); color: var(--color-ink);">Fasilitas & Keunggulan</h2>
                    <div class="grid-2-col" style="gap: var(--spacing-md);">
                        <?php foreach ($service['features'] as $feat): ?>
                            <div style="border-top: 1px solid var(--color-hairline); padding-top: var(--spacing-xs);">
                                <div class="feature-icon-wrapper">
                                    <i class="fa-solid <?= htmlspecialchars($feat['icon']) ?>"></i>
                                </div>
                                <h3 class="title-sm" style="color: var(--color-ink); margin-bottom: 6px;"><?= htmlspecialchars($feat['title']) ?></h3>
                                <p class="body-md" style="color: var(--color-body); font-size: 13px; line-height: 1.6;">
                                    <?= htmlspecialchars($feat['desc']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div>
                    <h2 class="display-md" style="margin-bottom: var(--spacing-sm); color: var(--color-ink);">Pertanyaan Umum (FAQ)</h2>
                    <div style="border-top: 1px solid var(--color-hairline);">
                        <?php foreach ($service['faqs'] as $faq): ?>
                            <div class="faq-item">
                                <div class="faq-question">
                                    <span><?= htmlspecialchars($faq['q']) ?></span>
                                    <i class="fa-solid fa-plus faq-icon"></i>
                                </div>
                                <div class="faq-answer">
                                    <p><?= htmlspecialchars($faq['a']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
            
            <!-- Right CTA Block -->
            <div>
                <div class="cta-card">
                    <h3 class="title-md" style="color: var(--color-ink); margin-bottom: var(--spacing-xxs); letter-spacing: 0.5px; text-transform: uppercase;">FAMILY DRIVE</h3>
                    <p class="caption-uppercase" style="color: var(--color-primary); margin-bottom: var(--spacing-xs);">SOLUSI PENYEWAAN TERPERCAYA</p>
                    
                    <p class="body-md" style="color: var(--color-body); font-size: 13px; line-height: 1.6; margin-bottom: var(--spacing-sm);">
                        Pesan layanan ini secara cepat, aman, dan mudah. Hubungi tim kami sekarang juga untuk penawaran terbaik atau langsung sewa melalui katalog kendaraan yang tersedia.
                    </p>
                    
                    <div style="display: flex; flex-direction: column; gap: var(--spacing-xs);">
                        <!-- Direct Catalog CTA -->
                        <?php if ($type === 'lepas-kunci'): ?>
                        <a href="<?= $base_url ?><?= htmlspecialchars($service['cta_link']) ?>" class="btn-primary-ferrari" style="width: 100%; text-align: center; gap: 8px;">
                            <i class="fa-solid fa-car"></i> <?= htmlspecialchars($service['cta_text']) ?>
                        </a>
                        <?php endif; ?>
                        
                        <!-- WhatsApp CTA -->
                        <?php 
                            $wa_number = '6287863664414';
                            $wa_url = 'https://wa.me/' . $wa_number . '?text=' . rawurlencode($service['wa_message']);
                        ?>
                        <a href="<?= $wa_url ?>" target="_blank" class="btn-outline-dark-ferrari" style="width: 100%; text-align: center; gap: 8px; border-color: #25d366; color: #25d366;" onmouseover="this.style.backgroundColor='#25d366'; this.style.color='var(--color-canvas)';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#25d366';">
                            <i class="fa-brands fa-whatsapp"></i> Hubungi WhatsApp
                        </a>
                    </div>

                    <div style="margin-top: var(--spacing-sm); padding-top: var(--spacing-xs); border-top: 1px solid var(--color-hairline); display: flex; flex-direction: column; gap: 6px;">
                        <span style="font-size: 11px; text-transform: uppercase; color: var(--color-muted);"><i class="fa-solid fa-phone" style="color: var(--color-primary); margin-right: 6px;"></i> +62 87 863 664 414</span>
                        <span style="font-size: 11px; text-transform: uppercase; color: var(--color-muted);"><i class="fa-solid fa-envelope" style="color: var(--color-primary); margin-right: 6px;"></i> info@familydriverental.com</span>
                        <span style="font-size: 11px; text-transform: uppercase; color: var(--color-muted);"><i class="fa-solid fa-clock" style="color: var(--color-primary); margin-right: 6px;"></i> Layanan CS 24 Jam</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>


<!-- Interactive Accordion Script -->
<script>
    document.querySelectorAll('.faq-question').forEach(button => {
        button.addEventListener('click', () => {
            const faqItem = button.parentElement;
            
            // Toggle active class on this item
            faqItem.classList.toggle('active');
            
            // Close other FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                }
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
