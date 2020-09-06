<?php

namespace App\Console\Commands;

use App\Http\Controllers\OrderController;
use App\Ut_order;
use App\Ut_order_queue;
use App\Ut_order_status_history;
use App\Ut_setting;
use App\Ut_taxi;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class DemoCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        date_default_timezone_set('Asia/Baku');

        //1 deq erzinde coordinate gonderemeyen teksilerin live 0 olsun
        $taxi_coordinates = Ut_taxi::where(['live' => 1, 'status' => true, 'delete' => 0])->get();
        foreach ($taxi_coordinates as $taxi_coordinate) {
            if ((time() - strtotime($taxi_coordinate->last_coordinate_date)) * 1000 > 60000) {
                DB::table('ut_taxi')->where('id', $taxi_coordinate->id)
                    ->update([
                        'live' => false,
                    ]);
            }
        }

        //get reached time from setting

        $reached_time = Ut_setting::where('setting_key', 'reached_time')->value('setting_value');

        $to = date('Y-m-d H:i:s', strtotime("+" . $reached_time . " minutes"));

        //eger taxinin teli sonuludurse onda sifaris havada qalmasin deye statusu yeniden 0 olub crona dusmelidir
        $to_find_taxi = Carbon::now()->format('Y-m-d H:i:s');

        $order_find_taxies = Ut_order::with('orderDetailName')->where(['status' => 1, 'delete' => 0, 'taxi' => 0, 'test' => 0])->get();
        foreach ($order_find_taxies as $of) {
            $order_find_taxi_history = Ut_order_status_history::where('order', $of->id)->get()->last();
            $duration = 120;
            $dateinsec = strtotime($order_find_taxi_history->date);
            $newdate = $dateinsec + $duration;
            $after_30_date = date('Y-m-d H:i:s', $newdate);

            if (($after_30_date < $to_find_taxi) && ($order_find_taxi_history->status == 1 || $order_find_taxi_history->status == 50)) {
                Ut_order::where('id', $order_find_taxi_history->order)->update([
                    'status' => 0,
                    'taxi' => 0,
                    'auto_search' => 1,
                    'refused_taxies' => $of->refused_taxies ? $of->refused_taxies . ',' . $order_find_taxi_history->taxi : $order_find_taxi_history->taxi,
                ]);

                Ut_taxi::where('id', $order_find_taxi_history->taxi)->update([
                    'action' => 0,
                ]);

                Ut_order_status_history::insertGetId([
                    'order' => $of->id,
                    'taxi' => $of->taxi,
                    'user_id' => 13,
                    'status' => 111,
                    'reason' => '1 dəq keçdiyi üçün yenidən Crona saldı',
                    'date' => date('Y-m-d H:i:s'),
                    'location' => $of->orderDetailName->locationName()
                ]);

            }
        }

        $orders = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName'])->join('ut_order_detail as utd', 'ut_order.id', 'utd.order_id')
            ->whereIn('ut_order.status', [0, 600, 700])
            ->where('utd.order_date', '<', $to)
            ->where('ut_order.auto_search', true)
            ->where('ut_order.taxi', false)
            ->where('ut_order.delete', false)
            ->select('ut_order.*', 'utd.order_date')
            ->get();

        if (count($orders)) {

            foreach ($orders as $order) {
                $checkStatusSound = true;
                $taxi_future_id = false;

                if ($order->status == 600 || $order->status == 700) {
                    $checkStatusSound = false;
                    $taxi_future_id = Ut_order_queue::where(['order_id' => $order->id, 'o' => true])->value('taxi_id');
                }

                DB::table('ut_order')->where('id', $order->id)->update(['status' => false]);

                $orderStatusBannedTaxi = explode(',', $order->refused_taxies);

                $location = json_decode($order->orderDetailName->route, true)[0];

                $result = OrderController::postOrderFindTaxi($checkStatusSound,$order, $location['lat'], $location['lng'], $orderStatusBannedTaxi, $taxi_future_id);
            }
        }

    }

}
