<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request  Request yang masuk
     * @param  \Closure  $next  Closure untuk melanjutkan ke request berikutnya
     * @param  string  $role  Parameter role (misal 'admin', 'supplier')
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            abort(403, 'Anda harus login terlebih dahulu.');
        }

        // Cek apakah role user sesuai
        if (Auth::user()->role !== $role) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Jika semua sesuai, lanjutkan request
        return $next($request);
    }
}
