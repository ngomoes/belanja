<?php require __DIR__ . '/lib.php'; $u = guard(true); head('Admin · Catatan Belanja', 'admin'); topbar($u); ?>
<main class="wrap wide">
  <h1 class="h1">Panel Admin</h1>
  <section class="kpis">
    <div class="glass kpi"><span>Pengguna</span><b id="kUsers">0</b></div>
    <div class="glass kpi"><span>Akun aktif</span><b id="kActive">0</b></div>
    <div class="glass kpi"><span>Total barang</span><b id="kItems">0</b></div>
    <div class="glass kpi"><span>Total estimasi</span><b id="kEst">Rp 0</b></div>
  </section>
  <section class="glass panel">
    <input id="q" type="search" placeholder="Cari nama atau username…" aria-label="Cari pengguna">
    <div class="tbl" id="tbl"></div>
  </section>
  <p id="status" class="status"></p>
</main>
<script src="assets/app.js"></script></body></html>
