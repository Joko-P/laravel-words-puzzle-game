@extends('layouts.main-layout')
@section('page-title')
    Main Menu
@endsection
@push('css')
<style>
    .word-row{
        display:flex;
        justify-content:center;
        margin-bottom:0.25rem;
        margin-top:0.25rem;
    }

    .letter{
        min-width: 3rem;
        width:100%;
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
</style>
@endpush
@section('content')
<div id="game-icon-area">

</div>
<div class="row m-0 mt-5 d-flex flex-row justify-content-center align-items-center w-100">
    <div class="col-4">
        <button type="button" class="btn btn-lg btn-outline-dark w-100 fw-bold" id="newGameBtn">Mulai Game Baru</button>
    </div>
    <div class="col-4">
        <button type="button" class="btn btn-lg btn-outline-dark w-100 fw-bold" id="resumeGameBtn">Lanjutkan Game Sebelumnya</button>
    </div>
</div>
@endsection
@push('modals')
<div class="modal fade" id="startNewGameModal" tabindex="-1" aria-labelledby="startNewGameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered model-dialog-scrollable modal-xl">
        <div class="modal-content">
            <form action="{{ route('start-new-game') }}" method="POST">
                @csrf
                <div class="modal-header justify-content-center">
                    <h1 class="modal-title fs-3" id="startNewGameModalLabel">Mulai Game Baru</h1>
                </div>
                <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                    <div class="input-group my-3 w-50">
                        <span class="input-group-text border-dark-subtle">Nama Sesi Game</span>
                        <input type="text" class="form-control border-dark-subtle" id="namaSesiInput" name="namaSesi" placeholder="Nama Sesi" required>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-lg btn-dark">Mulai!</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="continueGameModal" tabindex="-1" aria-labelledby="continueGameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered model-dialog-scrollable modal-xl">
        <div class="modal-content">
            <form action="{{ route('continue-game') }}" method="POST">
                @csrf
                <div class="modal-header justify-content-center">
                    <h1 class="modal-title fs-3" id="continueGameModalLabel">Lanjutkan Game</h1>
                </div>
                <div class="modal-body d-flex flex-column align-items-center justify-content-center">
                    <div class="input-group my-3 w-50">
                        <label class="input-group-text" for="nameSesiSelect">Nama Sesi Game</label>
                        <select class="form-select border-dark-subtle" name="namaSesi" id="nameSesiSelect" required>
                            <option selected value="" disabled>Pilih Sesi Game</option>
                            @foreach ($availableGameSessions as $sessionName)
                                <option value="{{ $sessionName }}">{{ $sessionName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-lg btn-dark">Lanjutkan!</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@push('js')
<script>
    $(document).ready(function() {
        const startNewButton = $("button#newGameBtn");
        const resumeButton = $("button#resumeGameBtn");
        const startNewGameModal = $("div#startNewGameModal");
        const continueGameModal = $("div#continueGameModal");
        const words = [
            { text: "GAME", start: 0 },
            { text: "TEBAK", start: 1 },
            { text: "KALIMAT", start: 0 }
        ];
        const maxCols = 7;

        for (const word of words)
        {
            let row = $("<div class='word-row'></div>");
            for(let i=0; i<maxCols; i++){
                let cell = $("<div class='letter w-100'></div>");
                if(i >= word.start && i < word.start + word.text.length){
                    cell
                        .addClass("filled")
                        .attr("data-letter", word.text[i-word.start]);

                }
                row.append(cell);
            }
            $("#game-icon-area").append(row);
        }
        
        function animateGameIcon()
        {
            const letters = [...new Set(
                $(".filled").map(function(){
                    return $(this).data("letter");
                }).get()
            )].sort(()=>Math.random()-0.5);

            letters.forEach((letter, index) => {
                setTimeout(() => {
                    $(`.filled[data-letter="${letter}"]`).each(function(){
                        $(this)
                            .append($("<p>")
                                .text(letter)
                                .css("opacity",0)
                                .animate({
                                    opacity:1
                                },250)
                            );
                    });
                }, index * 500);
            });

            const totalTime = letters.length * 500;
            
            setTimeout(() => {
                $(".filled p").animate({
                    opacity:0
                }, 500, function() {
                    $(this).remove();
                });
            }, totalTime + 1000);

            setTimeout(() => {
                animateGameIcon();
            }, totalTime + 2000);
        }

        startNewButton.on('click', function() {
            startNewGameModal.modal('show');
        });
        
        startNewGameModal.on('shown.bs.modal', function () {
            $(this).find('input#namaSesiInput').first().focus();
        });
        
        resumeButton.on('click', function() {
            continueGameModal.modal('show');
        });

        animateGameIcon();
    });
</script>
@endpush