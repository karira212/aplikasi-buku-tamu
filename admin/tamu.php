<?php
session_start();
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../db.php';

$conn = db_connect();

$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_selesai = $_GET['tanggal_selesai'] ?? '';
$filter_kegiatan_id = isset($_GET['kegiatan_id']) ? (int)$_GET['kegiatan_id'] : 0;

if ($tanggal_mulai !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_mulai)) {
    $tanggal_mulai = '';
}
if ($tanggal_selesai !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_selesai)) {
    $tanggal_selesai = '';
}

$kegiatan_list = [];
$res_k = $conn->query('SELECT id, nama FROM kegiatan ORDER BY nama ASC');
if ($res_k) {
    while ($row = $res_k->fetch_assoc()) {
        $kegiatan_list[] = $row;
    }
}

$conditions = [];
if ($tanggal_mulai !== '') {
    $safe = $conn->real_escape_string($tanggal_mulai);
    $conditions[] = "DATE(t.created_at) >= '" . $safe . "'";
}
if ($tanggal_selesai !== '') {
    $safe = $conn->real_escape_string($tanggal_selesai);
    $conditions[] = "DATE(t.created_at) <= '" . $safe . "'";
}
if ($filter_kegiatan_id > 0) {
    $conditions[] = 't.kegiatan_id = ' . (int)$filter_kegiatan_id;
}

$where_sql = '';
if ($conditions) {
    $where_sql = ' WHERE ' . implode(' AND ', $conditions);
}

$base_sql = 'SELECT t.id, t.nama_lengkap, t.no_hp, t.jabatan, t.alamat, t.created_at, t.foto_path,
        CASE WHEN t.kegiatan_id IS NULL OR t.kegiatan_id = 0 THEN t.kegiatan_lainnya ELSE k.nama END AS nama_kegiatan,
        CASE WHEN t.instansi_id IS NULL OR t.instansi_id = 0 THEN t.instansi_lainnya ELSE i.nama END AS nama_instansi
        FROM tamu t
        LEFT JOIN kegiatan k ON t.kegiatan_id = k.id
        LEFT JOIN instansi i ON t.instansi_id = i.id';

if (isset($_GET['print']) && $_GET['print'] === '1') {
    $sql_print = $base_sql . $where_sql . ' ORDER BY t.created_at ASC';
    $res_print = $conn->query($sql_print);
    $rows = [];
    if ($res_print) {
        while ($row = $res_print->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    $judul_kegiatan = 'Semua kegiatan';
    if ($filter_kegiatan_id > 0) {
        foreach ($kegiatan_list as $k) {
            if ((int)$k['id'] === $filter_kegiatan_id) {
                $judul_kegiatan = $k['nama'];
                break;
            }
        }
    }
    ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Rekap Data Tamu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    @media print {
        .no-print { display: none !important; }
    }
    </style>
</head>
<body class="bg-white">
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary btn-sm">Print</button>
        <a href="tamu.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
    <h1 class="h5 text-center mb-1">Rekap Buku Tamu</h1>
    <p class="text-center mb-3">TP PKK Kecamatan Koja</p>
    <div class="mb-3">
        <div><strong>Periode:</strong>
            <?php
            if ($tanggal_mulai === '' && $tanggal_selesai === '') {
                echo 'Semua tanggal';
            } else {
                $label_mulai = $tanggal_mulai !== '' ? $tanggal_mulai : '-';
                $label_selesai = $tanggal_selesai !== '' ? $tanggal_selesai : '-';
                echo htmlspecialchars($label_mulai . ' s/d ' . $label_selesai, ENT_QUOTES, 'UTF-8');
            }
            ?>
        </div>
        <div><strong>Jenis kegiatan:</strong> <?php echo htmlspecialchars($judul_kegiatan, ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Jumlah tamu:</strong> <?php echo count($rows); ?></div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle">
            <thead>
            <tr>
                <th style="width:40px;">No</th>
                <th>Nama</th>
                <th>No. HP</th>
                <th>Jabatan</th>
                <th>Instansi</th>
                <th>Jenis Kegiatan</th>
                <th>Alamat</th>
                <th>Waktu</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($rows) { ?>
                <?php $no = 1; foreach ($rows as $row) { ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['no_hp'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['jabatan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_instansi'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_kegiatan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['alamat'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="8" class="text-center text-muted">Belum ada data tamu.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
    exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $sql_export = $base_sql . $where_sql . ' ORDER BY t.created_at ASC, t.id ASC';
    $res_export = $conn->query($sql_export);
    header('Content-Type: text/csv; charset=utf-8');
    $filename = 'data_tamu_' . date('Ymd_His') . '.csv';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['No', 'Nama', 'No HP', 'Jabatan', 'Alamat', 'Jenis Kegiatan', 'Instansi', 'Waktu', 'Foto']);
    if ($res_export) {
        $no = 1;
        while ($row = $res_export->fetch_assoc()) {
            fputcsv($out, [
                $no++,
                $row['nama_lengkap'],
                $row['no_hp'],
                $row['jabatan'],
                $row['alamat'],
                $row['nama_kegiatan'],
                $row['nama_instansi'],
                $row['created_at'],
                $row['foto_path'],
            ]);
        }
    }
    fclose($out);
    exit;
}

$sql = $base_sql . $where_sql . ' ORDER BY t.created_at ASC, t.id ASC';
$list = [];
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $list[] = $row;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Data Tamu - Admin Buku Tamu</title>
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
                <li class="nav-item"><a class="nav-link active" href="tamu.php">Data Tamu</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Keluar</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
    <h1 class="h5 mb-3">Data Tamu</h1>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form class="row gy-2 gx-2 align-items-end" method="get">
                <div class="col-sm-4 col-md-3">
                    <label class="form-label mb-1">Tanggal mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="<?php echo htmlspecialchars($tanggal_mulai, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label mb-1">Tanggal selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="<?php echo htmlspecialchars($tanggal_selesai, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label mb-1">Jenis kegiatan</label>
                    <select name="kegiatan_id" class="form-select form-select-sm">
                        <option value="0">Semua</option>
                        <?php foreach ($kegiatan_list as $k) { ?>
                            <option value="<?php echo (int)$k['id']; ?>" <?php echo $filter_kegiatan_id === (int)$k['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-sm-4 col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm me-2">Terapkan Filter</button>
                    <a href="tamu.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
                <div class="col-12 mt-2">
                    <a href="tamu.php?<?php
                        $params = $_GET;
                        $params['export'] = 'csv';
                        echo htmlspecialchars(http_build_query($params), ENT_QUOTES, 'UTF-8');
                    ?>" class="btn btn-success btn-sm me-2">Export CSV</a>
                    <a href="tamu.php?<?php
                        $params = $_GET;
                        $params['print'] = '1';
                        echo htmlspecialchars(http_build_query($params), ENT_QUOTES, 'UTF-8');
                    ?>" target="_blank" class="btn btn-outline-primary btn-sm">Tampilan Cetak</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>No. HP</th>
                        <th>Jabatan</th>
                        <th>Jenis Kegiatan</th>
                        <th>Instansi</th>
                        <th>Alamat</th>
                        <th>Waktu</th>
                        <th>Foto</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $no = 1; foreach ($list as $row) { ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['no_hp'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['jabatan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_kegiatan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_instansi'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['alamat'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php if ($row['foto_path']) { ?>
                                    <a href="../<?php echo htmlspecialchars($row['foto_path'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                <?php } else { ?>
                                    <span class="text-muted">-</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (!$list) { ?>
                        <tr><td colspan="9" class="text-center text-muted">Belum ada data tamu.</td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
