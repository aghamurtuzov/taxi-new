<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Ut_taxi_device_backup extends Model
{
    protected $table = 'ut_taxi_device_backup';
    protected $guarded = [];
}
