@extends('layouts.template')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Daftar Transaksi Penjualan</h3>
        <div class="card-tools">
            <a href="{{ url('/penjualan/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export PDF</a>
            <button onclick="modalAction('{{ url('/penjualan/import') }}')" class="btn btn-info">Import Penjualan</button>
            <a href="{{ url('/penjualan/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Excel</a>
            <button onclick="modalAction('{{ url('/penjualan/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
        </div>
    </div>
    <div class="card-body">
        <div id="filter" class="form-horizontal filter-date p-2 border-bottom mb-2">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group row">
                        <label class="col-4 control-label col-form-label">Filter :</label>
                        <div class="col-6">
                            <input type="date" name="tanggal_penjualan" id="tanggal_penjualan" class="form-control" required>
                            <small id="error-tanggal_penjualan" class="error-text form-text text-danger"></small>                     
                            <small class="form-text text-muted">Tanggal penjualan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <table class="table table-bordered table-striped table-hover table-sm" id="table_penjualan">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Penjualan</th>
                    <th>Pembeli</th>
                    <th>Petugas</th>
                    <th>Tanggal Penjualan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" data-width="75%" aria-hidden="true"></div>
@endsection

@push('js')
<script>
    function modalAction(url = '') {
        $('#myModal').load(url, function () {
            $('#myModal').modal('show');
        });
    }
    var dataPenjualan;
    $(document).ready(function () {
        dataPenjualan = $('#table_penjualan').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: "{{ url('/penjualan/list') }}",
                type: "POST",
                data: function (d) {
                    d.tanggal_penjualan = $('#tanggal_penjualan').val();
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            },
            columns: [
                { 
                    data: "DT_RowIndex", 
                    className: "text-center", 
                    orderable: false, 
                    searchable: false 
                },{ 
                    data: "penjualan_kode", 
                    orderable: true 
                },{ 
                    data: "pembeli", 
                    orderable: true 
                },{ 
                    data: "user.username", 
                    orderable: true 
                },{ 
                    data: "tanggal_penjualan", 
                    orderable: true 
                },{ 
                    data: "aksi", 
                    orderable: false, 
                    searchable: false, 
                    className: "text-center" 
                }
            ]
        });

        $('#tanggal_penjualan').on('change', function () {
            dataPenjualan.ajax.reload();
        });
    });
</script>
@endpush