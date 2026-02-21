<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../db.php';

$conn = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_guestbook'])) {
    $status = $_POST['toggle_guestbook'] === 'open' ? '1' : '0';
    $name = 'guestbook_open';
    $stmt = $conn->prepare('INSERT INTO app_settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
    if ($stmt) {
        $stmt->bind_param('ss', $name, $status);
        $stmt->execute();
        $stmt->close();
    }
}

$total_kegiatan = 0;
$total_tamu = 0;
$total_instansi = 0;
$guestbook_open = true;

$res = $conn->query('SELECT COUNT(*) AS c FROM kegiatan');
if ($res && $row = $res->fetch_assoc()) {
    $total_kegiatan = (int)$row['c'];
}
$res = $conn->query('SELECT COUNT(*) AS c FROM tamu');
if ($res && $row = $res->fetch_assoc()) {
    $total_tamu = (int)$row['c'];
}
$res = $conn->query('SELECT COUNT(*) AS c FROM instansi');
if ($res && $row = $res->fetch_assoc()) {
    $total_instansi = (int)$row['c'];
}
$res_setting = $conn->query("SELECT value FROM app_settings WHERE name = 'guestbook_open' LIMIT 1");
if ($res_setting && $row_setting = $res_setting->fetch_assoc()) {
    $guestbook_open = $row_setting['value'] === '1';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Dashboard Admin Buku Tamu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .admin-card {
        border: 0;
        border-radius: 0.75rem;
        box-shadow: 0 0.15rem 0.6rem rgba(15, 23, 42, 0.18);
        overflow: hidden;
    }
    .admin-card-body {
        padding: 1.1rem 1.25rem;
    }
    .admin-card-title {
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .35rem;
    }
    .admin-card-value {
        font-size: 1.9rem;
        font-weight: 600;
        margin-bottom: 0;
    }
    .admin-card-icon {
        font-size: 2.2rem;
        opacity: .35;
    }
    </style>
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
                <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="kegiatan.php">Jenis Kegiatan</a></li>
                <li class="nav-item"><a class="nav-link" href="instansi.php">Instansi</a></li>
                <li class="nav-item"><a class="nav-link" href="tamu.php">Data Tamu</a></li>
                <li class="nav-item"><a class="nav-link" href="change_password.php">Ganti Password</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Keluar</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <h1 class="h4 mb-4">Dashboard</h1>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="admin-card bg-primary text-white">
                <div class="admin-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="admin-card-title text-white-50">Jumlah Kegiatan</div>
                            <p class="admin-card-value mb-0"><?php echo $total_kegiatan; ?></p>
                        </div>
                        <div class="admin-card-icon">
                            •
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card bg-success text-white">
                <div class="admin-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="admin-card-title text-white-50">Jumlah Tamu</div>
                            <p class="admin-card-value mb-0"><?php echo $total_tamu; ?></p>
                        </div>
                        <div class="admin-card-icon">
                            •
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card bg-warning text-dark mb-3">
                <div class="admin-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="admin-card-title text-dark-50">Jumlah Instansi</div>
                            <p class="admin-card-value mb-2"><?php echo $total_instansi; ?></p>
                            <div class="small mb-1">Status Buku Tamu</div>
                            <div class="fw-semibold <?php echo $guestbook_open ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $guestbook_open ? 'Dibuka' : 'Ditutup'; ?>
                            </div>
                        </div>
                        <form method="post" class="ms-3 text-end">
                            <?php if ($guestbook_open) { ?>
                                <input type="hidden" name="toggle_guestbook" value="close">
                                <button type="submit" class="btn btn-sm btn-outline-light text-danger bg-white border-0">Tutup</button>
                            <?php } else { ?>
                                <input type="hidden" name="toggle_guestbook" value="open">
                                <button type="submit" class="btn btn-sm btn-outline-light text-success bg-white border-0">Buka</button>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
