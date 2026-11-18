(() => {
  const $ = s => document.querySelector(s);
  const rp = n => 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
  const csrf = $('meta[name=csrf]').content;
  const page = document.body.dataset.page;
  async function api(a, body) {
    const o = body ? { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF': csrf }, body: JSON.stringify(body) } : {};
    const r = await fetch('api.php?a=' + a, o);
    const d = await r.json().catch(() => ({ error: 'Respons server tidak valid' }));
    if (!r.ok) throw new Error(d.error || 'Terjadi kesalahan');
    return d;
  }
  function toast(m) { const t = document.createElement('div'); t.className = 'toast'; t.textContent = m; document.body.append(t); setTimeout(() => t.remove(), 2800); }
  const h = (tag, p = {}, ...kids) => { const e = Object.assign(document.createElement(tag), p); e.append(...kids); return e; };

  // ---- Tema (default terang) ----
  const tb = $('#themeBtn');
  const paint = () => { if (tb) tb.textContent = document.documentElement.dataset.theme === 'dark' ? '☀️' : '🌙'; };
  if (tb) tb.onclick = () => {
    const n = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = n; try { localStorage.setItem('theme', n); } catch (_) {} paint();
  };
  paint();
  const lo = $('#logout');
  if (lo) lo.onclick = async () => { await api('logout', {}); location.href = 'index.php'; };

  // ---- Halaman masuk / daftar ----
  if (page === 'auth') {
    let mode = 'login';
    document.querySelectorAll('.tab').forEach(t => t.onclick = () => {
      mode = t.dataset.tab;
      document.querySelectorAll('.tab').forEach(x => x.classList.toggle('on', x === t));
      $('#nameRow').hidden = mode !== 'signup';
      $('#submitBtn').textContent = mode === 'signup' ? 'Buat akun' : 'Masuk';
      $('#aPass').autocomplete = mode === 'signup' ? 'new-password' : 'current-password';
      $('#msg').hidden = true;
    });
    $('#authForm').onsubmit = async e => {
      e.preventDefault();
      try {
        const d = await api(mode, { name: $('#aName').value, username: $('#aUser').value, password: $('#aPass').value });
        location.href = d.role === 'admin' ? 'admin.php' : 'app.php';
      } catch (err) { const m = $('#msg'); m.hidden = false; m.textContent = err.message; }
    };
  }

  // ---- Dashboard belanja ----
  if (page === 'app') {
    let S = { title: 'Belanja', cash: 0, items: [] }, timer;
    const save = () => {
      clearTimeout(timer); $('#status').textContent = 'Menyimpan…';
      timer = setTimeout(async () => {
        try { await api('list_save', S); $('#status').textContent = 'Tersimpan'; }
        catch (e) { $('#status').textContent = 'Gagal menyimpan: ' + e.message; }
      }, 400);
    };
    // Total = jumlah x harga satuan semua barang
    const total = () => S.items.reduce((s, i) => s + i.qty * i.price, 0);
    const stats = () => {
      const t = total(), change = S.cash - t;
      $('#change').textContent = (change < 0 ? '-' : '') + rp(Math.abs(change));
      $('#cashV').textContent = rp(S.cash); $('#total').textContent = rp(t);
      $('#count').textContent = S.items.filter(i => i.done).length + '/' + S.items.length;
      $('#cashFmt').textContent = S.cash ? '= ' + rp(S.cash) : '';
      $('#fill').style.width = (S.cash ? Math.min(100, t / S.cash * 100) : 0) + '%';
      const w = $('#warn');
      if (t > 0 && S.cash <= 0) { w.hidden = false; w.textContent = 'Isi dulu uang yang dibawa.'; }
      else if (change < 0) { w.hidden = false; w.textContent = 'Uang kurang ' + rp(-change) + '. Kurangi barang atau tambah uang.'; }
      else w.hidden = true;
    };
    const render = () => {
      stats(); $('#empty').hidden = S.items.length > 0;
      const ul = $('#list'); ul.innerHTML = '';
      S.items.forEach(i => {
        const cb = h('input', { type: 'checkbox', checked: i.done }); cb.setAttribute('aria-label', 'Sudah dibeli: ' + i.name);
        cb.onchange = () => { i.done = cb.checked; save(); render(); };
        const right = h('div', { className: 'right' }, h('div', { className: 'sum', textContent: rp(i.qty * i.price) }));
        right.append(h('button', { className: 'del', type: 'button', textContent: 'Hapus', onclick: () => { S.items = S.items.filter(x => x !== i); save(); render(); } }));
        ul.append(h('li', { className: 'glass item' + (i.done ? ' done' : '') }, cb,
          h('div', {}, h('div', { className: 'nm', textContent: i.name }), h('div', { className: 'meta', textContent: i.qty + ' × ' + rp(i.price) })), right));
      });
    };
    $('#addForm').onsubmit = e => {
      e.preventDefault(); const name = $('#iName').value.trim(); if (!name) return;
      S.items.push({ id: Date.now().toString(36) + Math.random().toString(36).slice(2, 6), name, qty: parseFloat($('#iQty').value) || 1, price: parseFloat($('#iPrice').value) || 0, done: false, paid: 0 });
      $('#iName').value = ''; $('#iPrice').value = ''; $('#iQty').value = 1; $('#iName').focus(); save(); render();
    };
    $('#title').oninput = e => { S.title = e.target.value || 'Belanja'; save(); };
    $('#cash').oninput = e => { S.cash = parseFloat(e.target.value) || 0; save(); stats(); };
    $('#reset').onclick = () => {
      if (!confirm('Mulai daftar baru? Daftar dan uang saat ini akan dihapus.')) return;
      S = { title: 'Belanja', cash: 0, items: [] };
      $('#title').value = 'Belanja'; $('#cash').value = ''; save(); render();
    };
    api('list_get').then(d => { S = { title: d.title || 'Belanja', cash: d.cash || 0, items: d.items || [] }; $('#title').value = S.title; $('#cash').value = S.cash || ''; render(); })
      .catch(e => { $('#status').textContent = e.message; });
  }

  // ---- Panel admin ----
  if (page === 'admin') {
    let users = [];
    const act = async (a, body, ok) => { try { await api(a, body); toast(ok); load(); } catch (e) { toast(e.message); } };
    const draw = () => {
      const q = $('#q').value.toLowerCase(), box = $('#tbl'); box.innerHTML = '';
      users.filter(u => (u.name + u.username).toLowerCase().includes(q)).forEach(u => {
        const acts = h('div', { className: 'acts' });
        if (!u.self) acts.append(
          h('button', { className: 'chip', type: 'button', textContent: u.active ? 'Blokir' : 'Aktifkan', onclick: () => act('admin_toggle', { id: u.id }, 'Status diperbarui') }),
          h('button', { className: 'chip', type: 'button', textContent: 'Reset sandi', onclick: () => { const p = prompt('Kata sandi baru untuk ' + u.username + ' (min. 6 karakter):'); if (p) act('admin_reset', { id: u.id, password: p }, 'Kata sandi diganti'); } }),
          h('button', { className: 'chip dng', type: 'button', textContent: 'Hapus', onclick: () => { if (confirm('Hapus akun ' + u.username + ' beserta daftarnya?')) act('admin_delete', { id: u.id }, 'Akun dihapus'); } }));
        else acts.append(h('button', { className: 'chip', type: 'button', textContent: 'Reset sandi saya', onclick: () => { const p = prompt('Kata sandi baru (min. 6 karakter):'); if (p) act('admin_reset', { id: u.id, password: p }, 'Kata sandi diganti'); } }));
        box.append(h('div', { className: 'row' },
          h('div', {}, h('b', { textContent: u.name }), h('small', { textContent: '@' + u.username })),
          h('span', { className: 'tag' + (u.role === 'admin' ? ' adm' : ''), textContent: u.role }),
          h('span', { className: 'tag' + (u.active ? '' : ' off'), textContent: u.active ? 'Aktif' : 'Diblokir' }),
          h('div', {}, h('b', { textContent: u.items + ' barang' }), h('small', { textContent: rp(u.est) })), acts));
      });
      if (!box.children.length) box.append(h('p', { className: 'empty', textContent: 'Tidak ada pengguna ditemukan.' }));
    };
    const load = async () => {
      try {
        users = await api('admin_users');
        $('#kUsers').textContent = users.length; $('#kActive').textContent = users.filter(u => u.active).length;
        $('#kItems').textContent = users.reduce((s, u) => s + u.items, 0); $('#kEst').textContent = rp(users.reduce((s, u) => s + u.est, 0));
        draw();
      } catch (e) { $('#status').textContent = e.message; }
    };
    $('#q').oninput = draw; load();
  }
})();
