<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../db.php';

$conn = db_connect();

$nama = '';
$edit_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($nama !== '') {
        if ($id > 0) {
            $stmt = $conn->prepare('UPDATE instansi SET nama = ? WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('si', $nama, $id);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare('INSERT INTO instansi (nama) VALUES (?)');
            if ($stmt) {
                $stmt->bind_param('s', $nama);
                $stmt->execute();
                $stmt->close();
            }
        }
        header('Location: instansi.php');
        exit;
    }
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id > 0) {
        $stmt = $conn->prepare('DELETE FROM instansi WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }
    header('Location: instansi.php');
    exit;
}

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    if ($edit_id > 0) {
        $stmt = $conn->prepare('SELECT id, nama FROM instansi WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $edit_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $nama = $row['nama'];
            }
            $stmt->close();
        }
    }
}

$list = [];
$res = $conn->query('SELECT id, nama FROM instansi ORDER BY nama ASC');
$usage = [];
$res_usage = $conn->query('SELECT instansi_id, COUNT(*) AS c FROM tamu WHERE instansi_id IS NOT NULL GROUP BY instansi_id');
if ($res_usage) {
    while ($row = $res_usage->fetch_assoc()) {
        $usage[(int)$row['instansi_id']] = (int)$row['c'];
    }
}
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $row['dipakai'] = $usage[(int)$row['id']] ?? 0;
        $list[] = $row;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Instansi - Admin Buku Tamu</title>
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
                <li class="nav-item"><a class="nav-link active" href="instansi.php">Instansi</a></li>
                <li class="nav-item"><a class="nav-link" href="tamu.php">Data Tamu</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h1 class="h5 mb-3"><?php echo $edit_id ? 'Ubah Instansi' : 'Tambah Instansi'; ?></h1>
                    <form method="post">
                        <input type="hidden" name="id" value="<?php echo $edit_id ? (int)$edit_id : 0; ?>">
                        <div class="mb-3">
                            <label class="form-label">Nama Instansi</label>
                            <input type="text" name="nama" class="form-control" required value="<?php echo htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary"><?php echo $edit_id ? 'Simpan Perubahan' : 'Tambah'; ?></button>
                        <?php if ($edit_id) { ?>
                            <a href="instansi.php" class="btn btn-link text-decoration-none">Batal</a>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Daftar Instansi</h2>
                    <table class="table table-sm align-middle">
                        <thead>
                        <tr>
                            <th style="width:60px;">No</th>
                            <th>Nama</th>
                            <th style="width:110px;">Dipakai</th>
                            <th style="width:130px;">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; foreach ($list as $row) { ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int)$row['dipakai']; ?></td>
                                <td class="text-nowrap">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="instansi.php?edit=<?php echo (int)$row['id']; ?>" class="btn btn-outline-primary">Ubah</a>
                                        <a href="instansi.php?hapus=<?php echo (int)$row['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Hapus data ini?');">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if (!$list) { ?>
                            <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
