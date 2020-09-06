<?php

namespace App\Http\Controllers\Api;

use App\Events\NewOrderFuture;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\User;
use App\Ut_account;
use App\Ut_messages;
use App\Ut_order;
use App\Ut_order_cancel_request;
use App\Ut_order_detail;
use App\Ut_order_queue;
use App\Ut_order_status_history;
use App\Ut_order_taxi_temp;
use App\Ut_penalty_strategy;
use App\Ut_priority_transactions;
use App\Ut_setting;
use App\Ut_special_object_category;
use App\Ut_special_objects;
use App\Ut_taxi;
use App\Ut_taxi_geolocation;
use App\Ut_taxi_live_history;
use App\Ut_transaction;
use App\Ut_xref_taxi_order;
use Auth;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;

class TaxiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {

    }

    public function postTaxiLogin(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'device_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Login ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $device_id = $request->get('device_id');

            Ut_taxi::where(['code' => $car_code, 'device_id' => $device_id])
                ->update([
                    'live' => 1
                ]);

            $result = Ut_taxi::where(['code' => $car_code, 'device_id' => $device_id])->first();

            if (!$result) {
                Log::channel('error')->error('Taxi Login ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $result Message : Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Login ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'taxi_info' => $result]);

    }

    public function getTaxiMessage(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Message ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');

            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->first();

            if (!$taxi->id) {
                Log::channel('error')->error('Taxi Message ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi->id Message : Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $messages = DB::table('ut_messages as m')
                ->join('ut_users as u', 'u.id', 'm.user_id')
                ->where('m.delete', false)
                ->where('m.destination_id', $taxi->id)
                ->where('m.destination_type', 1)
                ->select('m.*', DB::raw("CONCAT(u.first_name,' ',u.last_name) AS sender"))
                ->orderBy('m.id', 'DESC')
                ->get();


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Message ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $messages]);

    }

    public function postTaxiMessageRead(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'message_id' => 'required|string',
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Message Read ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');

            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->first();
            if (!$taxi->id) {
                Log::channel('error')->error('Taxi Message Read ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi->id Message : Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $message_id = $request->get('message_id');

            $message = Ut_messages::where(['id' => $message_id, 'destination_id' => $taxi->id, 'delete' => false])->first();
            if (!$message) {
                Log::channel('error')->error('Taxi Message Read ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$message  Message : Mesaj tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Mesaj tapılmadı']);
            }

            $message->update([
                'read' => true
            ]);


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Message Read ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }

    public function getTaxiOrderHistories(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Histories ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');

            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->first();
            if (!$taxi->id) {
                Log::channel('error')->error('Taxi Order Histories ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi->id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $date = $request->get('date') ? date('Y-m-d', strtotime($request->get('date'))) : date('Y-m-d');

            $orders = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_', 'customerName'])->join('ut_order_detail as utd', 'ut_order.id', 'utd.order_id')
                ->where('ut_order.status', 4)
                ->where('ut_order.taxi', $taxi->id)
                ->where('ut_order.delete', false)
                ->whereDate('utd.order_date', $date)
                ->select('ut_order.*', 'utd.order_date')
                ->get();

            $results = [];

            if (count($orders)) {
                foreach ($orders as $order) {
                    $results[] = StaticController::getResultFutureOrPublicOrListen($order);
                }
            }


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Order Histories ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $results]);

    }

    public function getTaxiBalanceTransactions(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Balance Transactions ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $limit = $request->get('limit') ? $request->get('limit') : 20;

            $taxi_id = Ut_taxi::where(['code' => $car_code, 'status' => true])->value('id');

            if (!$taxi_id) {
                Log::channel('error')->error('Taxi Balance Transctions ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $account_id = Ut_account::where('type', 1)->where('destination', $taxi_id)->value('id');

            if (!$account_id) {
                Log::channel('error')->error('Taxi Balance Transctions ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$account_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $transactions = Ut_transaction::where('delete', false)
                ->whereIn('type', [1, 3])
                ->where(function ($query) use ($account_id) {
                    $query->where('from_account', $account_id);
                    $query->orWhere('to_account', $account_id);
                })
                ->orderBy('id', 'DESC')
                ->limit($limit)
                ->select('id', 'order', 'type', DB::raw('Date(date) AS date'), 'amount', 'description', DB::raw('TIME(date) AS time'))
                ->get();


            $transactions = collect($transactions);

            $transactions->each(function ($item, $key) {

                switch ($item->type) {
                    case 1:
                        $item->type = 'Sifariş №"' . $item->order;
                        break;
                    case 3:
                        $item->type = 'Cərimə';
                        break;
                    default:
                        $item->type = 'Naməlum';
                }

                return true;
            });

            $transactions->all();


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Balance Transactions ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'transactions' => $transactions]);

    }

    public function getTaxiBalanceHistories(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Balance Histories ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $limit = $request->get('limit') ? $request->get('limit') : 20;

            $taxi_id = Ut_taxi::where(['code' => $car_code, 'status' => true])->value('id');

            if (!$taxi_id) {
                Log::channel('error')->error('Taxi Balance Histories ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");

                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $account_id = Ut_account::where('type', 1)->where('destination', $taxi_id)->value('id');

            if (!$account_id) {
                Log::channel('error')->error('Taxi Balance Histories ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'account_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $transactions = Ut_transaction::where('delete', false)
                ->whereIn('type', [4, 5])
                ->where(function ($query) use ($account_id) {
                    $query->where('from_account', $account_id);
                    $query->orWhere('to_account', $account_id);
                })
                ->orderBy('id', 'DESC')
                ->limit($limit)
                ->select('id', 'order', 'type', DB::raw('Date(date) AS date'), 'amount', 'description', DB::raw('TIME(date) AS time'))
                ->get();

            $transactions = collect($transactions);

            $transactions->each(function ($item, $key) {

                switch ($item->type) {
                    case 4:
                        $item->type = 'Balans';
                        break;
                    case 5:
                        $item->type = 'Nağdlaşdırma';
                        break;
                    default:
                        $item->type = 'Naməlum';
                }

                return true;
            });

            $transactions->all();


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Balance Histories ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'transactions' => $transactions]);

    }

    public function getTaxiPriorityList(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Priority List ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $limit = $request->get('limit') ? $request->get('limit') : 20;

            $taxi = Ut_taxi::where(['code' => $car_code, 'status' => true, 'delete' => 0])->first();
            if (!$taxi) {
                Log::channel('error')->error('Taxi Priority List ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $results = Ut_taxi::where(['status' => true, 'delete' => 0, 'category' => $taxi->category])->limit($limit)->get(['code', 'firstname', 'lastname', 'priority']);

        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Priority List ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $results]);

    }

    public function getTaxiPriorityHistories(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Priority Histories ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $limit = $request->get('limit') ? $request->get('limit') : 20;

            $taxi_id = Ut_taxi::where(['code' => $car_code, 'status' => true, 'delete' => 0])->value('id');
            if (!$taxi_id) {
                Log::channel('error')->error('Taxi Priority Histories ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $results = Ut_priority_transactions::where(['taxi_id' => $taxi_id, 'delete' => 0])->limit($limit)->get();


            $transactions = collect($results);

            $transactions->each(function ($item, $key) {


                switch ($item->type) {
                    case 1:
                        $item->description = $item->order_id . ' №-li sifarişi tamamladığı üçün';
                        break;
                    case 2:
                        $item->description = $item->order_id . ' №-li sifarişi ləğv etdiyi üçün';
                        break;
                    case 3:
                        $item->description = $item->order_id . ' №-li sifarişi cavabsız qoyduğu üçün';
                        break;
                    case 4:
                        $item->description = $item->order_id . ' №-li sifarişi qəbul edib ləğv etdiyi üçün';
                        break;
                    default:
                        $item->description = 'Naməlum';
                }

                return true;
            });

            $transactions->all();

        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Priority Histories ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $results]);

    }

    public function postTaxiCoordinate(Request $request)
    {

        try {

            date_default_timezone_set('Asia/Baku');

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'latitude' => 'required|string',
                'longitude' => 'required|string',
                'bearing' => 'required|string',
                'fcm_token' => 'required|string',
                'region_id' => 'required|string',
                'live' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Coordinate ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $latitude = $request->get('latitude');
            $longitude = $request->get('longitude');
            $bearing = $request->get('bearing');
            $fcm_token = $request->get('fcm_token');
            $region_id = $request->get('region_id') ?? 0;
            $live = $request->get('live');

            $limit = $request->get('limit') ? $request->get('limit') : 20;

            $taxi = Ut_taxi::where(['code' => $car_code, 'status' => true, 'delete' => 0])->first();
            if (!$taxi) {
                Log::channel('error')->error('Taxi Coordinate ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            if ($region_id) {
                Log::channel('error')->error('Taxi Coordinate ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'region_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                $region = Ut_special_objects::where(['id' => $region_id, 'status' => true, 'delete' => false])->first();

                if (!$region) {
                    Log::channel('error')->error('Taxi Coordinate ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$region ---- Message : ' . ' Region tapilmadi');
                    Log::channel('error')->info("----------------------------------------------------------------------");
                    return response()->json(['status' => 'false', 'error' => 'Region tapilmadi']);
                }

                $fake_latitude = $region->latitude;
                $fake_longitude = $region->longitude;


            } else {
                //region - static location olmadigi ucun face adi bos dusmelidir
                $fake_latitude = '';
                $fake_longitude = '';
            }

            if ($taxi->action) {
                $order = Ut_order::where(['delete' => false, 'taxi' => $taxi->id])
                    ->whereIn('status', [2, 3, 8])
                    ->orderBy('created_at', 'DESC')
                    ->first();

                if ($order) {
                    $order_id = $order->id ? $order->id : NULL;
                    $order_status = $order->status ? $order->status : NULL;
                } else {
                    $order_status = NULL;
                    $order_id = NULL;
                }
            } else {
                $order_status = NULL;
                $order_id = NULL;
            }


            DB::table('ut_taxi')->where('id', $taxi->id)
                ->update([
                    'live' => $live,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'bearing' => $bearing,
                    'fake_latitude' => $fake_latitude,
                    'fake_longitude' => $fake_longitude,
                    'region_id' => $region_id,
                    'last_coordinate_date' => date('Y-m-d H:i:s'),
                    'fcm_registered_id' => $fcm_token,
                ]);

            $geolocation_id = Ut_taxi_geolocation::insertGetId([
                'taxi_id' => $taxi->id,
                'live' => $taxi->live,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'region_id' => $region_id,
                'fake_latitude' => $fake_latitude,
                'fake_longitude' => $fake_longitude,
                'date' => date('Y-m-d H:i:s'),
                'order_id' => $order_id,
                'order_status' => $order_status
            ]);

//            $taxies = Ut_taxi::where(['delete' => false])->limit(20)->get();
//
//            $transactions = collect($taxies);
//            $transactions->each(function ($item, $key) {
//                $order = Ut_order::where(['delete' => false, 'taxi' => $item->id])->get()->last();
//                if ($order) {
//                    $order_status = $order->status;
//                } else {
//                    $order_status = null;
//                }
//
//                $item->order_status = $order_status;
//                return true;
//            });

//            $taxi_free = $taxies->where('action', 0)->count();
//            $taxi_not_free = $taxies->where('action', 1)->count();
//            $taxi_accepted = DB::table('ut_taxi as t')
//                ->join('ut_order as ut', 't.id', 'ut.taxi')
//                ->where('ut.status', 2)
//                ->where('t.delete', false)
//                ->where('t.action', true)
//                ->count();
//
//            $taxi_reached = DB::table('ut_taxi as t')
//                ->join('ut_order as ut', 't.id', 'ut.taxi')
//                ->where('ut.status', 3)
//                ->where('t.delete', false)
//                ->where('t.action', true)
//                ->count();
//
//            $taxi_pickup = DB::table('ut_taxi as t')
//                ->join('ut_order as ut', 't.id', 'ut.taxi')
//                ->where('ut.status', 8)
//                ->where('t.delete', false)
//                ->where('t.action', true)
//                ->count();
//
//
//            $transactions->all();
//
//            $taxiInfo = [
//                'taxi_free' => $taxi_free,
//                'taxi_not_free' => $taxi_not_free,
//                'taxi_accepted' => $taxi_accepted,
//                'taxi_reached' => $taxi_reached,
//                'taxi_pickup' => $taxi_pickup,
//                'taxi_all_taxi' => count($taxies),
//            ];


//            broadcast(new \App\Events\TaxiMapEvent($transactions, $taxiInfo));

        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Coordinate ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'geolocation_id' => $geolocation_id, 'operation' => true]);

    }

    public function getTaxiOrderListen(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Listen ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');

            $taxi = Ut_taxi::where(['code' => $car_code, 'status' => true, 'delete' => 0])->first();
            if (!$taxi) {
                Log::channel('error')->error('Taxi Order Listen ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $order_temp = Ut_order_taxi_temp::where('taxi_id', $taxi->id)->get()->last();
            if (!$order_temp) {
                Log::channel('error')->error('Taxi Order Listen ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$order_temp ---- Message : ' . ' Son sifariş tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Son sifariş tapılmadı']);
            }

            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['id' => $order_temp->order_id, 'delete' => 0])
                ->whereIn('status', [1, 2, 3, 8])
                ->get()->last();

            if (!$order) {
                Log::channel('error')->error('Taxi Order Listen ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'order ---- Message : ' . ' Sifariş tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Sifariş tapılmadı']);
            }

            $result = StaticController::getResultFutureOrPublicOrListen($order);


        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Listen ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json($result);

    }

    public function getTaxiDetail(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Detail ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");

                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');

            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->first();

            if (!$taxi) {
                Log::channel('error')->error('Taxi Detail ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $taxi->balance = $taxi->account->balance;
            $taxi->bonus = $taxi->account->bonus;

            $defaultSettings = [];

            $defaults = Ut_setting::get();
            foreach ($defaults as $d) {
                $defaultSettings[$d->setting_key] = $d->setting_value;
            }

            $setting = $taxi->settingName ? $taxi->settingName : $defaultSettings;

            $balancePenalties = Ut_penalty_strategy::where(['status' => true, 'delete' => false])->get();

            $banned = [
                'banned_status' => $taxi->bannedTaxiName ? $taxi->bannedTaxiName->status : 0,
                'banned_description' => $taxi->bannedTaxiName ? $taxi->bannedTaxiName->description : NULL,
            ];


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Detail ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'taxi' => $taxi, 'setting' => $setting, 'banned' => $banned, 'balancePenalties' => $balancePenalties]);

    }

    public function postTaxiOrderCancel(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'order_id' => 'required|integer',
                'cancel_type' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Cancel ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $order = Ut_order::with('orderDetailName')->where('id', $request->get('order_id'))->first();
            if (!$order) {
                Log::channel('error')->error('Taxi Order Cancel ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$order ---- Message : ' . ' Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi']);
            }

            $car_code = $request->get('code');
            $cancel_type = $request->get('cancel_type');

            $taxi_id = Ut_taxi::where(['status' => true, 'code' => $car_code])->value('id');

            if (!$taxi_id) {
                Log::channel('error')->error('Taxi Order Cancel ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $taxi_status_history = Ut_order_status_history::where('order', $order->id)->get()->last();
            if (!$taxi_status_history) {
                Log::channel('error')->error('Taxi Order Cancel ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_status_history ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Taksi tapilmadi']);
            }


            $reason = $cancel_type ? 'Taksi ləğv etdi' : 'Taksi 20 saniyə keçdiyi üçün sifariş ləğv olundu';
            $status = $cancel_type ? 10 : 20;

            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi_id,
                'user_id' => 13,
                'status' => $status,
                'reason' => $reason,
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);

            $xref_taxi_order_id = Ut_xref_taxi_order::insertGetId([
                'taxi_id' => $taxi_id,
                'order_id' => $order->id,
                'user_id' => '-1',
                'type' => 2,
                'location' => $order->orderDetailName->locationName(),
                'reason' => $reason,
                'date' => date('Y-m-d H:i:s')
            ]);

            Ut_order::where('id', $order->id)
                ->update([
                    'taxi' => 0,
                    'is_queue' => 0,
                    'auto_search' => 1,
                    'status' => $order->public ? 600 : 0,
                    'refused_taxies' => $order->refused_taxies ? $order->refused_taxies . ',' . $taxi_id : $taxi_id,
                    'last_xref_id' => $xref_taxi_order_id,
                    'sort' => $order->public ? 1 : 0
                ]);

            Ut_order_taxi_temp::where('order_id', $order->id)
                ->delete();

            DB::table('ut_taxi')->where('id', $taxi_id)
                ->update([
                    'action' => 0,
                ]);

            Ut_order_queue::where('order_id', $order->id)
                ->delete();

            if ($status == 10) {
                StaticController::getPriorityRulesCheck($order->id, $taxi_id, 2, $order->orderDetailName->payment_method);
            }

            if ($status == 20) {
                StaticController::getPriorityRulesCheck($order->id, $taxi_id, 3, $order->orderDetailName->payment_method);
            }

            if ($order->public) {
                $orderOpen = StaticController::getTaxiOrderPublicOrFuture(600);
                FcmController::notification(600, '/topics/taxi', 'Basliq', 'Metn', $orderOpen);
            }

            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['delete' => false, 'id' => $order->id])->first();
            $order = StaticController::getResultFutureOrPublicOrListen($order, 0);
            broadcast(new \App\Events\OrderEvent($order, 1));

            $postFindTaxiTest = new OrderController();
            $postFindTaxiTest->postFindTaxiTest();


        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Cancel ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }

    public function postTaxiOrderAccept(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'order_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Accept ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $order = Ut_order::with('orderDetailName')->where('id', $request->get('order_id'))->first();
            if (!$order) {
                Log::channel('error')->error('Taxi Order Accept  ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$order ---- Message : ' . ' Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi']);
            }

            $car_code = $request->get('code');
            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->first();

            if (!$taxi) {
                Log::channel('error')->error('Taxi Order Accept ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $taxi_status_history = Ut_order_status_history::where('order', $order->id)->get()->last();
            if (!$taxi_status_history) {
                Log::channel('error')->error('Taxi Order Accept  ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_status_history ---- Message : ' . ' Taksi tapılmadı');
                return response()->json(['status' => 'false', 'error' => 'Taksi tapilmadi']);
            }


            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi->id,
                'user_id' => 13,
                'status' => 2,
                'reason' => 'Taksi qəbul etdi',
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);

            $xref_taxi_order_id = Ut_xref_taxi_order::insertGetId([
                'taxi_id' => $taxi->id,
                'order_id' => $order->id,
                'user_id' => '-1',
                'type' => 3,
                'location' => $order->orderDetailName->locationName(),
                'reason' => 'Taksi qəbul etdi',
                'date' => date('Y-m-d H:i:s')
            ]);

            Ut_order::where('id', $order->id)
                ->update([
                    'taxi' => $taxi->id,
                    'status' => 2,
                    'auto_search' => 0,
                    'public' => 0,
                    'sort' => 2
                ]);

            DB::table('ut_taxi')->where('id', $taxi->id)
                ->update([
                    'action' => 1,
                ]);


            $message = 'Sizə yaxınlaşacaq:' . $taxi->colorName->color_name . ',' . $taxi->brandName->name . ' ' . $taxi->modelName_->name . ',' . $taxi->number . ',' . $taxi->phone . ' - ' . $order->orderDetailName->price;

            FcmClientController::notification(1, $order->customerName->fcm_registered_id, 'Ulduz Taxi', 'Sizə taksi təyin edildi', $message);

            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['delete' => false, 'id' => $order->id])->first();
            $order = StaticController::getResultFutureOrPublicOrListen($order, 0);
            broadcast(new \App\Events\OrderEvent($order, 1));


        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Accept ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }

    public function postTaxiOrderApply(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'order_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Apply ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $order = Ut_order::with("orderDetailName")->where('id', $request->get('order_id'))->first();
            if (!$order) {
                Log::channel('error')->error('Taxi Order Apply ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$order ---- Message : ' . ' Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi']);
            }

            $car_code = $request->get('code');
            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->first();

            if (!$taxi) {
                Log::channel('error')->error('Taxi Order Apply ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $taxi_status_history = Ut_order_status_history::where('order', $order->id)->get()->last();
            if (!$taxi_status_history) {
                Log::channel('error')->error('Taxi Order Apply ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_status_history ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Taksi tapilmadi']);
            }


            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi->id,
                'user_id' => 13,
                'status' => 3,
                'reason' => 'Müştəriyə çatdım',
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);

            Ut_order::where('id', $order->id)
                ->update([
                    'status' => 3,
                    'sort' => 4
                ]);


            $message = 'Taksi sizi gözləyir:' . $taxi->colorName->color_name . ',' . $taxi->brandName->name . ' ' . $taxi->modelName_->name . ',' . $taxi->number . ',' . $taxi->phone . ' - ' . $order->orderDetailName->price;

            FcmClientController::notification(2, $order->customerName->fcm_registered_id, 'Ulduz Taxi', 'Taksi sizi gözləyir', $message);


            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['delete' => false, 'id' => $order->id])->first();
            $order = StaticController::getResultFutureOrPublicOrListen($order, 0);
            broadcast(new \App\Events\OrderEvent($order, 1));

        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Apply ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }

    public function postTaxiOrderPickup(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'order_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Pickup ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $order = Ut_order::with('orderDetailName')->where('id', $request->get('order_id'))->first();
            if (!$order) {
                Log::channel('error')->error('Taxi Order Pickup ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'order ---- Message : ' . ' Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi']);
            }

            $car_code = $request->get('code');
            $taxi_id = Ut_taxi::where(['status' => true, 'code' => $car_code])->value('id');

            if (!$taxi_id) {
                Log::channel('error')->error('Taxi Order Pickup ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'taxi_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $taxi = Ut_order_status_history::where('order', $order->id)->get()->last();
            if (!$taxi) {
                Log::channel('error')->error('Taxi Order Pickup ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Taksi tapilmadi']);
            }

            if ($order && $order->orderDetailName && $order->orderDetailName->payment_method == 2) {
                $price = $order->orderDetailName ? $order->orderDetailName->price + $order->orderDetailName->operator_price : 0;
                $price *= 100;
                $price = (int)$price;
                $payment = new PaymentGatewayGoldenpayController();
                $paymentResult = $payment->getPaymentKeyJSONRequest($price, 'lv', 'v', 'Odeme');
                if ($paymentResult->status->message === 'success') {
                    $sms = new ClientSmsController();
                    $sms->sendSms('994557463160');
                    FcmClientController::notification(1000, $order->customerName->fcm_registered_id, 'Ulduz Taxi', '', 'Balansınızdan ' . $price . ' Azn çıxıldı ');
                } else {
//                    FcmClientController::notification(1001, $order->customerName->fcm_registered_id, 'Ulduz Taxi', '', 'Balansınızda vəsait yoxdur');

//                    Ut_order_detail::where('order_id', $order->id)->update('payment_method', 1);
//
//                    if ($order->taxi && $order->taxiName) {
//                        Ut_order_status_history::insertGetId([
//                            'order' => $order->id,
//                            'taxi' => $taxi_id,
//                            'user_id' => 13,
//                            'status' => 15,
//                            'reason' => 'Sifariş dəyişikliyi',
//                            'date' => date('Y-m-d H:i:s'),
//                            'location' => $order->orderDetailName->locationName(),
//                        ]);
//                        FcmController::notification(15, $order->taxiName->fcm_registered_id, 'Basliq', 'Metn', $order = []);
//                    }
                }
            }


            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi_id,
                'user_id' => 13,
                'status' => 8,
                'reason' => 'Müştərini götürdüm',
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);

            Ut_order::where('id', $order->id)
                ->update([
                    'status' => 8,
                    'sort' => 5
                ]);

            FcmClientController::notification(3, $order->customerName->fcm_registered_id, 'Ulduz Taxi', '', '');

            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['delete' => false, 'id' => $order->id])->first();
            $order = StaticController::getResultFutureOrPublicOrListen($order, 0);
            broadcast(new \App\Events\OrderEvent($order, 1));

        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Pickup ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }

    public function postTaxiOrderComplete(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'order_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Complete ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $order = Ut_order::with('orderDetailName')->where('id', $request->get('order_id'))->first();
            if (!$order) {
                Log::channel('error')->error('Taxi Order Complete ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$order ---- Message : ' . ' Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi']);
            }

            $car_code = $request->get('code');
            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->get()->last();

            if (!$taxi) {
                Log::channel('error')->error('Taxi Order Complete ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $taxi_status_history = Ut_order_status_history::where('order', $order->id)->get()->last();
            if (!$taxi_status_history) {
                Log::channel('error')->error('Taxi Order Complete ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_status_history ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Taksi tapilmadi']);
            }


            //bu price-dan asagi olduqda taksiden faiz tutulmur
            $minimalPrice = $order->orderDetailName ? json_decode($order->orderDetailName->tariffName->plan_for_distance, true) : '';
            //sifarisin qiymeti
            $price = $order->orderDetailName ? $order->orderDetailName->price + $order->orderDetailName->operator_price : 0; //4.27
            //tarifin faizi
            $distance_fee = $order->orderDetailName ? $order->orderDetailName->tariffName->distance_fee : 0; //10
            //cixilacaq mebleb
            $fee = round(($price * $distance_fee) / 100, 2); //0.472
            //nagd ve ya nagdsiz
            $payment_method = $order->orderDetailName ? $order->orderDetailName->payment_method : 1; //1
            //taksinin balansi
            $taxiBalance = $taxi->account->balance;//100

            if ($payment_method == 1) {
                if ($price > $minimalPrice[0]['price']) {
                    $transaction_id = Ut_transaction::insertGetId([
                        'user' => 0,
                        'from_account' => $taxi->account->id,
                        'from_account_type' => 1,
                        'to_account' => 1,
                        'to_account_type' => 0,
                        'order' => $order->id,
                        'type' => 1,
                        'amount' => '-' . $fee,
                        'description' => 'Sifarişi tamamladığına görə',
                        'date' => date('Y-m-d H:i:s')
                    ]);

                    $taxiBalanceText = $fee;

                    if (!$taxi->free) {
                        $taxiBalance -= $fee; //4.5
                        $priceText = "Sifarişi uğurla tamamladınız . Balansınızdan " . $fee . " AZN çıxıldı .";
                    } else {
                        $priceText = "Sifarişi uğurla tamamladınız .";
                    }

                } else {
                    $fee = 0;
                    $priceText = "";
                }

            } else {

                $customerGroup = $order->customerName->groupName->account;

                $transaction_id = Ut_transaction::insertGetId([
                    'user' => 0,
                    'from_account' => $customerGroup->id,
                    'from_account_type' => 3,
                    'to_account' => $taxi->account->id,
                    'to_account_type' => 1,
                    'order' => $order->id,
                    'type' => 1,
                    'amount' => $price,
                    'description' => 'Nəğdsiz sifariş olduğu üçün balansına əlavə edildi',
                    'date' => date('Y-m-d H:i:s')
                ]);

                $customerGroupBalance = $customerGroup->balance - $price;
                Ut_account::where(['type' => 3, 'destination' => $customerGroup->destination])
                    ->update([
                        'balance' => $customerGroupBalance,
                    ]);

                $taxiBalance = ($taxiBalance + $price) - $fee;

                $taxiBalanceText = $price - $fee;

                $priceText = "Sifarişi uğurla tamamladınız. Balansınıza " . $taxiBalanceText . " AZN əlavə edildi .";
            }

            Ut_account::where(['type' => 1, 'destination' => $taxi->id])
                ->update([
                    'balance' => $taxiBalance,
                ]);

            $currentBalance = Ut_account::where(['type' => 1, 'destination' => $taxi->id])->value('balance');

            if ($price > $minimalPrice) {
                $message = $priceText . "Qalıq balans" . $currentBalance . "AZN";
            } else {
                $message = "Sifarişi uğurla tamamladınız . ";
            }


//            $reason = $order->orderDetailName->payment_method ? $taxiBalanceText . ' Azn balansından çıxıldı' : $taxiBalanceText . ' Azn balansınıza əlavə olundu';

            $reason = $priceText;

            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi->id,
                'user_id' => 13,
                'status' => 4,
                'reason' => 'Sifariş bitdi',
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);

            Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi->id,
                'user_id' => 13, //balans artımı
                'status' => 30,
                'reason' => $reason,
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);

            //prioritetin artirilmasi
            StaticController::getPriorityRulesCheck($order->id, $taxi->id, 1, $order->orderDetailName->payment_method);


            Ut_order::where('id', $order->id)
                ->update([
                    'status' => 4,
                    'sort' => 6
                ]);

            DB::table('ut_taxi')->where('id', $taxi->id)
                ->update([
                    'action' => 0,
                ]);

            $checkDeleteFutureOrder = Ut_order_queue::where(['order_id' => $order->id, 'taxi_id' => $taxi->id, 'o' => 1])
                ->delete();

            if ($checkDeleteFutureOrder) {
                $orders = StaticController::getTaxiOrderPublicOrFuture($order->status);
                FcmController::notification($order->status, '/topics/taxi', 'Basliq', 'Metn', $orders);
            }

            FcmClientController::notification(4, $order->customerName->fcm_registered_id, 'Ulduz Taxi', '', '');

            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['delete' => false, 'id' => $order->id])->first();
            $order = StaticController::getResultFutureOrPublicOrListen($order, 0);
            broadcast(new \App\Events\OrderEvent($order, 1));

        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Complete ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true, 'message' => $reason]);

    }

//    public function getTaxiOrderPublic(Request $request)
//    {
//        try {
//
//            $validator = Validator::make($request->all(), [
//                'code' => 'required|string',
//            ]);
//
//            if ($validator->fails()) {
//                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
//            }
//
//            $car_code = $request->get('code');
//            $taxi_id = Ut_taxi::where(['status' => true, 'code' => $car_code])->value('id');
//
//            if (!$taxi_id) {
//                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
//            }
//
//            $result = StaticController::getTaxiOrderPublicOrFuture(600);
//
//        } catch
//        (\Exception $e) {
//            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
//        }
//
//        return response()->json($result);
//
//    }
//
//    public function postTaxiOrderPublicRequest(Request $request)
//    {
//        try {
//
//            $validator = Validator::make($request->all(), [
//                'code' => 'required|string',
//                'order_id' => 'required|string',
//            ]);
//
//            if ($validator->fails()) {
//                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
//            }
//
//            $order = Ut_order::where('id', $request->get('order_id'))->where('taxi', false)->where('status', 600)->first();
//            if (!$order) {
//                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi', 'order' => []]);
//            }
//
//            $car_code = $request->get('code');
//            $taxi_id = Ut_taxi::where(['status' => true, 'code' => $car_code])->value('id');
//
//            if (!$taxi_id) {
//                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
//            }
//
//            $order->update([
//                'taxi' => $taxi_id,
//                'status' => 2,
//            ]);
//
//            $order_status_id = Ut_order_status_history::insertGetId([
//                'order' => $order->id,
//                'taxi' => $taxi_id,
//                'user_id' => 13,
//                'status' => 2,
//                'reason' => 'Taksi qəbul etdi',
//                'date' => date('Y-m-d H:i:s'),
//                'location' => $order->orderDetailName->locationName()
//            ]);
//
//            $order_temp_id = Ut_order_taxi_temp::insertGetId([
//                'taxi_id' => $taxi_id,
//                'order_id' => $order->id,
//            ]);
//
//            $result = StaticController::getResultFutureOrPublicOrListen($order);
//
//            $orders = StaticController::getTaxiOrderPublicOrFuture(600);
//
//            FcmController::notification(600, '/topics/taxi', 'Basliq', 'Metn', $orders);
//
//        } catch
//        (\Exception $e) {
//            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
//        }
//
//        return response()->json($result);
//
//    }

    public function getTaxiRegions(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Regions ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');

            $taxi = Ut_taxi::where(['code' => $car_code, 'status' => true, 'delete' => 0])->first();
            if (!$taxi) {
                Log::channel('error')->error('Taxi Regions ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $regions = Ut_special_object_category::where(['status' => true, 'delete' => false])->orderBy('sort', 'DESC')->get();


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Regions ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $regions]);

    }

    public function getTaxiRegionObjects(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required|string',
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Region Objects ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');

            $taxi = Ut_taxi::where(['code' => $car_code, 'status' => true, 'delete' => 0])->first();
            if (!$taxi) {
                Log::channel('error')->error('Taxi Region Objects ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $category_id = $request->get('category_id');

            $region = Ut_special_object_category::where(['id' => $category_id, 'status' => true, 'delete' => false])->first();
            if (!$region) {
                return response()->json(['status' => 'false', 'error' => 'Region tapilmadi']);
            }


            $orders = Ut_order::whereIn('status', [0, 600, 700])
                ->where('taxi', false)
                ->where('delete', false)
                ->get();

            $regionObjects = Ut_special_objects::where(['category_id' => $category_id, 'status' => true, 'delete' => false])->orderBy('sort', 'DESC')->get();

            $transactions = collect($regionObjects);
            $transactions->each(function ($item, $key) use ($orders) {
                $km = 0.03;
                $away3LatMax = (number_format(($item->latitude + $km), 2, '.', ""));
                $away3LatMin = (number_format(($item->latitude - $km), 2, '.', ""));
                $away3LongMax = (number_format(($item->longitude + $km), 2, '.', ""));
                $away3LongMin = (number_format(($item->longitude - $km), 2, '.', ""));

                $taxiCount = Ut_taxi::where(['delete' => false, 'status' => true, 'live' => true, 'action' => false, 'fake_latitude' => $item->latitude, 'fake_longitude' => $item->longitude])->count();

                $orderCount = 0;
                if (count($orders)) {
                    foreach ($orders as $order) {
                        $location = json_decode($order->orderDetailName->route, true)[0];
                        if ($location['lat'] < $away3LatMax &&
                            $location['lat'] > $away3LatMin &&
                            $location['lng'] < $away3LongMax &&
                            $location['lng'] > $away3LongMin
                        ) {
                            $orderCount++;
                        }
                    }
                }

                $item->taxi = $taxiCount;
                $item->order = $orderCount;
                return true;
            });

            $transactions->all();


        } catch (\Exception $e) {
            Log::channel('error')->error('Taxi Region Objects ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $regionObjects]);

    }

    //app a daxil olarken menden aldigi on sifarisler
    public function getTaxiOrderFutureOrPublic(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'is_public' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Future Or Public ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->first();

            if (!$taxi) {
                Log::channel('error')->error('Taxi Order Future Or Public ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $is_public = $request->get('is_public');
            $status = $is_public ? 600 : 700;


            //eger bu taksinin on sifarisi varsa
            $order_queues = Ut_order_queue::where(['taxi_id' => $taxi->id, 'o' => true, 'is_public' => $is_public])->get();

            $results = [];

            if (count($order_queues)) {
                foreach ($order_queues as $order_queue) {
                    $order = Ut_order::where(['id' => $order_queue->order_id, 'taxi' => false, 'status' => $status])->first();
                    if ($order) {
                        $result[] = StaticController::getResultFutureOrPublicOrListen($order, 1);
                        $results = ['status' => 'true', 'error' => '', 'success' => 200, 'orders' => $result];
                    }
                }
            } else {
                $results = StaticController::getTaxiOrderPublicOrFuture($status);
            }


        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Future Or Public ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json($results);

    }

    public function postTaxiOrderFutureOrPublicRequest(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'order_id' => 'required|string',
                'is_public' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Future Or Public Request ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $is_public = $request->get('is_public');
            $status = $is_public ? 600 : 700;

            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName'])->where('id', $request->get('order_id'))->where('taxi', false)->where('status', $status)->first();
            if (!$order) {
                Log::channel('error')->error('Taxi Order Future Or Public Request ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$order ---- Message : ' . ' Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi', 'order' => []]);
            }

            $car_code = $request->get('code');
            $taxi = Ut_taxi::with('account')->where(['status' => true, 'code' => $car_code])->first();

            if (!$taxi) {
                Log::channel('error')->error('Taxi Order Future Or Public Request ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            // fee ni tapmaq ucun
            $price = $order->orderDetailName ? $order->orderDetailName->price + $order->orderDetailName->operator_price : 0; //4.27
            //tarifin faizi
            $distance_fee = $order->orderDetailName ? $order->orderDetailName->tariffName->distance_fee : 0; //10
            //cixilacaq mebleb
            $fee = round(($price * $distance_fee) / 100, 2); //0.472

            if ($taxi->account->balance < $fee) {
                Log::channel('error')->error('Taxi Order Future Or Public Request ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi->account->balance < $fee ---- Message : ' . ' Sizin balansınız kifayət qədər deyil');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'true', 'error' => 403, 'message' => 'Sizin balansınız kifayət qədər deyil']);
            }

            $order_queue_id = Ut_order_queue::insertGetId([
                'taxi_id' => $taxi->id,
                'order_id' => $order->id,
                'date' => date('Y-m-d H:i:s'),
                'o' => 0,
                'is_public' => $is_public,
            ]);

            $queues = Ut_order_queue::where('order_id', $order->id)->get();

            if (count($queues) == 1) {
                event(new NewOrderFuture($order));
            }


        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Future Or Public Request ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }


    public function postTaxiOrderCancelRequest(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'order_id' => 'required|integer',
                'reason' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Cancel Request ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $order = Ut_order::with("orderDetailName")->where('id', $request->get('order_id'))->first();
            if (!$order) {
                Log::channel('error')->error('Taxi Order Cancel Request ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$order ---- Message : ' . ' Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi']);
            }

            $car_code = $request->get('code');
            $taxi = Ut_taxi::where(['status' => true, 'code' => $car_code])->first();

            if (!$taxi) {
                Log::channel('error')->error('Taxi Order Cancel Request ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }


            $reason = $request->get('reason');

            $order_status_id = Ut_order_cancel_request::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi->id,
                'date' => date('Y-m-d H:i:s'),
                'reason' => $reason,
                'confirm' => 0,
                'location' => json_encode(['latitude' => $taxi->latitude, 'longitude' => $taxi->longitude])
            ]);

            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi->id,
                'user_id' => 13,
                'status' => 40,
                'reason' => 'Sifariş ləğv edilmə gözləyir',
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);


            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['delete' => false, 'id' => $order->id])->first();
            $order = StaticController::getResultFutureOrPublicOrListen($order, 0);
            broadcast(new \App\Events\OrderEvent($order, 1));

        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Cancel Request ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }


    // taksi sorgu yox temamile legv etmek
    public function postTaxiOrderFail(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'order_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Order Fail ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $order = Ut_order::with('orderDetailName')->where('id', $request->get('order_id'))->first();
            if (!$order) {
                Log::channel('error')->error('Taxi Balance Transctions ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$order ---- Message : ' . ' Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi']);
            }

            $car_code = $request->get('code');
            $taxi_id = Ut_taxi::where(['status' => true, 'code' => $car_code])->value('id');

            if (!$taxi_id) {
                Log::channel('error')->error('Taxi Balance Transctions ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            Ut_order::where('id', $order->id)
                ->update([
                    'status' => 0,
                    'taxi' => 0,
                    'is_queue' => 0,
                    'auto_search' => 1,
                    'refused_taxies' => $order->refused_taxies ? $order->refused_taxies . ',' . $taxi_id : $taxi_id,
                    'sort' => 7,
                ]);

            Ut_taxi::where('id', $taxi_id)
                ->update([
                    'action' => 0,
                ]);


            Ut_order_queue::where('order_id', $order->id)
                ->delete();

            StaticController::getPriorityRulesCheck($order->id, $taxi_id, 4, $order->orderDetailName->payment_method);
            StaticController::getBalanceRulesCheck($order->id, $taxi_id, 4, $order->orderDetailName->payment_method);

            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi_id,
                'user_id' => 13,
                'status' => 25,
                'reason' => 'Sifarişi götürdükdən sonra taksi ləğv etdi',
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);

            //musteriye fcm gedir
            FcmClientController::notification(5, $order->customerName->fcm_registered_id, 'Ulduz Taxi', 'Taksi çıxarıldı', '');

            $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['delete' => false, 'id' => $order->id])->first();
            $order = StaticController::getResultFutureOrPublicOrListen($order, 0);
            broadcast(new \App\Events\OrderEvent($order, 1));

        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Order Fail ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }


    // taksi sorgu yox temamile legv etmek
    public function postTaxiLive(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
                'live' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Live ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $taxi_id = Ut_taxi::where(['status' => true, 'code' => $car_code])->value('id');

            if (!$taxi_id) {
                Log::channel('error')->error('Taxi Balance Transctions ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$taxi_id ---- Message : ' . ' Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }

            $live = $request->get('live');

            $taxi_live_id = Ut_taxi_live_history::insertGetId([
                'taxi' => $taxi_id,
                'live' => $live,
                'date' => date('Y-m-d H:i:s')
            ]);


            Ut_taxi::where('id', $taxi_id)
                ->update([
                    'live' => $live,
                ]);

        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Live ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }


    // taksiler arasi balans kocurme
    public function postTaxiMoveBalance(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'from_taxi_code' => 'required|string',
                'to_taxi_code' => 'required|string',
                'balance' => 'required|string',
            ]);

            $amount = (int)$request->get('balance');

            if ($validator->fails()) {
                Log::channel('error')->error('Taxi Move Balance ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $from_taxi_code = $request->get('from_taxi_code');
            $from_taxi = Ut_taxi::where(['status' => true, 'code' => $from_taxi_code])->get()->last();
            if (!$from_taxi) {
                Log::channel('error')->error('Taxi Move Balance---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$from_taxi ---- Message : ' . ' Pul göndərən taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Pul göndərən taksi tapılmadı']);
            }

            $fromAccount = Ut_account::where(['type' => 1, 'destination' => $from_taxi->id])->get()->last();
            if (!$fromAccount) {
                Log::channel('error')->error('Taxi Move Balance ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$fromAccount ---- Message : ' . ' Pul göndərən taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Pul göndərən taksi tapılmadı']);
            }

            if ($fromAccount->balance < $amount) {
                Log::channel('error')->error('Taxi Move Balance ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$fromAccount->balance < $amount ---- Message : ' . ' Pul göndərən taksinin balansı kiyafət qədər deyil');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Pul göndərən taksinin balansı kiyafət qədər deyil']);
            }

            $to_taxi_code = $request->get('to_taxi_code');
            $to_taxi = Ut_taxi::where(['status' => true, 'code' => $to_taxi_code])->get()->last();
            if (!$to_taxi) {
                Log::channel('error')->error('Taxi Move Balance ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$to_taxi ---- Message : ' . ' Pulu qəbul edən taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Pulu qəbul edən taksi tapılmadı']);
            }

            $toAccount = Ut_account::where(['type' => 1, 'destination' => $to_taxi->id])->get()->last();
            if (!$toAccount) {
                Log::channel('error')->error('Taxi Move Balance ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . '$toAccount ---- Message : ' . ' Pulu qəbul edən taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Pulu qəbul edən taksi tapılmadı']);
            }


            date_default_timezone_set('Asia/Baku');

            $date = Carbon::now();

            DB::table('ut_transaction')
                ->insert([
                    'user' => 13,
                    'from_account' => $fromAccount->id,
                    'from_account_type' => 1,
                    'to_account' => $toAccount->id,
                    'to_account_type' => 1,
                    'type' => 6,
                    'amount' => $amount,
                    'date' => $date,
                    'description' => 'Pul köçürmə əməliyyatı',
                ]);


            $fromBalance = $fromAccount->balance - $amount;

            $toBalance = $toAccount->balance + $amount;

            Ut_account::where(['type' => 1, 'destination' => $from_taxi->id])
                ->update([
                    'balance' => $fromBalance,
                ]);

            Ut_account::where(['type' => 1, 'destination' => $to_taxi->id])
                ->update([
                    'balance' => $toBalance,
                ]);

            FcmController::notification(3, $from_taxi->fcm_registered_id, 'Ulduz Taxi', 'Metn', 'Balansınızdan ' . $amount . ' AZN çıxıldı');

            FcmController::notification(3, $to_taxi->fcm_registered_id, 'Ulduz Taxi', 'Metn', 'Balansınıza ' . $amount . ' AZN əlavə edildi');

        } catch
        (\Exception $e) {
            Log::channel('error')->error('Taxi Move Balance ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }

}

