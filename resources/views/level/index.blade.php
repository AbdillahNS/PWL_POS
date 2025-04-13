@extends('layouts.template')

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card=title">{{$page->title}}</h3>
        <div class="card-tools">
            <a href="{{ url('/level/export_pdf') }}" class="btn btn-warning"><i class="fa fa-file-pdf"></i> Export Level</a> 
            <button onclick="modalAction('{{ url('/level/import') }}')" class="btn btn-info">Import Level</button>
            <a href="{{ url('/level/export_excel') }}" class="btn btn-primary"><i class="fa fa-file-excel"></i> Export Level</a> 
            <button onclick="modalAction('{{ url('/level/create_ajax') }}')" class="btn btn-success">Tambah Data (Ajax)</button>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{session('success')}}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{session('error')}}</div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row">
                    <label class="col-1 control-label col-form-label">Filter:</label>
                    <div class="col-3">
                        <select name="level_id" id="level_id" class="form-control" required>
                            <option value="">- Semua -</option>
                            @foreach($level as $item)
                                <option value="{{$item->level_id}}">{{$item->level_nama}}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Level</small>
                    </div>
                </div>
            </div>
        </div>
        <table class="table table-bordered table-striped table-hover table-sm" id="table_level">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Level Kode</th>
                    <th>Nama Level</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" data-width="75%" aria-hidden="true"></div>

@endsection
@push('css')
@endpush

@push('js')
    <script>
        function modalAction(url = ''){ 
            $('#myModal').load(url,function(){ 
                $('#myModal').modal('show'); 
            }); 
        }

        var dataLevel;
        $(document).ready(function() {
            dataLevel = $('#table_level').DataTable({
            serverSide: true, // serverSide: true, jika ingin menggunakan server side processing
            ajax: {
                "url": "{{ url('level/list') }}",
                "dataType": "json",
                "type": "POST",
                "data":function(d){
                    d.level_id = $('#level_id').val();
                }
            },
            columns: [
                {
                    data: "DT_RowIndex", // nomor urut dari laravel datatable addIndexColumn()
                    className: "text-center",
                    orderable: false,
                    searchable: false
                    },{
                    data: "level_kode", 
                    className: "",
                    orderable: true, // orderable: true, jika ingin kolom ini bisa diurutkan
                    searchable: true // searchable: true, jika ingin kolom ini bisa dicari
                    },{
                    data: "level_nama", 
                    className: "",
                    orderable: true, 
                    searchable: true 
                    },{
                    data: "aksi", 
                    className: "",
                    orderable: false, 
                    searchable: false 

                    }
                ]
            });
            $('#level_id').on('change',function(){
                dataLevel.ajax.reload();
            })
        });
    </script>
@endpush 