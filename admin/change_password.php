<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

$conn = db_connect();

$error = '';
$success = '';

function fetch_admin_user_by_username($conn, $username)
{
    $stmt = $conn->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $username = $_SESSION['admin_username'] ?? '';
        $user = $username !== '' ? fetch_admin_user_by_username($conn, $username) : null;
        $valid = false;
        if ($user) {
            if (password_verify($current_password, $user['password_hash'])) {
                $valid = true;
            }
        } else {
            if ($username === $admin_username && hash_equals($admin_password, $current_password)) {
                $valid = true;
            }
        }
        if (!$valid) {
            $error = 'Password lama tidak sesuai.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?) ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)');
            if ($stmt) {
                $stmt->bind_param('ss', $username, $hash);
                if ($stmt->execute()) {
                    $success = 'Password berhasil diganti.';
                } else {
                    $error = 'Gagal menyimpan password baru.';
                }
                $stmt->close();
            } else {
                $error = 'Terjadi kesalahan sistem.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Ganti Password Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Admin Buku Tamu</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsExample">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="kegiatan.php">Jenis Kegiatan</a></li>
                <li class="nav-item"><a class="nav-link" href="instansi.php">Instansi</a></li>
                <li class="nav-item"><a class="nav-link" href="tamu.php">Data Tamu</a></li>
                <li class="nav-item"><a class="nav-link active" href="change_password.php">Ganti Password</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Keluar</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <h1 class="h5 mb-3">Ganti Password Admin</h1>
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if ($error !== '') { ?>
                        <div class="alert alert-danger py-2 small mb-3">
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php } ?>
                    <?php if ($success !== '') { ?>
                        <div class="alert alert-success py-2 small mb-3">
                            <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php } ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Password Lama</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Simpan Password</button>
                        </div>
                    </form>
                </div>
            </div>
            <p class="small text-muted mt-3 mb-0">
                Setelah password diganti, login admin akan menggunakan password baru ini.
            </p>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

