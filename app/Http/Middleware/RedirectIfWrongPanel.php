<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfWrongPanel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('*.auth.logout')) {
            return $next($request);
        }

        $user = auth()->user();

        // If not logged in, let standard authentication handle it
        if (! $user) {
            return $next($request);
        }

        $panelId = Filament::getCurrentPanel()->getId();

        // Admin Panel Logic
        if ($panelId === 'admin') {
            if (! $user->hasRole('admin')) {
                if ($user->hasRole('dosen')) {
                    return redirect()->to('/dosen');
                }

                if ($user->hasRole('mahasiswa')) {
                    return redirect()->to('/mahasiswa');
                }

                Filament::auth()->logout();

                return redirect()->to('/admin/login');
            }
        }

        // Dosen Panel Logic
        if ($panelId === 'dosen') {
            if (! $user->hasRole('dosen')) {
                if ($user->hasRole('admin')) {
                    return redirect()->to('/admin');
                }

                if ($user->hasRole('mahasiswa')) {
                    return redirect()->to('/mahasiswa');
                }

                Filament::auth()->logout();

                return redirect()->to('/dosen/login');
            }
        }

        if ($panelId === 'mahasiswa') {
            if (! $user->hasRole('mahasiswa')) {

                if ($user->hasRole('admin')) {
                    return redirect()->to('/admin');
                }

                if ($user->hasRole('dosen')) {
                    return redirect()->to('/dosen');
                }

                Filament::auth()->logout();

                return redirect()->to('/mahasiswa/login');
            }
        }

        return $next($request);
    }
}
