<form action="{{ url('/penjualan/ajax') }}" method="POST" id="form-tambah-penjualan">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Penjualan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                <div class="form-group">
                    <label>Kode Penjualan</label>
                    <input type="text" name="penjualan_kode" id="penjualan_kode" class="form-control" required>
                    <small id="error-penjualan_kode" class="error-text form-text text-danger"></small>
                </div>

                <div class="form-group">
                    <label>Pembeli</label>
                    <input type="text" name="pembeli" id="pembeli" class="form-control" required>
                    <small id="error-pembeli" class="error-text form-text text-danger"></small>
                </div>

                <div class="form-group">
                    <label>Tanggal Penjualan</label>
                    <input type="date" name="tanggal_penjualan" id="tanggal_penjualan_form" class="form-control">
                    <small id="error-tanggal_penjualan" class="error-text form-text text-danger"></small>
                </div>

                <div class="form-group">
                    <label>Detail Barang</label>
                    <table class="table table-bordered" id="barang-table">
                        <thead>
                            <tr>
                                <th>Nama Barang</th>
                                <th>Harga Jual</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="barang_id[]" class="form-control barang-select" required>
                                        <option value="">- Pilih Barang -</option>
                                        @foreach($barangs as $barang)
                                            <option value="{{ $barang->barang_id }}" data-harga="{{ $barang->harga_jual }}">{{ $barang->barang_nama }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="detail_harga[]" class="form-control detail-harga" readonly required></td>
                                <td><input type="number" name="detail_jumlah[]" class="form-control detail-jumlah" required></td>
                                <td><input type="number" name="detail_total[]" class="form-control detail-total" readonly required></td>
                                <td><button type="button" class="btn btn-danger remove-barang-row">Hapus</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary add-barang-row">Tambah Barang</button>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function () {
    // Set tanggal default ke hari ini
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal_penjualan_form').val(today);

    $("#form-tambah-penjualan").validate({
        rules: {
            penjualan_kode: { required: true, minlength: 3 },
            pembeli: { required: true, maxlength: 100 },
            tanggal_penjualan: { required: true, date: true },
            'barang_id[]': { required: true },
            'detail_jumlah[]': { required: true, number: true, min: 1 }
        },
        submitHandler: function (form) {
            if (!$('#tanggal_penjualan_form').val()) {
                $('#tanggal_penjualan_form').val(today);
            }

            $.ajax({
                url: form.action,
                type: form.method,
                data: $(form).serialize(),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function (response) {
                    if (response.status) {
                        $('#myModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        });
                        dataPenjualan.ajax.reload(); // reload tabel penjualan
                    } else {
                        $('.error-text').text('');
                        $.each(response.msgField, function (prefix, val) {
                            $('#error-' + prefix).text(val[0]);
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseText || 'Terjadi kesalahan server'
                    });
                }
            });
            return false;
        }
    });

    // Fungsi tambah baris barang baru
    $('.add-barang-row').click(function () {
        const newRow = `
            <tr>
                <td>
                    <select name="barang_id[]" class="form-control barang-select" required>
                        <option value="">- Pilih Barang -</option>
                        @foreach($barangs as $barang)
                            <option value="{{ $barang->barang_id }}" data-harga="{{ $barang->harga_jual }}">{{ $barang->barang_nama }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="detail_harga[]" class="form-control detail-harga" readonly required></td>
                <td><input type="number" name="detail_jumlah[]" class="form-control detail-jumlah" required></td>
                <td><input type="number" name="detail_total[]" class="form-control detail-total" readonly required></td>
                <td><button type="button" class="btn btn-danger remove-barang-row">Hapus</button></td>
            </tr>
        `;
        $('#barang-table tbody').append(newRow);
        bindBarangSelect();
    });

    // Fungsi hapus baris barang
    $('#barang-table').on('click', '.remove-barang-row', function () {
        $(this).closest('tr').remove();
    });

    function bindBarangSelect() {
        $('.barang-select').off('change').on('change', function () {
            const harga = $(this).find(':selected').data('harga') || 0;
            const jumlah = $(this).closest('tr').find('.detail-jumlah').val() || 0;
            const total = harga * jumlah;

            $(this).closest('tr').find('.detail-harga').val(harga);
            $(this).closest('tr').find('.detail-total').val(total);

            $(this).closest('tr').find('.detail-jumlah').off('input').on('input', function () {
                const newJumlah = $(this).val();
                $(this).closest('tr').find('.detail-total').val(harga * newJumlah);
            });
        });
    }

    bindBarangSelect();
});
</script>