@extends('layouts.template')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Stok Barang</h3>
        <div class="card-tools">
            <a href="{{ url('/stok/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Stok</a> 
            <button onclick="modalAction('{{ url('/stok/import') }}')" class="btn btn-info">Import Stok</button>
            <a href="{{ url('/stok/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Stok</a> 
            <button onclick="modalAction('{{ url('/stok/create_ajax') }}')" class="btn btn-success">Tambah Stok (Ajax)</button>
        </div>
    </div>

    <div class="card-body">
        <div id="filter" class="form-horizontal filter-date p-2 border-bottom mb-2">
            <div class="row">
                <div class="col-md-10">
                    <div class="form-group form-group-sm row text-sm mb-0 align-items-center">
                        <label class="col-md-1 control-label col-form-label">Filter :</label>
                        
                        <div class="col-md-3">
                            <select name="filter_barang" class="form-control form-control-sm filter_barang">
                                <option value="">- Semua Barang -</option>
                                @foreach($barang as $b)
                                    <option value="{{ $b->barang_id }}">{{ $b->barang_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Barang</small>
                        </div>
        
                        <div class="col-md-3">
                            <select name="filter_supplier" class="form-control form-control-sm filter_supplier">
                                <option value="">- Semua Supplier -</option>
                                @foreach($supplier as $s)
                                    <option value="{{ $s->supplier_id }}">{{ $s->supplier_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Supplier</small>
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

        <table class="table table-bordered table-sm table-striped table-hover" id="table-stok">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Supplier</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div id="myModal" class="modal fade animate shake" tabindex="-1" data-backdrop="false" data-keyboard="false" data-width="75%"></div>
@endsection

@push('js')
<script>
    function modalAction(url = '') {
        $('#myModal').load(url, function () {
            $('#myModal').modal('show');
        });
    }
    var dataStok;
    $(document).ready(function () {
        dataStok = $('#table-stok').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('stok/list') }}",
                type: "POST",
                dataType: "json",
                data: function (d) {
                    d.barang_id = $('.filter_barang').val();
                    d.supplier_id = $('.filter_supplier').val();
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            },
            columns: [
                {
                    data: "DT_RowIndex",
                    className: "text-center",
                    width: "5%",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "supplier.supplier_nama",
                    width: "15%",
                    orderable: true,
                    searchable: true
                },
                {
                    data: "barang.barang_nama",
                    width: "20%",
                    orderable: true,
                    searchable: true
                },
                {
                    data: "stok_jumlah",
                    className: "text-center",
                    width: "10%",
                    render: function (data) {
                        return new Intl.NumberFormat('id-ID').format(data);
                    }
                },
                {
                    data: "stok_tanggal",
                    width: "15%"
                },
                {
                    data: "user.username",
                    width: "15%"
                },
                {
                    data: "aksi",
                    className: "text-center",
                    width: "20%",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('.filter_barang, .filter_supplier').change(function () {
            dataStok.draw();
        });
    });
</script>
@endpush