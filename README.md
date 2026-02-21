# Aplikasi Buku Tamu TP PKK Kecamatan Koja

Aplikasi **Buku Tamu TP PKK Kecamatan Koja** adalah aplikasi web sederhana untuk mencatat kedatangan tamu secara digital, dengan fokus pada kemudahan penggunaan di perangkat mobile dan kemudahan pengelolaan data oleh admin.

Aplikasi ini dikembangkan dengan teknologi **PHP murni** dan **MySQL**, tanpa framework berat, sehingga ringan dan mudah dipasang di shared hosting (misalnya cPanel) maupun lokal (XAMPP).

---

## Fitur Utama

- **Form Buku Tamu (Mobile Friendly)**
  - Input nama lengkap, nomor HP, jabatan, alamat.
  - Pilihan **Jenis Kegiatan** (dropdown) dengan opsi **Lainnya** + kolom teks.
  - Pilihan **Instansi / Asal Tamu** (dropdown) dengan opsi **Lainnya** + kolom teks.
  - Upload **foto kehadiran** dengan timestamp (menggunakan kamera HP).
  - Informasi tanggal & jam tampil dan diperbarui otomatis.
  - Halaman **Terima Kasih** setelah submit, dengan info waktu submit.

- **Dashboard Admin**
  - Ringkasan jumlah:
    - Total **Jenis Kegiatan**
    - Total **Tamu**
    - Total **Instansi**
  - Kartu dashboard berwarna ala AdminLTE namun tetap ringan (tanpa library tambahan).
  - Status **Buku Tamu Dibuka/Ditutup** tampil di dashboard.

- **Manajemen Data Master**
  - **Jenis Kegiatan**
    - Tambah / ubah / hapus jenis kegiatan.
    - Tampil dalam tabel dengan nomor urut rapi dan tombol aksi sejajar.
  - **Instansi**
    - Tambah / ubah / hapus instansi.
    - Kolom “Dipakai” menampilkan berapa kali instansi digunakan pada data tamu.
    - Tabel dengan nomor urut rapi dan tombol aksi sejajar.

- **Data Tamu**
  - Filter berdasarkan rentang tanggal dan jenis kegiatan.
  - Tabel data dengan:
    - No urut
    - Nama
    - No. HP
    - Jabatan
    - Jenis Kegiatan
    - Instansi
    - Alamat
    - Waktu kedatangan
    - Link untuk melihat foto kehadiran
  - **Export CSV**
    - File `data_tamu_YYYYMMDD_HHMMSS.csv`.
    - Baris diurutkan **ascending** berdasarkan waktu kedatangan (tamu pertama di atas).
  - **Tampilan Cetak (Print View)**
    - Rekap buku tamu dengan periode dan jenis kegiatan.
    - Siap dicetak langsung dari browser.

- **Keamanan & Kontrol Akses**
  - **Halaman Admin dengan Login**
    - Autentikasi admin dengan username dan password.
    - Password disimpan dengan `password_hash()` di tabel `admin_users`.
    - Batas percobaan login dan regenerasi session ID saat login sukses.
  - **Ganti Password Admin**
    - Halaman khusus admin untuk mengganti password dengan aman.
  - **Buka/Tutup Buku Tamu**
    - Admin bisa menutup form tamu (misalnya di luar jam kerja) untuk mencegah spam.
    - Saat buku tamu ditutup, form tidak bisa diisi dan muncul pesan info.
  - **Link Login Admin Tersembunyi**
    - Di halaman buku tamu ada link kecil “Admin” yang tidak mencolok agar tamu umum tidak tergoda mencoba-coba.
  - **Pencegahan Input Ganda**
    - Sistem mengecek nomor HP dan tanggal. Jika nomor HP yang sama sudah mengisi pada hari itu, tamu akan mendapat pesan bahwa data sudah tercatat dan tidak dibuat entri baru.

---

## Teknologi yang Digunakan

- **Bahasa Pemrograman**: PHP (tanpa framework)
- **Basis Data**: MySQL / MariaDB
- **Web Server**: Apache (contoh: XAMPP / hosting cPanel)
- **Front-end**:
  - Bootstrap 5 (via CDN)
  - HTML5 & CSS3
  - JavaScript murni untuk:
    - Update waktu berjalan
    - Menampilkan/menyembunyikan field “Lainnya”

---

## Struktur Folder

- `index.php` – halaman buku tamu utama (form tamu).
- `admin/` – halaman dan logika admin:
  - `admin/index.php` – dashboard admin.
  - `admin/kegiatan.php` – CRUD jenis kegiatan.
  - `admin/instansi.php` – CRUD instansi.
  - `admin/tamu.php` – data tamu, filter, export CSV, tampilan cetak.
  - `admin/login.php`, `admin/logout.php` – autentikasi admin.
  - `admin/change_password.php` – ganti password admin.
- `uploads/` – menyimpan foto kehadiran tamu.
- `config.php` – konfigurasi database dan pengaturan dasar admin.
- `db.php` – helper koneksi database.
- `tamu_db.sql` – skrip SQL untuk membuat seluruh struktur database dan data awal.

---

## Kebutuhan Sistem

- PHP 7.4+ atau PHP 8.x
- MySQL / MariaDB
- Web server (Apache atau sejenis)
- Ekstensi PHP:
  - `mysqli`
  - `mbstring`
  - `fileinfo` (untuk deteksi tipe MIME file foto)
- Izin tulis pada folder `uploads/` untuk menyimpan foto kehadiran.

---

## Cara Instalasi (Lokal dengan XAMPP)

1. **Clone / Salin Project**

   ```bash
   git clone https://github.com/karira212/aplikasi-buku-tamu.git
   ```

   atau salin folder ke:

   ```text
   C:\xampp\htdocs\tamu
   ```

2. **Buat Database**

   - Buka **phpMyAdmin**.
   - Buat database, misalnya: `tamu_db`.
   - Import file **`tamu_db.sql`** yang ada di project ke database tersebut.

3. **Konfigurasi Database**

   - Buka file `config.php`.
   - Sesuaikan:

     ```php
     $db_host = 'localhost';
     $db_name = 'tamu_db';
     $db_user = 'root';
     $db_pass = '';
     ```

   - Jika di hosting, sesuaikan dengan kredensial database dari provider.

4. **Akses Aplikasi**

   - Halaman buku tamu:

     ```text
     http://localhost/tamu/
     ```

   - Halaman admin:

     ```text
     http://localhost/tamu/admin/login.php
     ```

---

## Penggunaan

### Sebagai Admin

1. **Login Admin**
   - Akses `admin/login.php` atau klik link kecil “Admin” di pojok bawah halaman buku tamu.
   - Masuk menggunakan username dan password yang telah dikonfigurasi / disimpan di database.
   - Untuk keamanan, informasi username/password default tidak ditampilkan di halaman login.

2. **Buka/Tutup Buku Tamu**
   - Masuk ke dashboard admin (`admin/index.php`).
   - Di kartu “Jumlah Instansi” terdapat status **Buku Tamu: Dibuka/Ditutup** dan tombol untuk mengubah status.
   - Saat ditutup:
     - Form di halaman tamu non-aktif.
     - Muncul pesan “Buku tamu sedang ditutup. Silakan hubungi petugas.”

3. **Mengelola Jenis Kegiatan**
   - Menu **Jenis Kegiatan**.
   - Tambah, ubah, hapus data.
   - Urutan tampilan memakai nomor urut (bukan ID database).

4. **Mengelola Instansi**
   - Menu **Instansi**.
   - Tambah, ubah, hapus data.
   - Kolom “Dipakai” menunjukkan frekuensi instansi dalam data tamu.

5. **Melihat dan Menyimpan Data Tamu**
   - Menu **Data Tamu**.
   - Gunakan filter tanggal dan jenis kegiatan.
   - Klik **Export CSV** untuk mengunduh semua data hasil filter dalam bentuk file `.csv`.
   - Klik **Tampilan Cetak** untuk rekap siap print.

6. **Ganti Password Admin**
   - Masuk ke menu ganti password (di halaman admin).
   - Masukkan password lama, password baru, dan konfirmasi password baru.

7. **Cegah Input Ganda**
   - Sistem otomatis menolak input kedua pada hari yang sama dengan nomor HP yang sama.
   - Tamu akan mendapat pesan: *“Anda sudah mengisi buku tamu. Silakan masuk.”*

### Sebagai Tamu

1. Buka halaman buku tamu (disiapkan di tablet/HP di lokasi).
2. Isi semua kolom yang wajib:
   - Nama lengkap
   - No. HP
   - Jenis kegiatan
   - Asal instansi
   - Foto kehadiran (wajib)
3. Tekan **Kirim Buku Tamu**.
4. Setelah berhasil, tamu akan melihat halaman **Terima Kasih**.

---

## Keamanan

- Password admin disimpan menggunakan `password_hash()` dan diverifikasi dengan `password_verify()`.
- Sistem membatasi jumlah percobaan login dan meregenerasi session ID saat login berhasil.
- Form buku tamu bisa ditutup oleh admin melalui dashboard untuk menghindari spam atau penyalahgunaan ketika perangkat berada di tempat umum.
- Link login admin di halaman tamu dibuat kecil dan tidak mencolok sehingga tidak mengganggu tampilan tamu tetapi tetap mudah diakses oleh pengelola.
- Sistem menyimpan IP address dan user agent (informasi perangkat) saat tamu mengisi buku tamu untuk keperluan audit dan keamanan.

---

## Lisensi & Penggunaan

Aplikasi ini **gratis digunakan** (free) untuk:

- Kegiatan **pendidikan**
- Kegiatan sosial
- Penggunaan internal organisasi non-komersial

Silakan gunakan dan modifikasi sesuai kebutuhan, dengan tetap menjaga atribusi kepada pembuat.

---

## Pembuat

Aplikasi ini dibuat oleh:

**Teknologi Informasi Indonesia**

Jika Anda membutuhkan aplikasi sejenis untuk:

- Keperluan pendidikan
- Kegiatan pelatihan
- Atau ingin dibuatkan aplikasi lain sesuai kebutuhan

Silakan hubungi:

- **WhatsApp / Telepon**: `081287172216`
- Donaso se-iklasnya : BRI Rek. 034001005720534 An.TURINO HADISAPUTRO

Aplikasi ini dengan senang hati dapat dikembangkan lebih lanjut sesuai kebutuhan Anda. 
