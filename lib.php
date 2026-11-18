<?php
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
define('DATA', __DIR__ . '/data');
if (!is_dir(DATA)) mkdir(DATA, 0775, true);

function jread($f, $d = []) { $p = DATA . "/$f.json"; if (!file_exists($p)) return $d; $j = json_decode(file_get_contents($p), true); return is_array($j) ? $j : $d; }
function jwrite($f, $v) { return file_put_contents(DATA . "/$f.json", json_encode($v, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX); }

// Akun admin bawaan dibuat otomatis saat pertama kali jalan: admin / admin123 (SEGERA GANTI!)
function users() {
  $u = jread('users');
  if (!$u) {
    $u = [['id' => 'u1', 'username' => 'admin', 'name' => 'Administrator', 'role' => 'admin', 'active' => true,
           'hash' => password_hash('admin123', PASSWORD_DEFAULT), 'created' => date('c')]];
    jwrite('users', $u);
  }
  return $u;
}
function me() {
  if (empty($_SESSION['uid'])) return null;
  foreach (users() as $u) if ($u['id'] === $_SESSION['uid'] && $u['active']) return $u;
  return null;
}
function csrf() { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function guard($admin = false) {
  $u = me();
  if (!$u || ($admin && $u['role'] !== 'admin')) { header('Location: index.php'); exit; }
  return $u;
}
function head($title, $page) {
  echo '<!DOCTYPE html><html lang="id" data-theme="light"><head><meta charset="UTF-8">'
  . '<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">'
  . '<meta name="csrf" content="' . csrf() . '"><title>' . htmlspecialchars($title) . '</title>'
  . '<script>try{document.documentElement.dataset.theme=localStorage.getItem("theme")||"light"}catch(e){}</script>'
  . '<link rel="preconnect" href="https://fonts.googleapis.com">'
  . '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">'
  . '<link rel="stylesheet" href="assets/style.css"></head><body data-page="' . $page . '">'
  . '<div class="blobs" aria-hidden="true"><i></i><i></i><i></i></div>';
}
function topbar($u) {
  echo '<header class="bar glass"><a class="brand" href="' . ($u['role'] === 'admin' ? 'admin.php' : 'app.php') . '">🛒 Catatan Belanja</a><nav>';
  if ($u['role'] === 'admin') echo '<a class="chip" href="admin.php">Admin</a><a class="chip" href="app.php">Belanja saya</a>';
  echo '<button id="themeBtn" class="chip" type="button" aria-label="Ganti tema">🌙</button>'
     . '<button id="logout" class="chip" type="button">Keluar</button></nav></header>';
}
