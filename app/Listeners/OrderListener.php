<?php

namespace App\Listeners;

use App\Events\NewOrderFuture;
use App\Http\Controllers\Api\FcmController;
use App\Http\Controllers\Api\StaticController;
use App\Ut_order;
use App\Ut_order_queue;
use App\Ut_order_status_history;
use App\Ut_order_taxi_temp;
use App\Ut_taxi;
use App\Ut_taxi_categories;

class OrderListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param NewOrderFuture $event
     * @return void
     */
    public function handle(NewOrderFuture $event)
    {

        $taxies = [];

        $queues = Ut_order_queue::where('order_id', $event->order->id)->get();

        foreach ($queues as $queue) {
            $taxi = Ut_taxi::where('id', $queue->taxi_id)->get()->last();
            array_push($taxies, $taxi);
        }

        $resultsCollection = collect($taxies);

        $categories = Ut_taxi_categories::orderBy('sort', 'DESC')->get();

        if (count($taxies)) {
            foreach ($categories as $category) {
                $filtered = $resultsCollection->where('category', $category->id);
                if (count($filtered)) {
                    //priority si en cox olan taxi
                    $maxPriorityTaxi = $filtered->sortBy('priority')->values()->last();

                    if ($maxPriorityTaxi) {
                        break;
                    }

                }
            }

            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $event->order->id,
                'taxi' => $maxPriorityTaxi->id,
                'user_id' => 13,
                'status' => 60,
                'reason' => $event->order->status == 700 ? 'Ön sifariş üçün taksi tapdi' : 'Açıq sifariş üçün taksi tapdi',
                'date' => date('Y-m-d H:i:s'),
                'location' => $event->order->orderDetailName->locationName()
            ]);

            $order_temp_id = Ut_order_taxi_temp::insertGetId([
                'taxi_id' => $maxPriorityTaxi->id,
                'order_id' => $event->order->id,
            ]);

            $order_queue_id = Ut_order_queue::where(['order_id' => $event->order->id, 'taxi_id' => $maxPriorityTaxi->id])->update([
                'o' => 1,
            ]);

            Ut_order::where('id', $event->order->id)
                ->update([
                    'is_queue' => 1,
                    'auto_search' => 1,
                ]);


            $orders = StaticController::getTaxiOrderPublicOrFuture($event->order->status);
            FcmController::notification($event->order->status, '/topics/taxi', 'Basliq', 'Metn', $orders);

            if ($event->order->status == 600) {
                $location = json_decode($event->order->orderDetailName->route, true)[0];
                StaticController::findedTaxi($maxPriorityTaxi, $event->order, $location['lat'], $location['lng'], 0);
                FcmController::notification(1, $maxPriorityTaxi->fcm_registered_id, 'Basliq', 'Metn', $event->order);
            }


        }

    }
}
