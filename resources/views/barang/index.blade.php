@extends('layouts.template')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Barang</h3>
        <div class="card-tools">
            <a href="{{ url('/barang/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Barang</a> 
            <button onclick="modalAction('{{ url('/barang/import') }}')" class="btn btn-info">Import Barang</button>
            <a href="{{ url('/barang/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Barang</a> 
            <button onclick="modalAction('{{ url('/barang/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
        </div>
    </div>

    <div class="card-body">
        <!-- Filter Data -->
        <div id="filter" class="form-horizontal filter-date p-2 border-bottom mb-2">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-group-sm row text-sm mb-0">
                        <label for="filter_date" class="col-md-1 col-form-label">Filter</label>
                        <div class="col-md-3">
                            <select name="filter_kategori" class="form-control form-control-sm filter_kategori">
                                <option value="">- Semua -</option>
                                @foreach($kategori as $l)
                                    <option value="{{ $l->kategori_id }}">{{ $l->kategori_nama }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Kategori Barang</small>
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

        <table class="table table-bordered table-sm table-striped table-hover" id="table-barang">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal untuk form ajax -->
<div id="myModal" class="modal fade animate shake" tabindex="-1" data-backdrop="false"
    data-keyboard="false" data-width="75%">
</div>
@endsection

@push('js')
<script>
    function modalAction(url = '') {
        $('#myModal').load(url, function () {
            $('#myModal').modal('show');
        });
    }

    var tableBarang;

    $(document).ready(function () {
        tableBarang = $('#table-barang').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('barang/list') }}",
                type: "POST",
                dataType: "json",
                data: function (d) {
                    d.kategori_id = $('.filter_kategori').val();
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
                    data: "barang_kode",
                    width: "10%",
                    orderable: true,
                    searchable: true
                },
                {
                    data: "barang_nama",
                    width: "37%",
                    orderable: true,
                    searchable: true
                },
                {
                    data: "harga_beli",
                    width: "10%",
                    orderable: true,
                    searchable: false,
                    render: function (data) {
                        return new Intl.NumberFormat('id-ID').format(data);
                    }
                },
                {
                    data: "harga_jual",
                    width: "10%",
                    orderable: true,
                    searchable: false,
                    render: function (data) {
                        return new Intl.NumberFormat('id-ID').format(data);
                    }
                },
                {
                    data: "kategori.kategori_nama",
                    width: "14%",
                    orderable: true,
                    searchable: false
                },
                {
                    data: "aksi",
                    className: "text-center",
                    width: "14%",
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#table-barang_filter input').unbind().bind().on('keyup', function () {
            tableBarang.search(this.value).draw();
        });

        $('.filter_kategori').change(function () {
            tableBarang.draw();
        });
    });
</script>
@endpush