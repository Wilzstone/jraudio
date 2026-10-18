<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#12141c">
    <title>Login Admin | JR Audio</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/jr-favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login-admin.css') }}">
</head>
<body>
<div class="login-layout">
    <section class="login-showcase" aria-label="JR Audio">
        <div class="login-showcase-top">
            <img src="{{ asset('assets/img/jr-favicon.svg') }}" alt="Logo JR Audio">
            <span>JR AUDIO / MANAGEMENT SYSTEM</span>
        </div>
        <div class="login-showcase-copy">
            <p class="login-tag">PANEL ADMINISTRASI</p>
            <h1>Kelola event.<br><em>Atur semuanya.</em></h1>
            <p>Kelola penyewaan, peralatan, pelanggan, dan laporan melalui satu sistem yang terhubung.</p>
            <div class="login-pills"><span>Inventaris</span><span>Transaksi</span><span>Analitik</span></div>
        </div>
        <div class="login-showcase-foot">JR AUDIO <span>© {{ date('Y') }}</span></div>
    </section>
    <main class="login-main">
        <div class="login-card">
            <div class="login-mobile-brand"><img src="{{ asset('assets/img/jr-favicon.svg') }}" alt="JR Audio"><strong>JR AUDIO</strong></div>
            <p class="login-kicker">SELAMAT DATANG KEMBALI</p>
            <h2>Masuk ke akunmu</h2>
            <p class="login-subtitle">Gunakan akun Owner atau Karyawan JR Audio.</p>
            @if (session('error'))
                <div class="login-alert" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="login-alert" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ url('/proses-login') }}" class="login-form">
                @csrf
                <div class="login-field">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" placeholder="Masukkan username" value="{{ old('username') }}" autocomplete="username" maxlength="100" required autofocus>
                </div>
                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="login-password-wrap">
                        <input id="password" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" id="togglePassword" class="login-eye" aria-controls="password" aria-label="Tampilkan password" aria-pressed="false">Lihat</button>
                    </div>
                </div>
                <button type="submit" class="login-submit">Masuk ke Dashboard <span aria-hidden="true">→</span></button>
            </form>
            <div class="login-safe"><span aria-hidden="true">◈</span> Akses khusus Owner &amp; Karyawan</div>
            <a class="login-back" href="{{ url('/') }}">← Kembali ke website publik</a>
        </div>
    </main>
</div>
<script>
const toggle = document.getElementById('togglePassword');
const password = document.getElementById('password');
toggle.addEventListener('click', () => {
  const visible = password.type === 'password';
  password.type = visible ? 'text' : 'password';
  toggle.textContent = visible ? 'Sembunyi' : 'Lihat';
  toggle.setAttribute('aria-pressed', String(visible));
  toggle.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
});
</script>
</body>
</html>
