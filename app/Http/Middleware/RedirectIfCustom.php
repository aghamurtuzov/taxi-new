<?php

namespace App\Http\Middleware;

use Closure;

class RedirectIfCustom
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (session()->has('id')) {
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
