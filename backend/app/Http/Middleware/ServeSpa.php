<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServeSpa
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*') || $request->is('up')) {
            return $next($request);
        }

        $spaIndex = public_path('spa/index.html');

        if (file_exists($spaIndex)) {
            return response()->file($spaIndex);
        }

        return $next($request);
    }
}
