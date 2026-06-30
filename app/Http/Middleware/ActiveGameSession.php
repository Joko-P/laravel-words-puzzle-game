<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveGameSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentGame = session('current_game');

        if (empty($currentGame)) {
            return redirect()->route('main-menu')->withErrors(['error' => 'Sesi game tidak ditemukan atau sudah berakhir.']);
        }

        $gameSession = session('gameSessions')[$currentGame['name']] ?? [];
        $urlEncodedNamaSesi = $gameSession['urlEncodedNamaSesi'] ?? null;
        $urlParam = $request->route()->parameter('urlEncodedNamaSesi');
        if (empty($gameSession) || empty($currentGame) || $urlEncodedNamaSesi !== $urlParam) {
            session()->forget('current_game');
            return redirect()->route('main-menu')->withErrors(['error' => 'Sesi game tidak ditemukan atau sudah berakhir.']);
        }
        
        return $next($request);
    }
}
