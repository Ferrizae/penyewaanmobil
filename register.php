<?php
// register.php
$page_title = "Registrasi Akun";
require_once 'config/db.php';
require_once 'includes/header.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $nomor_sim = trim($_POST['nomor_sim']);

    if (empty($nama) || empty($email) || empty($password) || empty($no_hp) || empty($alamat) || empty($nomor_sim)) {
        $error_message = "Semua kolom input wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error_message = "Email sudah terdaftar. Silakan gunakan email lain atau masuk.";
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, no_hp, alamat, nomor_sim, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
                $stmt->execute([$nama, $email, $hashed_password, $no_hp, $alamat, $nomor_sim]);
                
                $success_message = "Registrasi berhasil! Silakan masuk menggunakan akun baru Anda.";
                // Clear inputs
                $nama = $email = $no_hp = $alamat = $nomor_sim = '';
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>

<section class="section-band" style="background-color: var(--color-canvas); min-height: 80vh; display: flex; align-items: center;">
    <div class="grid-container" style="width: 100%; max-width: 500px;">
        <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline); border-radius: var(--rounded-none);">
            
            <h2 class="display-md" style="margin-bottom: var(--spacing-xs); text-align: center; text-transform: uppercase; letter-spacing: 1px;">Daftar Akun Baru</h2>
            <p style="color: var(--color-muted); font-size: 13px; text-align: center; margin-bottom: var(--spacing-md);">Mulai pengalaman berkendara mewah Anda bersama Scuderia Rental</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert-ferrari error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert-ferrari success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= htmlspecialchars($success_message) ?></span>
                </div>
                <div style="text-align: center; margin-top: var(--spacing-sm);">
                    <a href="login.php" class="btn-primary-ferrari" style="width: 100%;">Masuk Sekarang</a>
                </div>
            <?php else: ?>
                <form action="register.php" method="POST">
                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="nama">Nama Lengkap</label>
                        <input type="text" name="nama" id="nama" class="form-input-dark" value="<?= isset($nama) ? htmlspecialchars($nama) : '' ?>" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="email">Alamat Email</label>
                        <input type="email" name="email" id="email" class="form-input-dark" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-input-dark" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="no_hp">Nomor Handphone</label>
                        <input type="text" name="no_hp" id="no_hp" class="form-input-dark" value="<?= isset($no_hp) ? htmlspecialchars($no_hp) : '' ?>" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-xs);">
                        <label class="form-label-dark" for="nomor_sim">Nomor Surat Izin Mengemudi (SIM A)</label>
                        <input type="text" name="nomor_sim" id="nomor_sim" class="form-input-dark" value="<?= isset($nomor_sim) ? htmlspecialchars($nomor_sim) : '' ?>" required>
                    </div>

                    <div style="margin-bottom: var(--spacing-sm);">
                        <label class="form-label-dark" for="alamat">Alamat Lengkap</label>
                        <textarea name="alamat" id="alamat" class="form-input-dark" style="height: 100px; resize: none; padding-top: var(--spacing-xxs);" required><?= isset($alamat) ? htmlspecialchars($alamat) : '' ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary-ferrari" style="width: 100%;">Registrasi</button>
                </form>

                <p style="margin-top: var(--spacing-md); text-align: center; font-size: 13px;">
                    Sudah memiliki akun? <a href="login.php" style="color: var(--color-primary); font-weight: 600;">Masuk di sini</a>
                </p>
            <?php endif; ?>
            
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
