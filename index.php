<?php require __DIR__ . '/lib.php';
if ($u = me()) { header('Location: ' . ($u['role'] === 'admin' ? 'admin.php' : 'app.php')); exit; }
head('Masuk · Catatan Belanja', 'auth'); ?>
<main class="auth">
  <button id="themeBtn" class="chip theme-fab" type="button" aria-label="Ganti tema">🌙</button>
  <section class="glass card">
    <h1>🛒 Catatan Belanja</h1>
    <p class="sub">Tahu persis berapa uang yang dibawa dan apa saja yang dibeli.</p>
    <div class="tabs" role="tablist">
      <button type="button" class="tab on" data-tab="login">Masuk</button>
      <button type="button" class="tab" data-tab="signup">Daftar</button>
    </div>
    <form id="authForm" autocomplete="on">
      <label id="nameRow" hidden>Nama<input id="aName" maxlength="40" autocomplete="name"></label>
      <label>Username<input id="aUser" required minlength="3" maxlength="20" autocapitalize="none" autocomplete="username"></label>
      <label>Kata sandi<input id="aPass" type="password" required minlength="6" autocomplete="current-password"></label>
      <p id="msg" class="msg" role="alert" hidden></p>
      <button class="btn" id="submitBtn">Masuk</button>
    </form>
  </section>
</main>
<script src="assets/app.js"></script></body></html>
