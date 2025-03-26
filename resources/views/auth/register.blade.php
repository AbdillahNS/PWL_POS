<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Pengguna</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition register-page">
    <div class="register-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="{{ url('/') }}" class="h1"><b>Admin</b>LTE</a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Register untuk membuat akun baru</p>
                <form action="{{ url('/register') }}" method="POST" id="form-tambah">
                    @csrf
                    <div class="form-group">
                        <label>Level</label>
                        <select class="form-control w-100" id="level_id" name="level_id" required>
                            <option value="">- Pilih Level -</option>
                            @foreach($level as $item)
                                @if(in_array($item->level_nama, ['Manager', 'Staff/Kasir']))
                                    <option value="{{ $item->level_id }}">{{ $item->level_nama }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small id="error-level_id" class="error-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                        <small id="error-username" class="error-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="nama" id="nama" class="form-control" placeholder="Nama" required>
                        <small id="error-nama" class="error-text text-danger"></small>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                        <small id="error-password" class="error-text text-danger"></small>
                    </div>
                    <div class="row">
                        <div class="col-4 ml-auto">
                            <button type="submit" class="btn btn-primary btn-block">Daftar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#form-tambah").on("submit", function(event) {
                event.preventDefault(); // Cegah form submit langsung
                let formData = $(this).serialize();
                $.ajax({
                    url: "{{ url('/register') }}",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: "success",
                                title: "Registrasi Berhasil!",
                                text: response.message,
                                timer: 2000
                            }).then(() => {
                                window.location.href = "{{ url('/login') }}";
                            });
                        } else {
                            $('.error-text').text('');
                            $.each(response.errors, function(key, value) {
                                $('#error-' + key).text(value[0]);
                            });
                            Swal.fire({
                                icon: "error",
                                title: "Gagal Registrasi",
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Terjadi kesalahan pada server"
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>