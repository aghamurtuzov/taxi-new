<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Ut_danger_objects extends Model
{
    protected $table = 'ut_danger_objects';
    protected $guarded = [];

    public function scopeColumn_name($query, $column_name, $order_type = 'asc')
    {
        if ($column_name != null) {
            return $query->orderBy($column_name, $order_type);
        } else {
            return $query;
        }
    }

    public function scopeName($query, $name)
    {
        if ($name != null) {
            return $query->where('name', 'LIKE', '%' . $name . '%');
        } else {
            return $query;
        }

    }


    public function scopeStatus($query, $status)
    {
        if ($status != null) {
            return $query->where('status', $status);
        } else {
            return $query;
        }
    }
}
