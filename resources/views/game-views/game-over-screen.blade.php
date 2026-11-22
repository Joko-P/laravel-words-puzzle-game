@extends('layouts.main-layout')
@section('page-title')
    Game Over
@endsection
@push('css')
<style>
    .summary-area{
        width: min(100%, 60rem);
    }

    .summary-word-image{
        max-width: 12rem;
        max-height: 12rem;
        object-fit: contain;
    }

    .summary-word-desc a{
        color: var(--bs-link-color);
    }
</style>
@endpush
@section('content')
<div class="d-flex flex-row justify-content-center align-items-end">
    @if (isset($leaderBoard[1]))
    <div class="mx-2" style="width: 20vw; min-width: 20vw; max-width: 20vw;">
        <p class="fw-bold text-center mb-1" style="line-height: 2.5rem; font-size: 2.5rem;">{{ $leaderBoard[1]['score'] ?? '-' }}</p>
        <div class="p-0">
            <div class="image-profile-element-container my-2" style="max-width: 96px;">
                <img class="profile-pic-container" src="{{ $leaderBoard[1]['profilePicPath'] ?? '-' }}">
            </div>
        </div>
        <p class="fw-bold text-center mb-1" style="line-height: 1rem; font-size: 0.875rem;">{{ $leaderBoard[1]['name'] ?? '-' }}</p>
        <div class="w-100 d-flex align-items-end justify-content-center" style="border-bottom: 1rem solid rgba(204,204,204,1); background: linear-gradient(to top, rgba(0,0,0,0), rgba(231,231,231,1)); height: 9rem; min-height: 9rem; max-height: 9rem;">
            <p class="fw-bold mb-1 fs-4">2</p>
        </div>
    </div>
    @endif
    @if (isset($leaderBoard[0]))
    <div class="mx-2" style="width: 20vw; min-width: 20vw; max-width: 20vw;">
        <p class="fw-bold text-center mb-1" style="line-height: 2.5rem; font-size: 3rem;">{{ $leaderBoard[0]['score'] ?? '-' }}</p>
        <div class="p-0">
            <div class="image-profile-element-container my-2" style="max-width: 128px;">
                <img class="profile-pic-container" src="{{ $leaderBoard[0]['profilePicPath'] ?? '-' }}">
            </div>
        </div>
        <p class="fw-bold text-center mb-1" style="line-height: 1rem; font-size: 0.875rem;">{{ $leaderBoard[0]['name'] ?? '-' }}</p>
        <div class="w-100 d-flex align-items-end justify-content-center" style="border-bottom: 1rem solid rgba(255,204,0,1); background: linear-gradient(to top, rgba(0,0,0,0), rgba(255,230,129,1)); height: 12rem; min-height: 12rem; max-height: 12rem;">
            <p class="fw-bold mb-1 fs-4">1</p>
        </div>
    </div>
    @endif
    @if (isset($leaderBoard[2]))
    <div class="mx-2" style="width: 20vw; min-width: 20vw; max-width: 20vw;">
        <p class="fw-bold text-center mb-1" style="line-height: 2.5rem; font-size: 2rem;">{{ $leaderBoard[2]['score'] ?? '-' }}</p>
        <div class="p-0">
            <div class="image-profile-element-container my-2" style="max-width: 64px;">
                <img class="profile-pic-container" src="{{ $leaderBoard[2]['profilePicPath'] ?? '-' }}">
            </div>
        </div>
        <p class="fw-bold text-center mb-1" style="line-height: 1rem; font-size: 0.875rem;">{{ $leaderBoard[2]['name'] ?? '-' }}</p>
        <div class="w-100 d-flex align-items-end justify-content-center" style="border-bottom: 1rem solid rgba(223,170,135,1); background: linear-gradient(to top, rgba(0,0,0,0), rgba(233,198,175,1)); height: 6rem; min-height: 6rem; max-height: 6rem;">
            <p class="fw-bold mb-1 fs-4">3</p>
        </div>
    </div>
    @endif
</div>
<hr class="content-divider">
{{-- Lebar 25vw seperti desain awal, tapi minimal 20rem supaya tidak terlalu sempit saat di-zoom --}}
<div class="d-flex flex-column justify-content-start align-items-center overflow-y-auto" style="width: min(100%, max(25vw, 20rem)); height: 16rem; max-height: 16rem;">
    @for ($i = 3; $i < count($leaderBoard); $i++)
    <div class="d-flex flex-row align-items-center justify-content-start p-3 my-1 border border-2 border-dark bg-white w-100 rounded-3">
        <p class="m-0 fw-bold fs-5 pe-2">{{ $i + 1 }}.</p>
        <div class="p-0">
            <div class="image-profile-element-container me-2" style="max-width: 48px; min-width: 48px;">
                <img class="profile-pic-container" src="{{ $leaderBoard[$i]['profilePicPath'] ?? '-' }}">
            </div>
        </div>
        <p class="m-0 fw-bold fs-6">{{ $leaderBoard[$i]['name'] ?? '-' }}</p>
        <p class="m-0 fw-bold fs-6 text-end flex-grow-1 ps-4">{{ $leaderBoard[$i]['score'] ?? '-' }}</p>
    </div>
    @endfor
</div>
@if (!empty($summary))
<hr class="content-divider">
{{-- Ringkasan per ronde: kalimat, siapa yang menebak apa, dan desc / gambar dari pack kalau ada --}}
<div class="summary-area px-3 mb-3">
    <h4 class="fw-bold text-center mb-3">Ringkasan Permainan</h4>
    <div class="accordion" id="summaryAccordion">
        @foreach ($summary as $index => $round)
        <div class="accordion-item border-dark-subtle">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#summaryRound{{ $index }}" aria-expanded="false" aria-controls="summaryRound{{ $index }}">
                    <span class="fw-bold me-2 text-nowrap">Ronde {{ $round['number'] }}</span>
                    <span class="badge text-bg-dark me-2 fs-6">{{ $round['word'] }}</span>
                    <span class="small">
                        @if ($round['winner'])
                            Ditebak oleh <b>{{ $round['winner'] }}</b>
                        @else
                            Tidak terjawab
                        @endif
                    </span>
                </button>
            </h2>
            <div id="summaryRound{{ $index }}" class="accordion-collapse collapse">
                <div class="accordion-body">
                    @if ($round['imageUrl'] || $round['descHtml'])
                    <div class="d-flex flex-column flex-md-row gap-3 mb-3 align-items-center align-items-md-start">
                        @if ($round['imageUrl'])
                        <img src="{{ $round['imageUrl'] }}" alt="{{ $round['word'] }}" class="summary-word-image rounded border">
                        @endif
                        @if ($round['descHtml'])
                        {{-- descHtml sudah dibersihkan oleh WordsList::formatDescription(), hanya tag sederhana & link https --}}
                        <div class="summary-word-desc">{!! $round['descHtml'] !!}</div>
                        @endif
                    </div>
                    @endif

                    @if ($round['hasTurnLog'] && count($round['events']))
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4.5rem;">Giliran</th>
                                    <th>Pemain</th>
                                    <th>Aksi</th>
                                    <th class="text-end" style="width: 4.5rem;">Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($round['events'] as $event)
                                <tr>
                                    <td class="text-center">{{ $event['turn'] }}</td>
                                    <td class="fw-bold">{{ $event['player'] }}</td>
                                    <td>
                                        @switch($event['type'])
                                            @case('letter')
                                                Tebak huruf <b>{{ $event['value'] }}</b>
                                                {!! $event['correct'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' !!}
                                                @break
                                            @case('sentence')
                                                Tebak kalimat <b>"{{ $event['value'] }}"</b>
                                                {!! $event['correct'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' !!}
                                                @break
                                            @case('hint')
                                                <i class="bi bi-lightbulb-fill text-warning"></i> Buka hint: <i>"{{ $event['value'] }}"</i>
                                                @break
                                            @case('timeout')
                                                <i class="bi bi-hourglass-bottom text-danger"></i> Waktu habis
                                                @break
                                            @default
                                                {{ $event['type'] }}
                                        @endswitch
                                    </td>
                                    <td class="text-end fw-bold {{ $event['points'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $event['points'] > 0 ? '+' : '' }}{{ $event['points'] }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @elseif (!$round['hasTurnLog'])
                    <p class="small text-body-secondary m-0">
                        Detail per giliran tidak tersedia (game ini dimulai sebelum fitur ringkasan ada).
                        @if (count($round['guessedLetters']))
                            Huruf yang ditebak: <b>{{ implode(', ', $round['guessedLetters']) }}</b>
                        @endif
                    </p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
@push('modals')

@endpush