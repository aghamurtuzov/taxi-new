<?php

namespace App\Http\Middleware;

use Closure;

class Roles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (session()->get('group') == "1"){
            return session()->put('administrator',"1");
        }
        if (session()->get('group') == "2"){
            return session()->put('operator',"2");
        }
        if (session()->get('group') == "3"){
            return session()->put('dispatcher',"3");
        }
        if (session()->get('group') == "4"){
            return session()->put('accounting',"4");
        }
        if (session()->get('group') == "5"){
            return session()->put('cashier',"5");
        }
        if (session()->get('group') == "6"){
            return session()->put('taxipark',"6");
        }
        return $next($request);
    }
}
