<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('page-title') - {{ config('app.name', 'Laravel') }}</title>
    <link href="{{ asset('images/game-icon.png') }}" rel="icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    @stack('head')
    <style>
        body * {
            font-weight: 500;
            line-height: 1.25rem;
            font-family: 'Poppins', sans-serif;
        }
        .modal-backdrop.show {
            backdrop-filter: blur(4px);
            opacity: 1;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0);
        }
    </style>
    @stack('css')
</head>
<body>
    <div class="d-flex flex-column bg-dark vw-100 vh-100 p-2 align-items-center justify-content-center" style="max-height: 100vh; max-width: 100vw;">
        <div class="w-100 bg-secondary-subtle main-header-area h-100 py-1 px-2 rounded-top-4" style="max-height: 7.5vh;">
            <div class="row m-0 gx-2 d-flex flex-row justify-content-between align-items-center w-100 h-100">
                <div class="col-1">
                    <a href="/">
                        <img src="{{ asset('images/game-icon.png') }}" alt="Game Icon" class="img-fluid" style="max-height: 5vh;">
                    </a>
                </div>
                <div class="col-2">
                    <p class="m-0">Sesi game saat ini :</p>
                    <p class="m-0 fs-6 fw-bold">{{ session('current_game', '-') }}</p>
                </div>
                <div class="col-6">
                    <h2 class="m-0 text-center fw-bold">@yield('page-title')</h2>
                </div>
                <div class="col-2">
                    <p class="m-0">Jumlah sesi game :</p>
                    <p class="m-0 fs-6"><span class="fw-bold">{{ count(session('gameSessions', [])) }}</span> sesi game</p>
                </div>
                <div class="col-1">
                    <p class="m-0 p-0 text-center fw-bold text-primary" style="font-size: 0.75rem; cursor: pointer;" onclick="displayCredits();">Credits</p>
                </div>
            </div>
        </div>
        <div class="w-100 bg-light main-content-area flex-grow-1 d-flex flex-column align-items-center justify-content-center">
            @yield('content')
        </div>
        <div class="w-100 bg-secondary-subtle main-footer-area h-100 py-1 px-2 rounded-bottom-4" style="max-height: 7.5vh;">
            @yield('footer-area')
        </div>
    </div>
    @stack('modals')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="{{ asset('js/jquery-4.0.0.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script>
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                theme: 'bootstrap-5',
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                html: `
                        <div class="d-flex w-100 justify-content-center m-0 p-0">
                            <ul>
                            @foreach ($errors->all() as $error)
                                <li><p class="m-0 mb-1 text-start">{{ $error }}</p></li>
                            @endforeach
                            </ul>
                        </div>
                `
            });
        @endif

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                theme: 'bootstrap-5',
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                html: `
                        <div class="d-flex w-100 justify-content-center m-0 p-0">
                            <p class="m-0 my-2">{{ session('success') }}</p>
                        </div>
                `
            });
        @endif

        function displayCredits() {
            Swal.fire({
                icon: 'info',
                title: 'Komponen yang digunakan',
                theme: 'bootstrap-5',
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                html: `
                    <div class="d-flex flex-column w-100 justify-content-center align-items-center m-0 p-0">
                        <ul>
                            <li><p class="m-0 mb-1 text-start">Laravel 13</p></li>
                            <li><p class="m-0 mb-1 text-start">Bootstrap 5.3.8</p></li>
                            <li><p class="m-0 mb-1 text-start">jQuery 4.0.0</p></li>
                            <li><p class="m-0 mb-1 text-start">SweetAlert2</p></li>
                        </ul>
                    </div>
                `
            });
        }
    </script>
    @stack('js')
</body>
</html>