<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ut_order extends Model
{
    protected $table = 'ut_order';

    public $timestamps = false;

    protected $guarded = [];

    //get order detail en birincini
    public function orderDetailNameFirst()
    {
        return $this->hasOne('App\Ut_order_detail', 'order_id');
    }

    //get order detail en sonuncunu
    public function orderDetailName()
    {
        return $this->hasOne('App\Ut_order_detail', 'order_id')->orderBy('id', 'desc');
    }

    //get order details cunki bir cox detail ola biler eger operator ve ya dispetcer deyisiklik edibse
    public function orderDetailNames()
    {
        return $this->hasMany('App\Ut_order_detail', 'order_id');
    }

    //get customer
    public function customerName()
    {
        return $this->belongsTo('App\Ut_customer', 'customer');
    }

    public function cancelReason()
    {
        $arr = [];
        return Ut_cancel_reason::where('delete', false)->get();
//        foreach($reasons as $reason){
//            array_push($arr,$reason->)
//        }
    }

    //get address
    public function addressName($id)
    {
        return Ut_address::where('status', true)->where('delete', 0)->where('street', $id)->get();
    }

    //get customer full name
    public function fullCustomerNameWithNumber()
    {
        return $this->customerName->firstname . ' ' . $this->customerName->lastname . '<br> (' . $this->customerName->phone . ')';
    }

    public function customerNumber()
    {
        return $this->customerName->phone;
    }

    //get taxi
    public function taxiName()
    {
        return $this->belongsTo('App\Ut_taxi', 'taxi');
    }

    //get taxi full name
    public function fullTaxiNameWithCodeAndNumber()
    {
        if ($this->taxiName) {
            return $this->taxiName->code . '-' . $this->taxiName->firstname . ' ' . $this->taxiName->lastname . ' (' . $this->taxiName->phone . ')';
        } else {
            return 'Taksi təyin olunmayıb';
        }
    }

    //get option
    public function optionName()
    {
        $option = explode(',', $this->orderDetailName->option);

        return $option;
    }

    public function routeNameEdit()
    {
        $decode = json_decode($this->orderDetailName->route, true);
        return $decode;
    }

    public function routeName()
    {
        $decode = json_decode($this->orderDetailName->route);

        array_shift($decode);

        return $decode;
    }

    public function statusChanges()
    {

        return $this->hasMany('App\Ut_order_status_history', 'order');

    }

    public function statusChangesLast()
    {

        return $this->hasOne('App\Ut_order_status_history', 'order')->orderBy('id', 'DESC');

    }

    //Eger taxi legv etse
    public function statusCancelRequestNames()
    {

        return $this->hasMany('App\Ut_order_cancel_request', 'order');

    }


    public function scopeCustomer_phone($query, $name)
    {
        if ($name != null) {
            $customer = Ut_customer::where('phone', 'LIKE', '%' . $name . '%')->pluck('id');
            return $query->whereIn('customer', $customer);
        } else {
            return $query;
        }
    }

    public function scopeTaxi_id($query, $name)
    {
        if ($name != null) {
            $taxi = Ut_taxi::where('code', $name)->pluck('id');
            return $query->whereIn('taxi', $taxi);
        } else {
            return $query;
        }
    }

    public function scopeTariff($query, $name)
    {
        if ($name != null) {
            $tariff = Ut_order_detail::where('tariff', $name)->pluck('order_id');
            return $query->whereIn('id', $tariff);
        } else {
            return $query;
        }
//        if ($name != null) {
//            return $query->whereIn('id', $this->orderDetailNames()->where('tariff', 'LIKE', '%' . $name . '%')->pluck('order_id'));
//        } else {
//            return $query;
//        }

    }

    public function scopeStatus($query, $status)
    {
        if ($status != null) {
            return $query->where('status', $status);
        } else {
            return $query;
        }
    }


    public function scopeDate_from($query, $date)
    {
        if ($date != null) {
            return $query->whereDate('created_at', '>=', $date);
        } else {
            return $query->where(function ($q) {
                $q->where(function ($q_) {
                    $q_->whereDate('created_at', date('Y-m-d'));
                    $q_->where('status', 4);
                });
                $q->orWhere(function ($q__) {
                    $q__->whereNotIn('status', [4, 35]);
                });
            });
        }
    }

    public function scopeDate_from_submit($query, $date_from_submit)
    {
        return true;
    }

    public function scopeDate_to_submit($query, $date_to_submit)
    {
        return true;
    }

    public function scopeDate_to($query, $name)
    {
        if ($name != null) {
            return $query->where('created_at', '<=', $name);
        } else {
            return $query;
        }
    }

    public function scopePrice_min($query, $name)
    {
        if ($name != null) {
            $price = Ut_order_detail::where('price', '>=', $name)->select('order_id')->get();
            return $query->whereIn('id', $price);
        } else {
            return $query;
        }
    }

    public function scopePrice_max($query, $name)
    {
        if ($name != null) {
            $price = Ut_order_detail::where('price', '<=', $name)->select('order_id')->get();
            return $query->whereIn('id', $price);
        } else {
            return $query;
        }
    }


    public function scopeOrder_order_type($query, $name)
    {
        return true;
    }

    public function scopeOrder_value_from($query, $name)
    {
        if ($name != null) {
            $price = Ut_order_detail::where('order_value', '<=', $name)->select('order_id')->get();
            return $query->whereIn('id', $price);
        } else {
            return $query;
        }
    }

    public function scopeOrder_value_to($query, $name)
    {
        if ($name != null) {
            $price = Ut_order_detail::where('order_value', '<=', $name)->select('order_id')->get();
            return $query->whereIn('id', $price);
        } else {
            return $query;
        }
    }

    public function scopePayment_method($query, $name)
    {
        return true;
    }

    public function scopeAddress($query, $name)
    {
        return true;
    }

    public function scopeOrder_id($query, $id)
    {
        if ($id != null) {
            return $query->where('id', 'LIKE', '%' . $id . '%');
        } else {
            return $query;
        }
    }


    //get status
    public function statusName()
    {

        switch ($this->status) {
            case 0:
                $result = 'searching';
                break;
            case 1:
                $result = 'Taksi tapdı';
                break;
            case 2:
                $result = 'Qəbul etdi';
                break;
            case 3:
                $result = 'Çatdı';
                break;
            case 8:
                $result = 'Götürdü';
                break;
            case 4:
                $result = 'Tamamlanmış';
                break;
            case 35:
                $result = 'Ləğv';
                break;
            case 600:
                $result = 'Açıq';
                break;
            case 700:
                $result = 'Ön';
                break;
            default:
                $result = 'searching';
        }

        return $result;

    }


    public function colorName()
    {

        switch ($this->status) {
            case 0:
                $result = '#ff0000';
                break;
            case 1:
                $result = 'primary';
                break;
            case 2:
                $result = 'purple';
                break;
            case 3:
                $result = 'reached';
                break;
            case 8:
                $result = 'secondary';
                break;
            case 4:
                $result = 'success';
                break;
            case 35:
                $result = 'danger';
                break;
            case 600:
                $result = 'black';
                break;
            default:
                $result = '#006600';
        }

        return $result;

    }

    public function bgName()
    {

        switch ($this->status) {
            case 0:
                $result = '';
                break;
//            case 1:
//                $result = 'primary';
//                break;
            case 2:
                $result = '#06f94626';
                break;
            case 3:
                $result = '#06f94626';
                break;
            case 8:
                $result = '#06f94626';
                break;
            case 4:
                $result = '#4caf5059';
                break;
            case 35:
                $result = '#f443361f';
                break;
            case 600:
                $result = '#fff9b1';
                break;
            default:
                $result = '#fff';
        }

        return $result;

    }

    //get status
    public function sourceName()
    {

        switch ($this->source) {
            case 0:
                $result = 'Applikasiya';
                break;
            case 1:
                $result = 'Telefon';
                break;
            default:
                $result = '';
        }

        return $result;

    }


}
