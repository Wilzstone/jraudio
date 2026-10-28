// Dialog konfirmasi aksi destruktif. Tidak mengubah endpoint/controller.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-jr-confirm]').forEach(form => {
    form.addEventListener('submit', async event => {
      if (form.dataset.jrApproved === 'yes') return;
      event.preventDefault();
      const msg = form.dataset.jrConfirm || 'Lanjutkan tindakan ini?';
      if (window.Swal && typeof Swal.fire === 'function') {
        const result = await Swal.fire({title:'Konfirmasi tindakan',text:msg,icon:'warning',
          showCancelButton:true,confirmButtonText:'Ya, lanjutkan',cancelButtonText:'Batal',reverseButtons:true,
          confirmButtonColor:'#be2934'});
        if (!result.isConfirmed) return;
      } else if (!window.confirm(msg)) return;
      form.dataset.jrApproved = 'yes';
      form.requestSubmit ? form.requestSubmit() : form.submit();
    });
  });
});
