<?php
// login.php
$page_title = "Masuk Akun";
require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Email dan Password wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                    exit;
                } else {
                    header("Location: index.php");
                    exit;
                }
            } else {
                $error_message = "Email atau Password tidak cocok.";
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<section class="section-band" style="background-color: var(--color-canvas); min-height: 80vh; display: flex; align-items: center;">
    <div class="grid-container" style="width: 100%; max-width: 450px;">
        <div style="background-color: var(--color-canvas-elevated); padding: var(--spacing-md); border: 1px solid var(--color-hairline); border-radius: var(--rounded-none);">
            
            <h2 class="display-md" style="margin-bottom: var(--spacing-xs); text-align: center; text-transform: uppercase; letter-spacing: 1px;">Masuk Akun</h2>
            <p style="color: var(--color-muted); font-size: 13px; text-align: center; margin-bottom: var(--spacing-md);">Akses portal pemesanan supercar Scuderia Rental</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert-ferrari error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div style="margin-bottom: var(--spacing-xs);">
                    <label class="form-label-dark" for="email">Alamat Email</label>
                    <input type="email" name="email" id="email" class="form-input-dark" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                </div>

                <div style="margin-bottom: var(--spacing-sm);">
                    <label class="form-label-dark" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-input-dark" required>
                </div>

                <button type="submit" class="btn-primary-ferrari" style="width: 100%;">Masuk</button>
            </form>

            <p style="margin-top: var(--spacing-md); text-align: center; font-size: 13px;">
                Belum memiliki akun? <a href="register.php" style="color: var(--color-primary); font-weight: 600;">Daftar di sini</a>
            </p>
            
            <div style="margin-top: var(--spacing-md); padding-top: var(--spacing-xs); border-top: 1px dashed var(--color-hairline); font-size: 12px; color: var(--color-muted);">
                <p style="font-weight: 600; margin-bottom: 2px;">Akun Demo Sistem:</p>
                <p>Admin: <code style="color: var(--color-ink);">admin@ferrarirental.com</code> / Password: <code style="color: var(--color-ink);">admin123</code></p>
                <p>Customer: <code style="color: var(--color-ink);">user@ferrarirental.com</code> / Password: <code style="color: var(--color-ink);">user123</code></p>
            </div>
            
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
