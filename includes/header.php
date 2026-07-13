<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$base_url = "/penyewaanmobil/";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . " | Family Drive" : "Family Drive - Sewa Mobil Keluarga & Standar" ?></title>
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
    <!-- FontAwesome for some icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="top-nav">
    <a href="<?= $base_url ?>index.php" class="brand-mark">
        <i class="fa-solid fa-car-side"></i> FAMILY <span class="accent">DRIVE</span>
    </a>
    
    <nav style="display: flex; gap: var(--spacing-md); align-items: center;">
        <a href="<?= $base_url ?>index.php" class="nav-link-item">Katalog</a>
        <a href="<?= $base_url ?>services.php" class="nav-link-item">Layanan</a>
        
        <?php if (isset($_SESSION['role'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="<?= $base_url ?>admin/dashboard.php" class="nav-link-item"><i class="fa-solid fa-gauge"></i> Dashboard Admin</a>
                <a href="<?= $base_url ?>admin/manage_cars.php" class="nav-link-item">Kelola Mobil</a>
                <a href="<?= $base_url ?>admin/manage_rentals.php" class="nav-link-item">Penyewaan</a>
            <?php else: ?>
                <a href="<?= $base_url ?>history.php" class="nav-link-item">Riwayat Sewa</a>
            <?php endif; ?>
            
            <span style="color: var(--color-muted); font-size: 13px;">|</span>
            <span class="nav-link-item" style="color: var(--color-primary); text-transform: none; cursor: default;">
                <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION['nama']) ?>
            </span>
            <a href="<?= $base_url ?>logout.php" class="nav-link-item" style="color: var(--color-muted);"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
        <?php else: ?>
            <a href="<?= $base_url ?>login.php" class="nav-link-item">Masuk</a>
            <a href="<?= $base_url ?>register.php" class="btn-primary-ferrari" style="height: 36px; padding: 0 var(--spacing-xs); font-size: 12px;">Daftar</a>
        <?php endif; ?>
    </nav>
</header>

<main>
