CATATAN BELANJA (HTML + PHP + JavaScript, tanpa database)

Cara pakai
1. Ekstrak ke folder web server PHP 7.4+ (XAMPP: htdocs/belanja, Laragon: www/belanja).
   Atau: buka terminal di folder ini, jalankan  php -S 0.0.0.0:8000
2. Pastikan folder "data" bisa ditulis (Linux: chmod 775 data).
3. Buka http://localhost/belanja/

Login admin bawaan (dibuat otomatis saat pertama dibuka):
   username: admin   |   sandi: admin123
   -> SEGERA ganti lewat Panel Admin > "Reset sandi saya".

Struktur
  index.php   Masuk / Daftar        app.php    Daftar belanja pengguna
  admin.php   Panel admin           api.php    Semua aksi (JSON)
  lib.php     Sesi, helper JSON     assets/    CSS & JavaScript
  data/users.json  Akun (sandi di-hash)    data/lists.json  Daftar belanja per pengguna

Catatan keamanan
  - data/.htaccess memblokir akses langsung ke file JSON (Apache). Di Nginx, tambahkan
    "location /data { deny all; }" atau pindahkan folder data ke luar web root.
  - Sandi disimpan dengan password_hash, API dilindungi token CSRF.
