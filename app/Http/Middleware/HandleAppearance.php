<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 既定は白 × オレンジのライトテーマ (resources/js/composables/useAppearance.ts と合わせる)
        View::share('appearance', $request->cookie('appearance') ?? 'light');

        return $next($request);
    }
}
