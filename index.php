<?php
require_once __DIR__ . '/db.php';

$conn = db_connect();
$guestbook_open = true;
$setting_result = $conn->query("SELECT value FROM app_settings WHERE name = 'guestbook_open' LIMIT 1");
if ($setting_result && $row_setting = $setting_result->fetch_assoc()) {
    $guestbook_open = $row_setting['value'] === '1';
}

$kegiatan = [];
$instansi = [];

$kegiatan_result = $conn->query('SELECT id, nama FROM kegiatan ORDER BY nama ASC');
if ($kegiatan_result) {
    while ($row = $kegiatan_result->fetch_assoc()) {
        $kegiatan[] = $row;
    }
}

$instansi_result = $conn->query('SELECT id, nama FROM instansi ORDER BY nama ASC');
if ($instansi_result) {
    while ($row = $instansi_result->fetch_assoc()) {
        $instansi[] = $row;
    }
}

$errors = [];
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $guestbook_open) {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $kegiatan_id_raw = $_POST['kegiatan_id'] ?? '';
    $kegiatan_lainnya = trim($_POST['kegiatan_lainnya'] ?? '');
    $instansi_id_raw = $_POST['instansi_id'] ?? '';
    $instansi_lainnya = trim($_POST['instansi_lainnya'] ?? '');

    if ($nama_lengkap === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if ($no_hp === '') {
        $errors[] = 'No. handphone wajib diisi.';
    }

    $kegiatan_id = 0;
    $use_kegiatan_lainnya = false;
    if ($kegiatan_id_raw === '') {
        $errors[] = 'Jenis kegiatan wajib dipilih.';
    } elseif ($kegiatan_id_raw === 'lainnya') {
        $use_kegiatan_lainnya = true;
        if ($kegiatan_lainnya === '') {
            $errors[] = 'Isi jenis kegiatan pada kolom lainnya.';
        }
    } else {
        $kegiatan_id = (int)$kegiatan_id_raw;
        if ($kegiatan_id <= 0) {
            $errors[] = 'Jenis kegiatan tidak valid.';
        }
    }

    $instansi_id = null;
    $use_instansi_lainnya = false;
    if ($instansi_id_raw === '') {
        $errors[] = 'Asal tamu wajib dipilih.';
    } elseif ($instansi_id_raw === 'lainnya') {
        $use_instansi_lainnya = true;
        if ($instansi_lainnya === '') {
            $errors[] = 'Isi asal instansi pada kolom lainnya.';
        }
    } else {
        $instansi_id = (int)$instansi_id_raw;
        if ($instansi_id <= 0) {
            $errors[] = 'Asal instansi tidak valid.';
        }
    }

    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Foto wajib diunggah.';
    }

    $foto_path = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['foto']['tmp_name'];
        $mime = mime_content_type($tmp_name);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            $errors[] = 'Format foto harus JPG, PNG, atau WEBP.';
        } else {
            $ext = '.jpg';
            if ($mime === 'image/png') {
                $ext = '.png';
            } elseif ($mime === 'image/webp') {
                $ext = '.webp';
            }
            $upload_dir = __DIR__ . '/uploads';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . $ext;
            $destination = $upload_dir . '/' . $filename;
            if (move_uploaded_file($tmp_name, $destination)) {
                $foto_path = 'uploads/' . $filename;
            } else {
                $errors[] = 'Gagal menyimpan file foto.';
            }
        }
    }

    if (!$errors && $foto_path !== null) {
        $sql = 'INSERT INTO tamu (nama_lengkap, no_hp, jabatan, alamat, kegiatan_id, kegiatan_lainnya, instansi_id, instansi_lainnya, foto_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $instansi_id_param = $use_instansi_lainnya ? 0 : $instansi_id;
            $instansi_lainnya_param = $use_instansi_lainnya ? $instansi_lainnya : null;
            $kegiatan_id_param = $use_kegiatan_lainnya ? 0 : $kegiatan_id;
            $kegiatan_lainnya_param = $use_kegiatan_lainnya ? $kegiatan_lainnya : null;
            $kegiatan_id_param_str = (string)$kegiatan_id_param;
            $instansi_id_param_str = (string)$instansi_id_param;
            $stmt->bind_param(
                'sssssssss',
                $nama_lengkap,
                $no_hp,
                $jabatan,
                $alamat,
                $kegiatan_id_param_str,
                $kegiatan_lainnya_param,
                $instansi_id_param_str,
                $instansi_lainnya_param,
                $foto_path
            );
            if ($stmt->execute()) {
                $submitted = true;
            } else {
                $errors[] = 'Gagal menyimpan data tamu.';
            }
            $stmt->close();
        } else {
            $errors[] = 'Terjadi kesalahan sistem.';
        }
    }
}

if ($submitted) {
    $waktu = date('d-m-Y H:i');
    ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Buku Tamu TP PKK Kecamatan Koja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <h1 class="h4 mb-3">Terima Kasih</h1>
                    <p class="mb-1">Data kehadiran Anda sudah tercatat.</p>
                    <p class="text-muted mb-3">TP PKK Kecamatan Koja</p>
                    <p class="small text-secondary mb-0">Waktu submit: <?php echo htmlspecialchars($waktu, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
            <div class="text-end mt-2">
                <a href="admin/login.php" class="link-secondary link-offset-2 text-decoration-none small">Admin</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
    exit;
}

$now_display = date('d-m-Y H:i');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Buku Tamu TP PKK Kecamatan Koja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="h4 text-center mb-1">Buku Tamu</h1>
                    <p class="text-center text-muted mb-4">Hall TP PKK Kecamatan Koja</p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small text-muted">Tanggal & Jam</span>
                        <span class="badge bg-primary-subtle text-primary" id="waktu-sekarang"><?php echo htmlspecialchars($now_display, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <?php if (!$guestbook_open) { ?>
                        <div class="alert alert-warning py-2 small mb-3">
                            Buku tamu sedang ditutup. Silakan hubungi petugas.
                        </div>
                    <?php } ?>
                    <?php if ($errors) { ?>
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e) { ?>
                                    <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required <?php echo $guestbook_open ? '' : 'disabled'; ?> value="<?php echo isset($nama_lengkap) ? htmlspecialchars($nama_lengkap, ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. Handphone</label>
                            <input type="tel" name="no_hp" class="form-control" required <?php echo $guestbook_open ? '' : 'disabled'; ?> value="<?php echo isset($no_hp) ? htmlspecialchars($no_hp, ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" <?php echo $guestbook_open ? '' : 'disabled'; ?> value="<?php echo isset($jabatan) ? htmlspecialchars($jabatan, ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" <?php echo $guestbook_open ? '' : 'disabled'; ?>><?php echo isset($alamat) ? htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kegiatan</label>
                            <select name="kegiatan_id" id="kegiatan-select" class="form-select" required <?php echo $guestbook_open ? '' : 'disabled'; ?>>
                                <option value="">Pilih jenis kegiatan</option>
                                <?php foreach ($kegiatan as $k) { ?>
                                    <option value="<?php echo (int)$k['id']; ?>" <?php echo isset($kegiatan_id_raw) && (string)$kegiatan_id_raw === (string)$k['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($k['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                                <option value="lainnya" <?php echo isset($kegiatan_id_raw) && $kegiatan_id_raw === 'lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                            </select>
                            <?php if (!$kegiatan) { ?>
                                <div class="form-text text-danger">Belum ada jenis kegiatan. Hubungi admin.</div>
                            <?php } ?>
                        </div>
                        <div class="mb-3" id="kegiatan-lainnya-group" style="display:none;">
                            <label class="form-label">Jenis Kegiatan Lainnya</label>
                            <input type="text" name="kegiatan_lainnya" class="form-control" <?php echo $guestbook_open ? '' : 'disabled'; ?> value="<?php echo isset($kegiatan_lainnya) ? htmlspecialchars($kegiatan_lainnya, ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asal Tamu (Instansi)</label>
                            <select name="instansi_id" id="instansi-select" class="form-select" required <?php echo $guestbook_open ? '' : 'disabled'; ?>>
                                <option value="">Pilih asal instansi</option>
                                <?php foreach ($instansi as $ins) { ?>
                                    <option value="<?php echo (int)$ins['id']; ?>" <?php echo isset($instansi_id_raw) && (string)$instansi_id_raw === (string)$ins['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ins['nama'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php } ?>
                                <option value="lainnya" <?php echo isset($instansi_id_raw) && $instansi_id_raw === 'lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                            </select>
                            <?php if (!$instansi) { ?>
                                <div class="form-text text-danger">Belum ada data instansi. Hubungi admin.</div>
                            <?php } ?>
                        </div>
                        <div class="mb-3" id="instansi-lainnya-group" style="display:none;">
                            <label class="form-label">Asal Instansi Lainnya</label>
                            <input type="text" name="instansi_lainnya" class="form-control" <?php echo $guestbook_open ? '' : 'disabled'; ?> value="<?php echo isset($instansi_lainnya) ? htmlspecialchars($instansi_lainnya, ENT_QUOTES, 'UTF-8') : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Kehadiran (dengan Time Stamp)</label>
                            <input type="file" name="foto" class="form-control" accept="image/*" capture="environment" required <?php echo $guestbook_open ? '' : 'disabled'; ?>>
                            <div class="form-text">Ambil foto saat ini sebagai bukti kehadiran.</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" <?php echo $guestbook_open ? '' : 'disabled'; ?>>Kirim Buku Tamu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const waktuLabel = document.getElementById('waktu-sekarang');
if (waktuLabel) {
    function updateTime() {
        const now = new Date();
        const pad = n => n.toString().padStart(2, '0');
        const text = pad(now.getDate()) + '-' + pad(now.getMonth() + 1) + '-' + now.getFullYear() + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
        waktuLabel.textContent = text;
    }
    updateTime();
    setInterval(updateTime, 30000);
}
const instansiSelect = document.getElementById('instansi-select');
const instansiLainnyaGroup = document.getElementById('instansi-lainnya-group');
function toggleInstansiLainnya() {
    if (!instansiSelect) return;
    if (instansiSelect.value === 'lainnya') {
        instansiLainnyaGroup.style.display = 'block';
    } else {
        instansiLainnyaGroup.style.display = 'none';
    }
}
if (instansiSelect && instansiLainnyaGroup) {
    instansiSelect.addEventListener('change', toggleInstansiLainnya);
    toggleInstansiLainnya();
}
const kegiatanSelect = document.getElementById('kegiatan-select');
const kegiatanLainnyaGroup = document.getElementById('kegiatan-lainnya-group');
function toggleKegiatanLainnya() {
    if (!kegiatanSelect) return;
    if (kegiatanSelect.value === 'lainnya') {
        kegiatanLainnyaGroup.style.display = 'block';
    } else {
        kegiatanLainnyaGroup.style.display = 'none';
    }
}
if (kegiatanSelect && kegiatanLainnyaGroup) {
    kegiatanSelect.addEventListener('change', toggleKegiatanLainnya);
    toggleKegiatanLainnya();
}
</script>
</body>
</html>
