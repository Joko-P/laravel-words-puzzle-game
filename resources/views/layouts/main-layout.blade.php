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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    @stack('head')
    <style>
        body *:not(b) {
            font-weight: 500;
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
        .image-profile-element-container {
            text-align: center;
            margin: 0 auto;
            border: var(--bs-dark) 2px solid;
            background-color: var(--bs-white);
            border-radius: 100%;
            max-width: 128px;
            max-height: 128px;
            padding: 0;
        }
        .profile-pic-container {
            scale: 0.75;
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
                    <p class="m-0 fs-6 fw-bold" style="cursor: pointer;" id="sessionDetailsButton">{{ session('current_game')['name'] ?? '-' }}</p>
                </div>
                <div class="col-6">
                    <h2 class="m-0 text-center fw-bold" id="header-screen-title">@yield('page-title')</h2>
                </div>
                <div class="col-2">
                    <p class="m-0">Jumlah sesi game :</p>
                    <p class="m-0 fs-6"><span class="fw-bold">{{ count(session('gameSessions', [])) }}</span> sesi game</p>
                </div>
                <div class="col-1">
                    <p class="m-0 p-0 text-center fw-bold text-primary" style="font-size: 0.75rem; cursor: pointer;" onclick="displayCredits();">Credits</p>
                    <p class="m-0 p-0 text-center fw-bold text-primary" style="font-size: 0.75rem; cursor: pointer;" onclick="displayGameRules();">Game Rules</p>
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
    <div class="modal fade" id="sessionDetailsModal" tabindex="-1" aria-labelledby="sessionDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered model-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <h1 class="modal-title fs-3" id="sessionDetailsModalLabel">Detail Sesi Game</h1>
                </div>
                <div class="modal-body py-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="row m-0 p-0 px-5 g-2 w-100">
                        <div class="col-4 d-flex flex-row justify-content-between">
                            <p class="m-0">Nama Sesi Game</p>
                            <p class="m-0">:</p>
                        </div>
                        <div class="col-8">
                            <p class="m-0 fw-bold" id="namaSesiInput"></p>
                        </div>
                        <div class="col-4 d-flex flex-row justify-content-between">
                            <p class="m-0">Sesi Dibuat</p>
                            <p class="m-0">:</p>
                        </div>
                        <div class="col-8">
                            <p class="m-0 fw-bold" id="createdAtInput"></p>
                        </div>
                        <div class="col-4 d-flex flex-row justify-content-between">
                            <p class="m-0">Game Mulai</p>
                            <p class="m-0">:</p>
                        </div>
                        <div class="col-8">
                            <p class="m-0 fw-bold" id="startInput"></p>
                        </div>
                        <div class="col-4 d-flex flex-row justify-content-between">
                            <p class="m-0">Game Selesai</p>
                            <p class="m-0">:</p>
                        </div>
                        <div class="col-8">
                            <p class="m-0 fw-bold" id="finishInput"></p>
                        </div>
                        <hr class="my-2 border border-dark">
                        <div class="col-12 m-0">
                            <p class="m-0 text-center fw-bold fs-3">Players List</p>
                        </div>
                        <div class="col-12">
                            <div class="row m-0 p-0 g-1 row-cols-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-3" id="playersListDetailArea">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger" id="sessionDeleteButton">Hapus Sesi</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="creditsModal" tabindex="-1" aria-labelledby="creditsModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered model-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <h1 class="modal-title fs-3" id="creditsModal">Komponen yang Digunakan</h1>
                </div>
                <div class="modal-body py-3 d-flex flex-column align-items-center justify-content-center">
                    <ul class="row m-0 row-cols-2">
                        <li><a href="https://laravel.com/" target="_blank"><p class="m-0 mb-1 text-start">Laravel 13</p></a></li>
                        <li><a href="https://getbootstrap.com/docs/5.3/" target="_blank"><p class="m-0 mb-1 text-start">Bootstrap 5.3.8</p></a></li>
                        <li><a href="https://jquery.com/" target="_blank"><p class="m-0 mb-1 text-start">jQuery 4.0.0</p></a></li>
                        <li><a href="https://sweetalert2.github.io/" target="_blank"><p class="m-0 mb-1 text-start">SweetAlert2</p></a></li>
                        <li><a href="https://openmoji.org/" target="_blank"><p class="m-0 mb-1 text-start">OpenMoji</p></a></li>
                    </ul>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="gameRulesModal" tabindex="-1" aria-labelledby="gameRulesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered model-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <h1 class="modal-title fs-3" id="gameRulesModalLabel">Peraturan Game</h1>
                </div>
                <div class="modal-body py-3 d-flex flex-column align-items-center justify-content-center">
                    <ol class="m-0 w-100">
                        <li>Sistem <span style="font-style: italic;">Scoring</span> :</li>
                        <ul class="m-0 w-100" style="padding-left: 1.25rem;">
                            <li>
                                <b>Jika menebak huruf</b>, akan ada skor dasar 25.<br>
                                Nilai akhir dihitung dengan 25 + (100 / jumlah huruf ditebak)<br>
                                Contoh, jika menebak huruf 'A' dan ada 5 huruf 'A' yang muncul, maka skor yang didapat : 25 + (100 / 5) => <b>45</b>.<br>
                                Contoh lain jika menebak huruf 'W' dan ada 2 huruf yang muncul, maka skor yang didapat : 25 + (100 / 2) => <b>75</b>.<br>
                                <b>Namun jika huruf yang ditebak tidak ada di kalimat, maka -25 poin</b>.
                            </li>
                            <li>
                                <b>Jika menebak kalimat</b>, akan ada skor dasar 100 dan skor 20 per-huruf.<br>
                                Jika tebakan <span class="text-success fw-bold">benar</span>, maka skor yang didapat 100 + (sisa huruf * 20).<br>
                                Jika tebakan <span class="text-danger fw-bold">salah</span>, maka skor yang didapat (sisa huruf * <b>-</b>20).<br>
                            </li>
                        </ul>
                    </ol>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
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
            $('#creditsModal').modal('show');
        }
        
        function displayGameRules() {
            $('#gameRulesModal').modal('show');
        }

        $(document).ready(function() {
            const sessionDetailsModal = $("#sessionDetailsModal");
            const sessionDetailButton = $("#sessionDetailsButton");
            const sessionDeleteButton = $("#sessionDeleteButton");

            sessionDetailButton.click(function() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('current-session-details') }}",
                    success: function(result) {
                        if (result) {
                            console.log(result);
                            sessionDetailsModal.find("#playersListDetailArea").html('');
                            sessionDetailsModal.find("#namaSesiInput").text(result.namaSesi);
                            sessionDetailsModal.find("#createdAtInput").text(result.sessionCreatedAt);
                            sessionDetailsModal.find("#startInput").text(result.startTime);
                            sessionDetailsModal.find("#finishInput").text(result.endTime);

                            const playersId = Object.keys(result.playersList);
                            playersId.forEach(playerId => {
                                const playerData = result.playersList[playerId];
                                sessionDetailsModal.find("#playersListDetailArea").append($("<div>")
                                    .addClass("col")
                                    .attr('id', playerId)
                                    .append($("<div>")
                                        .addClass("row m-0 p-0 g-0 d-flex flex-row align-items-center")
                                        .append($("<div>")
                                            .addClass("col-3 p-2")
                                            .append(`
                                                <div class="image-profile-element-container">
                                                    <img class="profile-pic-container" id="profilePic_${playerId}" src="${playerData.profilePicPath}">
                                                </div>
                                            `)
                                        )
                                        .append($("<div>")
                                            .addClass("col-8 p-1 d-flex flex-column align-items-start justify-content-center")
                                            .html(`
                                                <p class="m-0 fw-bold" style="font-size: 1.25rem;">${playerData.name}</p>
                                                <p class="m-0" style="font-size: 0.875rem;">Urutan : <span class="fw-bold">${playerData.order + 1}</span></p>
                                                <p class="m-0" style="font-size: 0.875rem;">Skor : <span class="fw-bold">${playerData.score}</span></p>
                                            `)
                                        )
                                    )
                                );
                            });

                            sessionDetailsModal.modal('show');
                        }
                    },
                    error: function(err) {
                        console.log(err);
                    }
                });
            });

            sessionDeleteButton.click(function() {
                const sessionName = sessionDetailsModal.find("#namaSesiInput").val();
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Anda akan menghapus sesi game "${sessionName}"!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('delete-game-session') }}",
                            data: {
                                "_token": "{{ csrf_token() }}"
                            },
                            success: function(result) {
                                if (result.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: result.success,
                                        theme: 'bootstrap-5',
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        }
                                    }).then(() => {
                                        window.location.href = '/';
                                    });
                                } else if (result.error) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Terjadi Kesalahan',
                                        text: result.error,
                                        theme: 'bootstrap-5',
                                        customClass: {
                                            confirmButton: 'btn btn-primary',
                                        }
                                    });

                                }
                            },
                            error: function(err) {
                                console.log(err);
                            }
                        });
                    }
                });
            });
        });
    </script>
    @stack('js')
</body>
</html>