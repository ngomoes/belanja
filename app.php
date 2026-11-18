<?php require __DIR__ . '/lib.php'; $u = guard(); head('Belanja · Catatan Belanja', 'app'); topbar($u); ?>
<main class="wrap">
  <p class="hello">Halo, <b><?= htmlspecialchars($u['name']) ?></b></p>
  <input id="title" class="title" value="Belanja" maxlength="80" aria-label="Nama daftar belanja">

  <section class="glass panel">
    <label for="cash">Uang yang dibawa (Rp)</label>
    <input id="cash" type="number" min="0" step="1000" inputmode="numeric" placeholder="Contoh: 200000">
    <small id="cashFmt" class="status"></small>
  </section>

  <section class="glass hero" aria-live="polite">
    <p class="lbl">Kembalian</p>
    <p id="change" class="big">Rp 0</p>
    <div class="track"><span id="fill"></span></div>
    <div class="stats">
      <div><span>Uang dibawa</span><b id="cashV">Rp 0</b></div>
      <div><span>Total belanja</span><b id="total">Rp 0</b></div>
      <div><span>Sudah dibeli</span><b id="count">0/0</b></div>
    </div>
    <p id="warn" class="warn" hidden></p>
  </section>

  <form id="addForm" class="glass panel add" autocomplete="off">
    <input id="iName" placeholder="Mau beli apa? (mis. Telur)" required maxlength="80" aria-label="Nama barang">
    <input id="iQty" type="number" min="0.01" step="any" value="1" inputmode="decimal" aria-label="Jumlah">
    <input id="iPrice" type="number" min="0" step="500" placeholder="Harga satuan" inputmode="numeric" aria-label="Harga satuan">
    <button class="btn">Tambah</button>
  </form>

  <ul id="list" class="list"></ul>
  <p id="empty" class="empty">Belum ada barang. Tulis yang mau dibeli, lalu isi perkiraan harganya.</p>
  <div class="foot"><button id="reset" class="chip" type="button">Daftar baru</button><span id="status" class="status"></span></div>
</main>
<script src="assets/app.js"></script></body></html>
