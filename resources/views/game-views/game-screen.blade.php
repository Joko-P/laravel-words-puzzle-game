@extends('layouts.main-layout')
@section('page-title')
@endsection
@push('css')
<style>
    .word-row{
        display:flex;
        justify-content:center;
        align-items: center;
        margin-bottom:0.25rem;
        margin-top:0.25rem;
    }

    .letter{
        min-width: 3rem;
        height:4rem;
        margin:0.125rem;
        display:flex;
        justify-content:center;
        align-items:center;
        opacity:1;
        transition:.3s;
        user-select:none;
        border:2px solid rgba(0,0,0,0);
    }

    .letter p{
        font-size:2rem;
        font-weight:bold;
        margin:0;
    }

    .letter.filled{
        border:2px solid #212529;
    }

    .letter.show{
        opacity:1;
    }

    .guess-the-letter-btn{
        min-width: 3.5rem;
        max-width: 3.5rem;
        margin: 0.125rem;
        font-size: 1.5rem;
        text-align: center;
    }
</style>
@endpush
@section('content')
<div class="mb-4" id="words-display-area">

</div>
<div class="w-100 px-4 d-flex flex-row justify-content-center overflow-x-auto" style="max-width: 80vw;" id="player-turns-indicator-area">

</div>
<hr style="width: calc(100vw - 6rem);">
<div class="row m-0 p-0 g-0 w-100 d-flex flex-row justify-content-center align-items-center">
    <div class="col-2 text-center">
        <p class="fw-bold m-0 fs-5 mb-2">Leaderboard</p>
    </div>
    <div class="col-4 text-center">
        <p class="fw-bold m-0 fs-5 mb-2">Tebak Kalimatnya</p>
    </div>
    <div class="col-1"></div>
    <div class="col-5 text-center">
        <p class="fw-bold m-0 fs-5 mb-2">Tebak Hurufnya</p>
    </div>
    <div class="col-2 d-flex flex-column align-items-start justify-content-start overflow-y-auto" id="leaderboardArea" style="max-height: 16rem; height: 16rem;">

    </div>
    <div class="col-4 d-flex flex-row align-items-center justify-content-center">
        <div class="input-group m-0 w-75">
            <input type="text" id="guessTheSentenceInput" class="form-control border-dark">
            <button class="btn btn-dark fw-bold" type="button" id="guessTheSentenceButton">Tebak!</button>
        </div>
    </div>
    <div class="col-1 text-center">
        <p class="fw-bold m-0 fs-5">Atau</p>
    </div>
    <div class="col-5 text-center">
        <div class="m-0 p-0 w-100 d-flex flex-row justify-content-center align-items-center">
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="Q">Q</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="W">W</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="E">E</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="R">R</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="T">T</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="Y">Y</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="U">U</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="I">I</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="O">O</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="P">P</button>
        </div>
        <div class="m-0 p-0 w-100 d-flex flex-row justify-content-center align-items-center">
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="A">A</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="S">S</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="D">D</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="F">F</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="G">G</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="H">H</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="J">J</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="K">K</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="L">L</button>
        </div>
        <div class="m-0 p-0 w-100 d-flex flex-row justify-content-center align-items-center">
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="Z">Z</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="X">X</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="C">C</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="V">V</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="B">B</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="N">N</button>
            <button type="button" class="btn btn-outline-dark guess-the-letter-btn" value="M">M</button>
            <div class="guess-the-letter-btn"></div>
        </div>
    </div>
</div>
@endsection
@push('modals')

@endpush
@push('js')
<script>
    $(document).ready(function() {
        const wordsDisplayArea = $("div#words-display-area");
        const guessTheSentenceInput = $("input#guessTheSentenceInput");
        const guessTheSentenceButton = $("button#guessTheSentenceButton");
        const maxCols = 16;
        var gameSessionData = [];

        function wrapSentence(sentence) {
            const words = sentence.trim().split(/\s+/);
            const rows = [];
            let currentRow = "";

            words.forEach(word => {
                // If this is the first word in the row
                if (currentRow === "") {
                    currentRow = word;
                    return;
                }
                // Check if adding this word would exceed maxCols
                if ((currentRow + " " + word).length <= maxCols) {
                    currentRow += " " + word;
                } else {
                    rows.push(currentRow);
                    currentRow = word;
                }
            });

            if (currentRow !== "") {
                rows.push(currentRow);
            }

            return rows;
        }

        function grabGameSessionData() {
            $.ajax({
                type: "GET",
                url: "{{ route('game.game-screen-data', ['urlEncodedNamaSesi' => session('current_game.url')]) }}",
                success: function(result) {
                    if (result) {
                        if (result.game_over) {
                            window.location.href = "{{ route('game.game-over-screen', ['urlEncodedNamaSesi' => session('current_game.url')]) }}";
                        }

                        gameSessionData = result.game_session_data;
                        const leaderboard = result.leaderboard;
                        const roundData = gameSessionData.roundsData;
                        const playersTurn = result.players_turn;

                        $("title").html(`Round ${gameSessionData.currentRound}, Turn ${result.current_turn} - {{ config('app.name', 'Laravel') }}`);
                        $("h2#header-screen-title").html(`
                            Round ${gameSessionData.currentRound}, Turn ${result.current_turn}
                            <i class="bi bi-arrow-clockwise ps-2" style="cursor: pointer;" id="reloadGameScreen"></i>
                        `);

                        displayLeaderboard(leaderboard);
                        disableSomeLetterGuesses(roundData);
                        displayBaseWords(roundData);
                        displayPlayersTurn(playersTurn);
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function disableSomeLetterGuesses(roundData) {
            $("button.guess-the-letter-btn").removeClass('btn-secondary').addClass('btn-outline-dark').prop('disabled',false);
            const latestRoundKey = Object.keys(roundData).at(-1);
            const latestRound = roundData[latestRoundKey];
            $("button.guess-the-letter-btn").each(function() {
                if ((latestRound.guessedLetters).includes($(this).val())) {
                    $(this).prop('disabled',true).removeClass('btn-outline-dark').addClass('btn-secondary');
                }
            });
        }

        function displayLeaderboard(leaderboard) {
            const leaderboardArea = $("div#leaderboardArea");
            leaderboardArea.html('');

            leaderboard.forEach((player, index) => {
                var bgGradient = null;

                if (index == 0) {
                    bgGradient = 'linear-gradient(to right, rgba(255,204,0,1), rgba(255,230,129,1), rgba(0,0,0,0))';
                } else if (index == 1) {
                    bgGradient = 'linear-gradient(to right, rgba(204,204,204,1), rgba(231,231,231,1), rgba(0,0,0,0))';
                } else if (index == 2) {
                    bgGradient = 'linear-gradient(to right, rgba(223,170,135,1), rgba(233,198,175,1), rgba(0,0,0,0))';
                }

                leaderboardArea.append($("<div>")
                    .addClass('w-100 px-2 my-1 d-flex flex-row align-items-center justify-content-start')
                    .css('min-height','3rem')
                    .css('background-image',`${bgGradient}`)
                    .append($("<div>")
                        .css('min-width','2rem')
                        .html(`<p class="m-0 text-end fw-bold pe-2" style="font-size:1.5rem; line-height: 0;">
                            ${index + 1}.
                        </p>`)
                    )
                    .append($("<div>")
                        .addClass('w-100 d-flex flex-row justify-content-between')
                        .html(`
                            <p class="m-0 fw-bold" style="line-height: 1rem; font-size: 0.875rem;" title="${player.name}">
                                ${(player.name.length > 12) ? player.name.substring(0,11)+'...' : player.name}
                            </p>
                            <p class="m-0 fw-bold" style="line-height: 1.25rem; ">${player.score}</p>
                        `)
                    )
                );
            });
        }

        function displayPlayersTurn(playersTurn) {
            const playersTurnArea = $("div#player-turns-indicator-area");
            playersTurnArea.html('');

            playersTurn.forEach(element => {
                playersTurnArea.append($("<div>")
                    .addClass('p-0 px-2 d-flex flex-column align-items-center justify-content-center')
                    .css('height', '6rem')
                    .css('width', '4.5rem')
                    .css('max-height', '6rem')
                    .css('max-width', '4.5rem')
                    .css('min-height', '6rem')
                    .css('min-width', '4.5rem')
                    .append($("<div>")
                        .addClass(`image-profile-element-container w-100${element.currentlyPlay ? ' border border-3 border-info bg-info-subtle' : ''}`)
                        .attr('data-bs-toggle','tooltip')
                        .attr('data-bs-title',element.name)
                        .append($("<img>")
                            .addClass("profile-pic-container")
                            .attr('id',`profilePic_${element.id}`)
                            .attr('src',element.profilePicPath)
                        )
                    )
                    .append($("<p>")
                        .addClass('fw-bold text-center m-0')
                        .css('font-size','0.75rem')
                        .text(element.score)
                    )
                );
            });

            $('[data-bs-toggle="tooltip"]').each(function(index) {
                new bootstrap.Tooltip($(this)[0]);
            });
        }

        function displayBaseWords(roundData) {
            wordsDisplayArea.html('');
            const latestRoundKey = Object.keys(roundData).at(-1);
            const latestRound = roundData[latestRoundKey];
            const words = latestRound.currentWord; // Example "DUA TIGA BUAH KELAPA"
            const newWords = wrapSentence(words);

            for (const word of newWords) {
                let row = $("<div class='word-row'></div>");
                for(let i=0; i<word.length; i++){
                    let cell = $("<div class='letter'></div>");
                    if (word[i] != ' ') {
                        cell
                            .addClass("filled")
                            .attr("data-letter", word[i]);
                    }
                    row.append(cell);
                }
                wordsDisplayArea.append(row);
            }

            for (guessedLetter of latestRound.guessedLetters) {
                if (words.includes(guessedLetter)) {
                    $(`div.word-row div.letter[data-letter="${guessedLetter}"]`).addClass('show').html(`<p>${guessedLetter}</p>`);
                }
            }
        }

        $("h2#header-screen-title").on('click', 'i#reloadGameScreen', function() {
            grabGameSessionData();
        })

        $("button.guess-the-letter-btn").on('click', function(e) {
            const thisButton = $(this);
            const letter = thisButton.val();
            if (letter) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('game.guess-the-letter', ['urlEncodedNamaSesi' => session('current_game.url')]) }}",
                    data: {
                        "_token" : "{{ csrf_token() }}",
                        letter: letter,
                    },
                    success: function(result) {
                        if (result) {
                            const game_state = result.state;
                            if (game_state == 'next_round') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Kalimat Berhasil Ditebak',
                                    theme: 'bootstrap-5',
                                    customClass: {
                                        confirmButton: 'btn btn-primary',
                                    },
                                    html: `
                                        <div class="d-flex w-100 justify-content-center m-0 p-0">
                                            <p class="m-0 my-2 fw-bold fs-4">${result.sentence}</p>
                                        </div>
                                    `
                                });
                            }
                        }
                        grabGameSessionData();
                    },
                    error: function(err) {
                        console.log(err);
                        grabGameSessionData();
                    }
                });
            }
        });

        guessTheSentenceInput.on('keyup', function(e) {
            if (e.originalEvent.keyCode == '13' || e.originalEvent.key == 'Enter') {
                guessTheSentenceButton.trigger('click');
            }
        });

        guessTheSentenceButton.on('click', function(e) {
            const sentence = guessTheSentenceInput.val();
            if (sentence) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('game.guess-the-sentence', ['urlEncodedNamaSesi' => session('current_game.url')]) }}",
                    data: {
                        "_token" : "{{ csrf_token() }}",
                        sentence: sentence,
                    },
                    success: function(result) {
                        if (result) {
                            const game_state = result.state;
                            if (game_state == 'next_round') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Kalimat Berhasil Ditebak',
                                    theme: 'bootstrap-5',
                                    customClass: {
                                        confirmButton: 'btn btn-primary',
                                    },
                                    html: `
                                        <div class="d-flex w-100 justify-content-center m-0 p-0">
                                            <p class="m-0 my-2 fw-bold fs-4">${result.sentence}</p>
                                        </div>
                                    `
                                });
                            }
                        }
                        grabGameSessionData();
                    },
                    error: function(err) {
                        console.log(err);
                        grabGameSessionData();
                    }
                });
            }
            guessTheSentenceInput.val('');
        });

        grabGameSessionData();
    });
</script>
@endpush