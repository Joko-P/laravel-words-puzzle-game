@extends('layouts.main-layout')
@section('page-title')
    Game Over
@endsection
@push('css')
<style>
    
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
<hr style="width: calc(100vw - 6rem);">
<div class="d-flex flex-column justify-content-start align-items-center overflow-y-auto" style="max-width: 25vw; width: 25vw; height: 16rem; max-height: 16rem;">
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
@endsection
@push('modals')

@endpush