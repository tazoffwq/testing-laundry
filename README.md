# 🧺 Aplikasi Shaa Laundry

Aplikasi **Shaa Laundry** adalah sistem manajemen laundry sederhana berbasis **Web** yang dibangun menggunakan **PHP**, **MySQL**, **HTML**, **CSS**, dan **JavaScript**.  
Aplikasi ini memiliki dua peran pengguna: **Admin** dan **User**, dengan dashboard yang terpisah sesuai kebutuhan masing-masing.

---

## 🔑 Akun Dummy
Anda dapat menggunakan akun berikut untuk langsung mencoba aplikasi tanpa perlu registrasi:

**Admin**
- Username: `admin123`
- Password: `admin#1234`

**User**
- Username: `user@mail.com`
- Password: `user123`

---

## ✨ Fitur Utama

### 👤 Untuk Pengguna (User)
- **Login / Register** – Masuk atau membuat akun baru.  
- **Dashboard Pengguna** – Menampilkan ringkasan pesanan (total, diproses, selesai, diambil).  
- **Daftar Layanan & Harga** – Melihat layanan laundry beserta harga.  
- **Buat Pesanan** – Membuat pesanan baru dengan memilih layanan dan berat cucian.  
- **Riwayat Pesanan** – Melihat status pesanan aktif dan riwayat sebelumnya.  

### 🛠️ Untuk Admin
- **Dashboard Admin** – Ringkasan bisnis secara real-time.  
- **Statistik Pesanan** – Total pesanan, pending, diproses, selesai, diambil, serta total pendapatan.  
- **Manajemen Harga** – Mengelola dan memperbarui harga layanan.  
- **Kelola Pesanan** – Memfilter, memperbarui status, mengubah detail pesanan (layanan/berat), atau menghapus pesanan.  

---

## ⚙️ Persyaratan Sistem
- **Web Server**: Apache / Nginx / sejenisnya  
- **Database**: MySQL / MariaDB  
- **PHP**: Versi `8.2.12` atau lebih tinggi  

---

## 🗂️ Struktur Database
Database didefinisikan dalam file `laundry.sql` dan berisi tabel berikut:

- **users** → Data pengguna (username, password terenkripsi, role: admin/user)  
- **services** → Data layanan laundry (nama & harga)  
- **orders** → Data pesanan (user_id, service_id, berat, total_price, status: Pending/Proses/Selesai/Diambil)  

---

## 🚀 Instalasi

1. **Kloning repositori** ke folder root web server Anda (mis. `htdocs` untuk XAMPP):  
   ```bash
   git clone https://github.com/username/shaa-laundry.git
   ```

2. **Konfigurasi Database**:  
   - Buat database baru bernama `laundry`.  
   - Import file `laundry.sql` ke database.  

3. **Konfigurasi Koneksi**:  
   Buka file `config.php`, lalu sesuaikan:  
   ```php
   $host = "localhost";
   $user = "root"; 
   $pass = "";     
   $db   = "laundry";
   ```

4. **Akses Aplikasi**:  
   Buka browser lalu arahkan ke:  
   ```
   http://localhost/nama-folder-proyek
   ```

---

## 🎨 Interaksi Front-end
Aplikasi ini menggunakan **JavaScript** untuk menambah efek visual & interaktif:

- **Efek Fokus Input** – Input field akan memberi efek naik saat difokuskan.  
- **Ripple Effect** – Tombol memiliki efek ripple saat diklik.  
- **Notifikasi** – Muncul di pojok kanan atas sebagai feedback aksi pengguna.  
- **Animasi Dashboard** – Kartu statistik admin muncul dengan efek *fade-in up*.  

---

## 📄 Halaman Utama
- `index.php` → Halaman login.  
- `register.php` → Halaman registrasi user baru.  
- `dashboard.php` → Redirect otomatis ke dashboard user/admin sesuai role.  
- `dashboard_user.php` → Dashboard untuk user.  
- `dashboard_admin.php` → Dashboard untuk admin.  
- `logout.php` → Menghapus sesi & kembali ke halaman login.  

---

## 📌 Catatan
- Password di database sudah dienkripsi menggunakan metode bawaan PHP (`password_hash`).  
- Anda dapat menyesuaikan layanan & harga sesuai kebutuhan melalui dashboard admin.  

---

## 👨‍💻 Kontributor
Dikembangkan oleh **Aufa** sebagai proyek aplikasi laundry berbasis web.
