<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login JR Audio</title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/admin-custom.css">
    <link rel="stylesheet" href="/assets/css/sweetalert2.min.css">
</head>
<body class="bg-dark">
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow" style="width: 380px;">
        <div class="card-body p-4">
            <img src="/assets/img/logo-jraudio.png" alt="JR Audio" class="brand-logo-login">
            <h4 class="text-center mb-1">Login JR Audio</h4>
            <p class="text-center text-muted mb-4">Login Owner / Karyawan</p>

            <?php if (!empty(session()->get('error'))): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars(session()->get('error')) ?>
                </div>
            <?php endif; ?>

            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form action="/proses-login" method="POST">
    @csrf
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-dark w-100">Login</button>
            </form>
        </div>
    </div>
</div>

<script src="/assets/js/sweetalert2.min.js"></script>
<?php if (!empty(session()->get('error'))): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: <?= json_encode(session()->get('error')); ?>,
        confirmButtonText: 'OK'
    });
});
</script>

<?php endif; ?>

</body>
</html>
