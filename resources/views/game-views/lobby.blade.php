@extends('layouts.main-layout')
@section('page-title')
    Lobby
@endsection
@push('css')
<style>
</style>
@endpush
@section('content')
    <div class="row m-0 mb-4 p-0 d-flex flex-row justify-content-center align-items-center" style="width: 90vw; max-width: 90vw;">
        <div class="col-2">
            <div class="image-profile-element-container">
                <img class="profile-pic-container" id="profilePicSelectionImage" src="{{ asset($default_pic['path']) }}">
            </div>
            <button type="button" class="btn btn-sm btn-outline-dark w-100 text-center mt-2" id="randomizePicButton"><i class="bi bi-dice-3-fill pe-1"></i>Randomize</button>
        </div>
        <div class="col-4">
            <input type="text" class="d-none" id="profilePicIdSelectionInput" name="profilePicIdSelection" hidden required readonly value="{{ $default_pic['id'] }}">
            <div class="input-group mb-3">
                <span class="input-group-text border-dark-subtle">Nama Player</span>
                <input type="text" class="form-control border-dark-subtle" id="namaPlayerInput" name="namaPlayer" required>
            </div>
            <button type="button" class="btn btn-success w-100 fw-bold fs-5" id="addNewPlayerButton"><i class="bi bi-plus-square-fill pe-2"></i>Tambah Player</button>
        </div>
        <div class="col-4">
            <form method="POST" action="{{ route('game.start-game', ['urlEncodedNamaSesi' => session('current_game')['url']]) }}">
                @csrf
                <div class="row m-0 p-0 gx-1 d-flex flex-row justify-content-center align-items-center w-100">
                    <div class="col-12 py-2">
                        <h6 class="fw-bold">Words List</h6>
                        <div class="p-2 border border-dark-subtle bg-white rounded-2 overflow-y-auto" id="wordsListArea" style="max-height: 8rem;">
                            @foreach ($words_list as $words)
                            <div class="form-check">
                                <input class="form-check-input" name="wordsList[]" type="checkbox" value="{{ $words['id'] }}" id="wordsListId{{ $words['id'] }}">
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
                        <button type="submit" class="btn btn-dark btn-lg text-center fs-5 fw-bold w-100 py-1"><i class="bi bi-play-fill pe-2"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row m-0 my-2 p-0 d-flex flex-row justify-content-center align-items-center" style="width: 90vw; max-width: 90vw;">
        <div class="col-2"></div>
        <div class="col-8">
            <h2 class="m-0 text-center fw-bold">Players List</h2>
        </div>
        <div class="col-2 d-flex flex-row justify-content-end align-items-center">
            <button type="button" class="btn btn-sm btn-outline-dark" id="refreshPlayersListButton"><i class="bi bi-arrow-clockwise pe-2"></i>Refresh</button>
        </div>
        <div class="col-12">
            <hr class="my-2 border-dark border">
        </div>
    </div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 m-0 p-0 d-flex flex-row justify-content-start align-items-center overflow-y-auto" id="player-list-area" style="width: 90vw; max-width: 90vw; height: 16rem; max-height: 16rem;">
    </div>
@endsection
@push('modals')

@endpush
@push('js')
<script>
    $(document).ready(function() {
        const profilePicSelectionImage = $("#profilePicSelectionImage");
        const profilePicIdSelectionInput = $("#profilePicIdSelectionInput");
        const randomizePicButton = $("#randomizePicButton");
        const playerNameInput = $("#namaPlayerInput");
        const addNewPlayerButton = $("#addNewPlayerButton");
        const refreshPlayersListButton = $("#refreshPlayersListButton");
        const playerListArea = $("div#player-list-area");
        const wordsListArea = $("div#wordsListArea");

        randomizePicButton.on('click', function() {
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
        });

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
                        randomizePicButton.trigger('click');
                        playerNameInput.val('').focus();
                    }
                },
                error: function(err) {
                    console.log(err);
                    var list_error = "";
                    for (const [inputName, arrayMessage] of Object.entries(err.responseJSON)) {
                        arrayMessage.forEach(message => {
                            list_error += `<li>${message}</li>`;
                        });
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
                }
            });
        });

        refreshPlayersListButton.trigger('click');
    });
</script>
@endpush