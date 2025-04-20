<!-- Modal ubah profil -->
<div class="modal fade" id="modal-ubah-profil" tabindex="-1" role="dialog" aria-labelledby="ubahProfilLabel" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog" role="document">
        <form action="{{ url('/profil') }}" method="POST" enctype="multipart/form-data" id="form-foto-profil">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Foto Profil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="foto_profil">Upload Foto</label>
                        <input type="file" class="form-control" name="foto_profil" id="foto_profil">
                        <small id="error-foto_profil" class="form-text text-danger"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        $("#form-foto-profil").validate({
            rules: {
                foto_profil: {
                    required: true,
                    extension: "jpg|jpeg|png"
                }
            },
            messages: {
                foto_profil: {
                    required: "Harap pilih file foto.",
                    extension: "File harus berupa JPG, JPEG, atau PNG."
                }
            },
            submitHandler: function(form, event) {
                event.preventDefault(); // Mencegah pengiriman form default

                var formData = new FormData(form);
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#modal-ubah-profil').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            $('#avatar-img').attr('src', response.foto_url + '?v=' + new Date().getTime());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan dalam proses pengunggahan foto.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi kesalahan',
                            text: errorMessage
                        });
                    }
                });
            }
        });
    });
</script>
@endpush