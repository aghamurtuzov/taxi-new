<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Ut_driver_setting extends Model
{
    protected $table = 'ut_driver_setting';
    protected $guarded = [];


    public function navigatorArray()
    {
        $navigator = explode(',', $this->navigator);

        return $navigator;
    }

    public function taxiName()
    {
        return $this->belongsTo('App\Ut_taxi', 'taxi_id');
    }

}
