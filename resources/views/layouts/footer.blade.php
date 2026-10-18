</div>
<script src="/assets/js/sweetalert2.min.js"></script>

<?php if (!empty(session()->get('welcome_login'))): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Login Berhasil',
        text: <?= json_encode(session()->get('welcome_login')); ?>,
        confirmButtonText: 'OK',
        timer: 2200,
        timerProgressBar: true
    });
});
</script>
<?php session()->forget('welcome_login'); ?>
<?php endif; ?>

<?php if (!empty(session()->get('info'))): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'info',
        title: 'Informasi',
        text: <?= json_encode(session()->get('info')); ?>,
        confirmButtonText: 'OK'
    });
});
</script>
<?php session()->forget('info'); ?>
<?php endif; ?>

<?php if (!empty(session()->get('error'))): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: <?= json_encode(session()->get('error')); ?>,
        confirmButtonText: 'OK'
    });
});
</script>
<?php session()->forget('error'); ?>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('a[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            const url = el.getAttribute('href');
            const message = el.getAttribute('data-confirm') || 'Yakin melanjutkan aksi ini?';

            Swal.fire({
                icon: 'warning',
                title: 'Konfirmasi',
                text: message,
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed && url) {
                    window.location.href = url;
                }
            });
        });
    });
});
</script>
</body>
</html>
