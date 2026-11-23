@extends('layouts.main-layout')
@inject('mainService', 'App\Services\MainService')
@section('page-title')
    Lobby
@endsection
@push('css')
<style>
    /* Bootstrap hanya punya row-cols sampai 6, jadi yang 8 kolom dibuat sendiri (pola sama dengan Bootstrap).
       Kolom mengikuti lebar modal-xl: 500px di bawah 992px, 800px di 992px, 1140px mulai 1200px. */
    @media (min-width: 1200px) {
        .row-cols-xl-8 > * {
            flex: 0 0 auto;
            width: 12.5%;
        }
    }
    /* Pilihan profile pic di modal "Change" */
    .profile-pic-option .image-profile-element-container {
        max-width: 80px;
        max-height: 80px;
    }
    .profile-pic-option .image-profile-element-container img {
        width: 100%;
        height: auto;
    }
    .profile-pic-option small {
        overflow-wrap: anywhere;
    }
    .profile-pic-option.active {
        background-color: var(--bs-success-bg-subtle);
        border-color: var(--bs-success);
        border-width: 3px;
        color: var(--bs-dark);
    }
</style>
@endpush
@section('content')
    <div class="row m-0 mb-4 p-0 gy-3 gy-md-0 d-flex flex-row justify-content-center align-items-center" style="width: 90vw; max-width: 90vw;">
        <div class="col-4 col-md-2">
            <div class="image-profile-element-container">
                <img class="profile-pic-container" id="profilePicSelectionImage" src="{{ asset($default_pic['path']) }}">
            </div>
            <button type="button" class="btn btn-sm btn-outline-dark w-100 text-center mt-2" id="changePicButton" data-bs-toggle="modal" data-bs-target="#profilePicModal"><i class="bi bi-person-bounding-box pe-1"></i>Change</button>
            <button type="button" class="btn btn-sm btn-outline-dark w-100 text-center mt-2" id="randomPicButton"><i class="bi bi-dice-3 pe-1"></i>Randomize</button>
        </div>
        <div class="col-8 col-md-3">
            <input type="text" class="d-none" id="profilePicIdSelectionInput" name="profilePicIdSelection" hidden required readonly value="{{ $default_pic['id'] }}">
            <label class="" for="namaPlayerInput">Nama Pemain</label>
            <input type="text" class="form-control border-dark-subtle mb-3" id="namaPlayerInput" name="namaPlayer" required>
            <button type="button" class="btn btn-success w-100 fw-bold fs-6" id="addNewPlayerButton"><i class="bi bi-plus-square-fill pe-2"></i>Tambah Pemain</button>
        </div>
        <div class="col-12 col-md-7">
            <form method="POST" action="{{ route('game.start-game', ['urlEncodedNamaSesi' => session('current_game')['url']]) }}">
                @csrf
                <div class="row m-0 p-0 gx-1 d-flex flex-row justify-content-center align-items-center w-100">
                    <div class="col-12 py-2">
                        <h6 class="fw-bold">Daftar Kata - Kata</h6>
                        <div class="p-2 border border-dark-subtle bg-white rounded-2 overflow-y-auto" id="wordsListArea" style="max-height: 8rem;">
                            @foreach ($words_list as $words)
                            <div class="form-check">
                                <input class="form-check-input border-dark-subtle" name="wordsList[]" type="checkbox" value="{{ $words['id'] }}" id="wordsListId{{ $words['id'] }}">
                                <label class="form-check-label" for="wordsListId{{ $words['id'] }}">
                                    {{ $words['name'] }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="input-group">
                            <span class="input-group-text border-dark-subtle">Jml Ronde</span>
                            <input type="number" step="1" min="1" max="16" class="form-control border-dark-subtle" id="jumlahRondeInput" name="jumlahRonde" required>
                        </div>
                    </div>
                    <div class="col-3">
                        <button type="submit" class="btn btn-dark btn-lg text-center fs-5 fw-bold w-100 py-1">Mulai<i class="bi bi-play-fill ps-2"></i></button>
                    </div>
                    <div class="col-12 pt-1">
                        <button class="btn btn-link btn-sm p-0 text-dark text-decoration-none small" type="button" data-bs-toggle="collapse" data-bs-target="#pengaturanLainnya" aria-expanded="false" aria-controls="pengaturanLainnya">
                            <i class="bi bi-gear-fill pe-1"></i>Pengaturan Lainnya <i class="bi bi-chevron-down small"></i>
                        </button>
                        <div class="collapse" id="pengaturanLainnya">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 m-0 p-0 g-1">
                                <div class="col">
                                    <div class="input-group input-group-sm mt-1">
                                        <span class="input-group-text border-dark-subtle" title="Timer"><i class="bi bi-stopwatch small"></i></span>
                                        <input type="number" step="1" min="0" max="600" value="{{ old('batasWaktu', 0) }}" class="form-control border-dark-subtle" id="batasWaktuInput" name="batasWaktu" inputmode="numeric" title="0 = Tanpa Timer. Recommended Minimum : 30">
                                        <span class="input-group-text border-dark-subtle">Detik</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row m-0 my-2 p-0 d-flex flex-row justify-content-center align-items-center" style="width: 90vw; max-width: 90vw;">
        <div class="col-3 col-md-2"></div>
        <div class="col-6 col-md-8">
            <h2 class="m-0 text-center fw-bold text-nowrap">Daftar Pemain</h2>
        </div>
        <div class="col-3 col-md-2 d-flex flex-row justify-content-end align-items-center">
            <button type="button" class="btn btn-sm btn-outline-dark" id="refreshPlayersListButton"><i class="bi bi-arrow-clockwise pe-2"></i>Muat Ulang</button>
        </div>
        <div class="col-12">
            <hr class="my-2 border-dark border">
        </div>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 m-0 p-0 d-flex flex-row justify-content-start align-items-start overflow-y-auto" id="player-list-area" style="width: 90vw; max-width: 90vw; height: 16rem; max-height: 16rem;">
    </div>
@endsection
@push('modals')
    <div class="modal fade" id="profilePicModal" tabindex="-1" aria-labelledby="profilePicModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <h1 class="modal-title fs-3" id="profilePicModalLabel">Pilih Profile Picture</h1>
                </div>
                <div class="modal-body py-3">
                    <div class="row row-cols-3 row-cols-sm-4 row-cols-lg-6 row-cols-xl-8 g-2 m-0">
                        @foreach ($emojis as $emoji)
                        <div class="col">
                            {{-- Seluruh kotak bisa diklik; memilih profile pic ini lalu menutup modal --}}
                            <button type="button" class="profile-pic-option btn btn-outline-dark w-100 h-100 p-2 d-flex flex-column align-items-center justify-content-start" data-id="{{ $emoji['id'] }}" data-path="{{ asset($emoji['path']) }}">
                                <div class="image-profile-element-container w-100">
                                    <img class="profile-pic-container" src="{{ asset($emoji['path']) }}" alt="{{ $emoji['id'] }}" loading="lazy">
                                </div>
                                <small class="mt-1 lh-sm">{{ $emoji['id'] }}</small>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endpush
@push('js')
<script>
    $(document).ready(function() {
        const profilePicRandomizeButton = $("#randomPicButton");
        const profilePicSelectionImage = $("#profilePicSelectionImage");
        const profilePicIdSelectionInput = $("#profilePicIdSelectionInput");
        const profilePicModal = $("#profilePicModal");
        const playerNameInput = $("#namaPlayerInput");
        const addNewPlayerButton = $("#addNewPlayerButton");
        const refreshPlayersListButton = $("#refreshPlayersListButton");
        const playerListArea = $("div#player-list-area");
        const wordsListArea = $("div#wordsListArea");

        // Saat modal dibuka, tandai profile pic yang sedang terpilih (default acak atau pilihan terakhir)
        profilePicModal.on('show.bs.modal', function() {
            const selectedId = profilePicIdSelectionInput.val();
            profilePicModal.find('.profile-pic-option').each(function() {
                $(this).toggleClass('active', $(this).attr('data-id') === selectedId);
            });
        });

        // Klik salah satu pilihan: jadikan profile pic player ini, lalu tutup modal
        profilePicModal.on('click', '.profile-pic-option', function() {
            profilePicIdSelectionInput.val($(this).attr('data-id')).trigger('change');
            profilePicSelectionImage.attr('src', $(this).attr('data-path'));
            profilePicModal.modal('hide');
        });

        // Randomize Profile Picture
        profilePicRandomizeButton.on('click', function() {
            randomizeProfilePic();
        });

        // Profile pic acak sebagai default untuk player berikutnya (dipakai setelah Tambah Player)
        function randomizeProfilePic() {
            $.ajax({
                type: "GET",
                url: "{{ route('get-random-picture') }}",
                success: function(result) {
                    if (result) {
                        profilePicIdSelectionInput.val(result.id).trigger('change');
                        profilePicSelectionImage.attr('src', result.path);
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        playerNameInput.on('keyup', function(e) {
            if (e.originalEvent.keyCode == '13' || e.originalEvent.key == 'Enter') {
                addNewPlayerButton.trigger('click');
            }
        });

        addNewPlayerButton.on('click', function() {
            $.ajax({
                type: "POST",
                url: "{{ route('game.add-new-player', ['urlEncodedNamaSesi' => session('current_game')['url']]) }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    profilePicID: profilePicIdSelectionInput.val(),
                    name: playerNameInput.val(),
                },
                success: function(result) {
                    if (result && result.success) {
                        refreshPlayersListButton.trigger('click');
                        randomizeProfilePic();
                        playerNameInput.val('').focus();
                    }
                },
                error: function(err) {
                    console.log(err);
                    showErrorList(err);
                }
            });
        });

        refreshPlayersListButton.on('click', function() {
            $.ajax({
                type: "GET",
                url: "{{ route('game.fetch-players-list', ['urlEncodedNamaSesi' => session('current_game')['url']]) }}",
                success: function(result) {
                    if (result) {
                        playerListArea.html('');
                        for (const [playerId, playerData] of Object.entries(result)) {
                            playerListArea.append($("<div>")
                                .addClass("col p-1")
                                .attr('id', playerId)
                                .append($("<div>")
                                    .addClass("row m-0 p-2 d-flex flex-row justify-content-start align-items-center bg-secondary-subtle border border-dark rounded-2")
                                    .append($("<div>")
                                        .addClass("col-3 p-2")
                                        .html(`
                                            <div class="image-profile-element-container">
                                                <img class="profile-pic-container" id="profilePic_${playerId}" src="${playerData.profilePicPath}">
                                            </div>
                                        `)
                                    )
                                    .append($("<div>")
                                        .addClass("col-7 p-2")
                                        .html(`
                                            <p class="fw-bold text-start m-0">${playerData.name}</p>
                                        `)
                                    )
                                    .append($("<div>")
                                        .addClass("col-2 p-0 text-end")
                                        .html(`
                                            <button value="${playerId}" type="button" style="aspect-ratio:1/1; line-height:0;" class="btn btn-lg btn-danger p-2 h-100 fw-bold delete-player">
                                                ×
                                            </button>
                                        `)
                                    )
                                )
                            );
                        }
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        });

        playerListArea.on('click', 'button.delete-player', function() {
            const playerId = $(this).val();
            $.ajax({
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    player_id: playerId,
                },
                url: "{{ route('game.delete-player', ['urlEncodedNamaSesi' => session('current_game')['url']]) }}",
                success: function(result) {
                    if (result && result.success) {
                        refreshPlayersListButton.trigger('click');
                    }
                },
                error: function(err) {
                    console.log(err);
                    showErrorList(err);
                    refreshPlayersListButton.trigger('click');
                }
            });
        });

        function showErrorList(err) {
            var list_error = "";
            for (const [inputName, arrayMessage] of Object.entries(err.responseJSON ?? {})) {
                [].concat(arrayMessage).forEach(message => {
                    list_error += `<li>${message}</li>`;
                });
            }
            if (!list_error) {
                list_error = `<li>Terjadi kesalahan (HTTP ${err.status}).</li>`;
            }
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
                                ${list_error}
                            </ul>
                        </div>
                `
            });
        }

        refreshPlayersListButton.trigger('click');
    });
</script>
@endpush