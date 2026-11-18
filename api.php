<?php
require __DIR__ . '/lib.php';
header('Content-Type: application/json; charset=utf-8');
function out($d, $c = 200) { http_response_code($c); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

$a = $_GET['a'] ?? '';
$in = json_decode(file_get_contents('php://input'), true) ?: [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals(csrf(), $_SERVER['HTTP_X_CSRF'] ?? ''))
  out(['error' => 'Sesi tidak valid. Muat ulang halaman.'], 403);

if ($a === 'signup') {
  $un = strtolower(trim($in['username'] ?? '')); $nm = mb_substr(strip_tags(trim($in['name'] ?? '')), 0, 40); $pw = $in['password'] ?? '';
  if (!preg_match('/^[a-z0-9_]{3,20}$/', $un)) out(['error' => 'Username 3-20 karakter: huruf kecil, angka, garis bawah.'], 422);
  if ($nm === '') $nm = $un;
  if (strlen($pw) < 6) out(['error' => 'Kata sandi minimal 6 karakter.'], 422);
  $us = users();
  foreach ($us as $x) if ($x['username'] === $un) out(['error' => 'Username sudah dipakai.'], 409);
  $us[] = ['id' => 'u' . bin2hex(random_bytes(4)), 'username' => $un, 'name' => $nm, 'role' => 'user', 'active' => true,
           'hash' => password_hash($pw, PASSWORD_DEFAULT), 'created' => date('c')];
  jwrite('users', $us);
  $a = 'login'; // langsung masuk setelah daftar
}
if ($a === 'login') {
  $un = strtolower(trim($in['username'] ?? '')); $pw = $in['password'] ?? '';
  foreach (users() as $x) if ($x['username'] === $un && password_verify($pw, $x['hash'])) {
    if (!$x['active']) out(['error' => 'Akun diblokir. Hubungi admin.'], 403);
    session_regenerate_id(true); $_SESSION['uid'] = $x['id'];
    out(['ok' => true, 'role' => $x['role']]);
  }
  usleep(500000);
  out(['error' => 'Username atau kata sandi salah.'], 401);
}
if ($a === 'logout') { session_destroy(); out(['ok' => true]); }

$u = me(); if (!$u) out(['error' => 'Belum masuk.'], 401);

if ($a === 'list_get') { $l = jread('lists'); out($l[$u['id']] ?? ['title' => 'Belanja', 'cash' => 0, 'items' => []]); }
if ($a === 'list_save') {
  $c = ['title' => mb_substr(strip_tags($in['title'] ?? 'Belanja'), 0, 80), 'cash' => max(0, (float)($in['cash'] ?? 0)), 'items' => []];
  foreach (array_slice($in['items'] ?? [], 0, 300) as $i)
    $c['items'][] = ['id' => preg_replace('/[^a-z0-9]/i', '', (string)($i['id'] ?? uniqid())), 'name' => mb_substr(strip_tags($i['name'] ?? ''), 0, 80),
      'qty' => max(0.01, (float)($i['qty'] ?? 1)), 'price' => max(0, (float)($i['price'] ?? 0)), 'done' => !empty($i['done']), 'paid' => max(0, (float)($i['paid'] ?? 0))];
  $l = jread('lists'); $l[$u['id']] = $c; jwrite('lists', $l);
  out(['ok' => true]);
}

// ---- Khusus admin ----
if ($u['role'] !== 'admin') out(['error' => 'Khusus admin.'], 403);
$id = $in['id'] ?? '';
if ($a === 'admin_users') {
  $l = jread('lists'); $r = [];
  foreach (users() as $x) {
    $it = $l[$x['id']]['items'] ?? [];
    $r[] = ['id' => $x['id'], 'username' => $x['username'], 'name' => $x['name'], 'role' => $x['role'], 'active' => $x['active'], 'created' => $x['created'],
            'items' => count($it), 'est' => array_sum(array_map(fn($i) => $i['qty'] * $i['price'], $it)), 'self' => $x['id'] === $u['id']];
  }
  out($r);
}
if ($id === $u['id'] && in_array($a, ['admin_toggle', 'admin_delete'])) out(['error' => 'Tidak bisa pada akun sendiri.'], 422);
if ($a === 'admin_toggle') { $us = users(); foreach ($us as &$x) if ($x['id'] === $id) $x['active'] = !$x['active']; jwrite('users', $us); out(['ok' => true]); }
if ($a === 'admin_delete') {
  jwrite('users', array_values(array_filter(users(), fn($x) => $x['id'] !== $id)));
  $l = jread('lists'); unset($l[$id]); jwrite('lists', $l); out(['ok' => true]);
}
if ($a === 'admin_reset') {
  $pw = $in['password'] ?? ''; if (strlen($pw) < 6) out(['error' => 'Kata sandi minimal 6 karakter.'], 422);
  $us = users(); foreach ($us as &$x) if ($x['id'] === $id) $x['hash'] = password_hash($pw, PASSWORD_DEFAULT);
  jwrite('users', $us); out(['ok' => true]);
}
out(['error' => 'Aksi tidak dikenal.'], 404);
