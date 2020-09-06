<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BladeRoleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::if('administrator',function(){
            if(session()->get('administrator')){
                return true;
            }
            return false;
        });
        Blade::if('operator',function(){
            if(session()->get('operator')){
                return true;
            }
            return false;
        });
        Blade::if('dispatcher',function(){
            if(session()->get('dispatcher')){
                return true;
            }
            return false;
        });
        Blade::if('accounting',function(){
            if(session()->get('accounting')){
                return true;
            }
            return false;
        });
        Blade::if('cashier',function(){
            if(session()->get('cashier')){
                return true;
            }
            return false;
        });
        Blade::if('taxipark',function(){
            if(session()->get('taxipark')){
                return true;
            }
            return false;
        });
    }
}
