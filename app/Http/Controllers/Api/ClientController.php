<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\User;
use App\Ut_call;
use App\Ut_customer;
use App\Ut_order;
use App\Ut_order_detail;
use App\Ut_order_status_history;
use App\Ut_price_strategy;
use App\Ut_tariff;
use App\Ut_taxi;
use Auth;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Validator;

class ClientController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }


    public function getFc(Request $request)
    {
        FcmClientController::notification(1, 'dPf2VsZN9ac:APA91bFWAp4ZRCGwPwkBYhDtIQ2bd6DpstzjUys2I182rKcOu8eGtbnrKImHJ3UuOWzERNRpQASFiW_o2bi0o5Z4VYwd7nFuVd-Bgr8G83Nwownq8xxY9eqz-e4QEfkvbi9rrZSQLVKN', 'Ulduz Taxi', 'Sizə taksi təyin edildi', 'Test');

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);
    }

    public function getCustomerDetail(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Customer Detail ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $phone = $request->get('phone');

            $customer = Db::table('ut_customer as ut_c')
                ->join('ut_customer_group as ut_cg', 'ut_c.group', 'ut_cg.id')
                ->where(['ut_c.phone' => $phone, 'ut_c.status' => true, 'ut_c.delete' => false, 'ut_c.banned' => false])
                ->select('ut_c.*', 'ut_cg.name as group_name')
                ->first();

            $tariffs = Ut_tariff::where(['status' => true, 'delete' => false])->get();

            $customer_orders = [];

            if ($customer) {
                $customer_orders = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['customer' => $customer->id, 'status' => 4, 'delete' => false])->get();

                $customer_last_order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['customer' => $customer->id, 'delete' => false])
                    ->whereNotIn('status', [4, 35])
                    ->get()->last();

                if ($customer_last_order) {
                    $customer_last_order = StaticController::getResultFutureOrPublicOrListen($customer_last_order);
                }
            }


        } catch (\Exception $e) {
            Log::channel('error')->error('Customer Detail ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json([
            'status' => 'true',
            'error' => '',
            'success' => 200,
            'about' => 'Lorem ipsum',
            'customer' => $customer ?? [],
            'customer_orders' => $customer_orders,
            'customer_last_order' => $customer_last_order ?? [],
        ]);

    }

    public function postClientLogin(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'device_id' => 'required|string',
                'phone' => 'required|string',
                'fcm_registered_id' => 'required|string',
                'source' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Client Login ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }


            $phone = $request->get('phone');
            $device_id = $request->get('device_id');
            $fcm_registered_id = $request->get('fcm_registered_id');
            $source = $request->get('source');

            $password = mt_rand(100000, 999999);
            $token = Str::random(16);

            $customer = Ut_customer::where(['phone' => $phone])->first();
            if (!$customer) {
                Log::channel('error')->error('Client Login ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $customer Message : İstifadəçi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => '', 'success' => 404, 'result' => [], 'message' => 'İstifadəçi tapılmadı']);
            }

            if ($customer->status == false || $customer->delete == true || $customer->banned == true) {
                Log::channel('error')->error('Client Login ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $customer->status, $customer->delete, $customer->banned, Message: Bu istifadəçi deaktiv edilib');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => '', 'success' => 404, 'result' => [], 'message' => 'Bu istifadəçi deaktiv edilib']);
            }

            $sms = new  ClientSmsController();
            $smsCode = $sms->sendSms($phone);

            $data = [
                'device_id' => $device_id,
                'phone' => $phone,
                'fcm_registered_id' => $fcm_registered_id,
                'source' => $source,
                'password' => $password,
                'token' => $token,
                'date' => date('Y-m-d H:i:s'),
                'sms_code' => $smsCode,
            ];

            if (!$customer) {
                $customer_id = Ut_customer::insertGetId($data);
            } else {
                Ut_customer::where('phone', $phone)
                    ->update($data);
            }


        } catch (\Exception $e) {
            Log::channel('error')->error('Client Login ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true, 'smsCode' => $smsCode]);

    }

    public function postClientDetailUpdate(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'language' => 'required|string',
                'home_lat' => 'required|string',
                'home_lng' => 'required|string',
                'home_name' => 'required|string',
                'work_lat' => 'required|string',
                'work_lng' => 'required|string',
                'work_name' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Client Detail Update ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $phone = $request->get('phone');
            $firstname = $request->get('firstname');
            $lastname = $request->get('lastname');
            $language = $request->get('language') ?? 1;

            $home_lat = $request->get('home_lat') ?? 0.0;
            $home_lng = $request->get('home_lng') ?? 0.0;
            $home_name = $request->get('home_name') ?? '';

            $work_lat = $request->get('work_lat') ?? 0.0;
            $work_lng = $request->get('work_lng') ?? 0.0;
            $work_name = $request->get('work_name') ?? '';

            $customer = Ut_customer::where(['phone' => $phone, 'status' => true, 'delete' => false, 'banned' => false])->first();
            if (!$customer) {
                Log::channel('error')->error('Client Detail Update ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $customer Message : Client tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Client tapılmadı']);
            }

            Ut_customer::where(['phone' => $phone, 'status' => true, 'delete' => false, 'banned' => false])
                ->update([
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'language' => $language,
                    'home_lat' => $home_lat,
                    'home_lng' => $home_lng,
                    'home_name' => $home_name,
                    'work_lat' => $work_lat,
                    'work_lng' => $work_lng,
                    'work_name' => $work_name,
                    'date' => date('Y-m-d H:i:s')
                ]);


            $result = Ut_customer::where(['phone' => $phone])->first();

            if (!$result) {
                Log::channel('error')->error('Client Detail Update ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $result Message : Client tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Client tapılmadı']);
            }

        } catch (\Exception $e) {
            Log::channel('error')->error('Client Detail Update ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => $result]);

    }

    public function getClientSearchAddress(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'text' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Client Search Address ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $text = $request->get('text');

            $results = $this->getSearchAddressResult($text);

        } catch (\Exception $e) {
            Log::channel('error')->error('Client Search Address ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $results]);
    }

    public function replaceUtf8($text)
    {

        $a = array('E', 'İ', 'U', 'O', 'G', 'S', 'C', 'e', 'i', 'u', 'o', 'g', 's', 'c');

        $b = array('Ə', 'I', 'Ü', 'Ö', 'Ğ', 'Ş', 'Ç', 'ə', 'ı', 'ü', 'ö', 'ğ', 'ş', 'ç');

        return str_replace($a, $b, $text);
    }

    public function getSearchAddressResult($text)
    {

        $text_az = $this->replaceUtf8($text);

        $results = DB::select('
        
        SELECT
    ut_objects.id,
  CONCAT(ut_objects.name, IFNULL(CONCAT(\' \', ut_objects.street), \'\')) as name,
  \'1\' as type,
  \'1\' as tourniquet_type,
  ut_objects.latitude,
  ut_objects.longitude,
  ut_object_tourniquets.price,
  ut_objects.priority
FROM ut_objects
LEFT JOIN ut_region on ut_region.id=ut_objects.region
LEFT JOIN ut_city on ut_city.id=ut_objects.city
LEFT JOIN ut_district on ut_district.id=ut_objects.district
LEFT JOIN ut_object_tourniquets on ut_object_tourniquets.object_id=ut_objects.id
WHERE ut_objects.name LIKE "%' . $text . '%"
OR ut_objects.name LIKE "%' . $text_az . '%"



UNION ALL

SELECT
    ut_skat_addresses_filtered.id,
  ut_skat_addresses_filtered.name as name,
  \'1\' as type,
  \'2\' as tourniquet_type,
  ut_skat_addresses_filtered.latitude,
  ut_skat_addresses_filtered.longitude,
  0 as price,
  ut_skat_addresses_filtered.priority
FROM ut_skat_addresses_filtered
LEFT JOIN ut_city on ut_city.id=ut_skat_addresses_filtered.city_id
WHERE ut_skat_addresses_filtered.name LIKE "%' . $text . '%"
OR ut_skat_addresses_filtered.name LIKE "%' . $text_az . '%"


ORDER BY priority desc
LIMIT 30;
        
        
        ');

        return $results;


    }


    public function getClientCalculatePrice(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'km' => 'required|string',
                'customer_phone' => 'required|string',
                'destinations' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Client Calculate Price ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $km = $request->get('km');
            $customerPhone = $request->get('customer_phone');
            $destinations = json_decode($request->get('destinations'), true);

            $date = date('Y-m-d H:i:s');
            $orderTime = explode(' ', $date)[1];

            $customer = Ut_customer::where('phone', $customerPhone)->where('status', true)->where('delete', false)->first();
            if (!$customer) {
                Log::channel('error')->error('Client Calculate Price ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $customer Message : Client tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");

                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Client tapılmadı']);
            }

            $tariffPrices = Ut_tariff::where('status', true)->where('delete', false)->get();

            $results = [];

            foreach ($tariffPrices as $tariffPrice) {
                $price = 0;

                $plan_for_distance = json_decode($tariffPrice->plan_for_distance, true);

                $isPresentsInPlan = false;
                $planOrderDistance = $km; //km = 5.586
                foreach ($plan_for_distance as $plan) {
                    if ($km >= $plan['start']) { // 23.373 > 0
                        $isPresentsInPlan = true;
                        if ($km >= $plan['end']) { // 23.373 >= 3
                            $tmpDistance = $plan['end'] - $plan['start']; // ilk => 3 - 0 = 3 // ikinci => 10 - 3 =7
                            $planOrderDistance -= $tmpDistance; // 5.586-3 = 2.86
                            if ($plan['fix']) $price += $plan['price']; // ilk => 0 + 3 = 3 //ikinci =>
                            else $price += $tmpDistance * $plan['price']; // 1 *5 = 5
                        } else {
                            if ($plan['fix']) $price += $plan['price'];
                            else $price += $planOrderDistance * $plan['price'];
                        }
                    }
                }

                if (!$isPresentsInPlan) {
                    if ($plan_for_distance[count($plan_for_distance) - 1]['fix']) $price += $plan_for_distance[count($plan_for_distance) - 1]['price'];
                    else $price += $km * $plan_for_distance[count($plan_for_distance) - 1]['price'];
                }

                if (count($destinations) - 2 > 0) $price += ((count($destinations) - 2) * $tariffPrice->per_destination_fee) / 100;


//            if ($options) {
//                foreach ($options as $option) {
//                    $optionPrice = Ut_options::where('id', $option)->where('status', true)->where('delete', false)->value('price');
//                    $price += $optionPrice;
//                }
//            }

                //eger gozleme muddeti 10 deq artiqdirsa qalan deqiqeleri price ustune gelir
//            if ($timeout && $timeout > 10) {
//                $price += ($timeout - 10) * $tariffPrice->timeout_fee;
//            }

                // eger turniket girisi varsa price ustune gelir
//            for ($i = 0; $i < count($tourniquetWillPays); $i++) {
//                $price += $tourniquetWillPays[$i] * $tourniquetPrices[$i];
//            }

                //eger nese kompaniya filan avrsa price ustune gelir
                $priceStrategy = Ut_price_strategy::where(['tariff_id' => $tariffPrice->id, 'delete' => false])
                    ->where('date', $date)
                    ->where('start_time', '<', $orderTime)
                    ->where('end_time', '>', $orderTime)
                    ->first();

                if ($priceStrategy) {
                    if ($priceStrategy->is_fix_or_percent) {
                        $price -= ($price * $priceStrategy->discount) / 100;
                    } else {
                        $price -= $price * $priceStrategy->discount;
                    }
                }

                if ($customer && $customer->group == 1 && $customer->discount > 0) {
                    if ($customer->is_increase_discount) {
                        $price += ($price * $customer->discount) / 100;
                    } else {
                        $price -= ($price * $customer->discount) / 100;
                    }
                }

                if ($customer && $customer->group != 1) {
                    $customerGroup = $customer->groupName;
                    if ($customerGroup && $customerGroup->discount > 0)
                        if ($customer->is_increase_discount) {
                            $price += ($price * $customerGroup->discount) / 100;
                        } else {
                            $price -= ($price * $customer->discount) / 100;
                        }
                }

                $price = number_format($price, 2, '.', ' ');

                $payment_method = $customer && $customer->group == 1 ? 1 : 2;

                $results[] = [
                    'id' => $tariffPrice->id,
                    'name' => $tariffPrice->name,
                    'price' => $price,
                    'payment_method' => $payment_method
                ];

            }

        } catch (\Exception $e) {
            Log::channel('error')->error('Client Calculate Price ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $results]);

    }

    public function postClientOrder(Request $request)
    {
        date_default_timezone_set('Asia/Baku');

        try {

            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
                'tariff' => 'required|string',
                'payment_method' => 'required|string',
                'km' => 'required|string',
                'price' => 'required|string',
                'routes' => 'required|string',
                'lat' => 'required|string',
                'lng' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Client Order ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $phone = $request->get('phone');
            $tariff = $request->get('tariff');
            $payment_method = $request->get('payment_method');
            $km = $request->get('km');
            $price = $request->get('price');
            $orderDate = date('Y-m-d H:i:s');
            $routes = $request->get('routes');
            $lat = $request->get('lat');
            $lng = $request->get('lng');

            $tariff = Ut_tariff::where('id', $tariff)->where('status', true)->where('delete', false)->first();

            if (!$tariff) {
                Log::channel('error')->error('Client Order ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $tariff Message : Tariff tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Tariff tapılmadı']);
            }

            $customer = Ut_customer::where(['delete' => false, 'status' => true, 'phone' => $phone])->get()->last();
            if (!$customer) {
                Log::channel('error')->error('Client Order ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $customer Message : Müştəri tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Müştəri tapılmadı']);
            }

            $routes = json_encode($routes);
            $routes = json_decode($routes, true);

            $order_id = Ut_order::insertGetId([
                'taxi' => 0,
                'test' => false,
                'color' => $payment_method ? '' : '',
                'auto_search' => 1,
                'source' => 0,
                'public' => 0,
                'status' => 0,
                'is_edited' => 0,
                'important' => 0,
                'customer' => $customer->id,
                'created_at' => date('Y-m-d H:i:s'),
                'user_id' => 13,
                'last_xref_id' => 2,
                'sort' => 0,
            ]);

            if ($order_id) {
                $order_detail_id = Ut_order_detail::insertGetId([
                    'order_id' => $order_id,
                    'route' => $routes,
                    'price' => $price,
                    'order_type' => 1,
                    'order_value' => $km,
                    'tariff' => $tariff->id,
                    'order_date' => $orderDate,
                    'timeout' => 10,
                    'user_id' => 13,
                    'payment_method' => $payment_method,
                    'date' => date('Y-m-d H:i:s'),
                ]);


                $location = json_encode(['latitude' => $lat[0], 'longitude' => $lng[0]]);


                $order_status_id = Ut_order_status_history::insertGetId([
                    'order' => $order_id,
                    'taxi' => 0,
                    'user_id' => 13,
                    'status' => 0,
                    'reason' => 'Sifariş yaradıldı ve taksi axtarır',
                    'date' => date('Y-m-d H:i:s'),
                    'location' => $location
                ]);

                $order_status_id = Ut_call::insertGetId([
                    'number' => $customer->phone,
                    'order_id' => $order_id,
                    'callid' => 1,
                    'date' => date('Y-m-d H:i:s'),
                ]);


                $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName', 'orderDetailName.userName', 'statusChangesLast', 'taxiName', 'taxiName.colorName', 'taxiName.brandName', 'taxiName.modelName_'])->where(['delete' => false, 'id' => $order_id])->first();
                $order = StaticController::getResultFutureOrPublicOrListen($order, 0);
                broadcast(new \App\Events\OrderEvent($order, 0));

                $postFindTaxiTest = new OrderController();
                $postFindTaxiTest->postFindTaxiTest();

            }
        } catch
        (\Exception $e) {
            Log::channel('error')->error('Client Order ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => true]);

    }


    public function getClientTaxiCoordinate(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Client Taxi Coordinate ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $car_code = $request->get('code');
            $results = Ut_taxi::select('latitude', 'longitude', 'bearing')->where(['status' => true, 'code' => $car_code])->get()->last();

            if (!$results) {
                Log::channel('error')->error('Client Taxi Coordinate ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $results Message : Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }


        } catch (\Exception $e) {
            Log::channel('error')->error('Client Taxi Coordinate ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $results]);
    }


    public function getNearlyTaxies(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'lat' => 'required|string',
                'lng' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Nearly Taxies ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $km = 3;
            $lat = $request->get('lat');
            $lng = $request->get('lng');

            $away3LatMax = (number_format(($lat + $km), 2, '.', ""));
            $away3LatMin = (number_format(($lat - $km), 2, '.', ""));
            $away3LongMax = (number_format(($lng + $km), 2, '.', ""));
            $away3LongMin = (number_format(($lng - $km), 2, '.', ""));

            $results = DB::table('ut_taxi as ut_t')
                ->where([
                    'ut_t.delete' => false,
                    'ut_t.status' => true,
                    'ut_t.action' => false,
//                    'ut_t.live' => true,
                ])
                ->where(function ($q) use ($away3LatMax, $away3LatMin, $away3LongMax, $away3LongMin) {
                    $q->where(
                        [
                            ['ut_t.latitude', '<', $away3LatMax],
                            ['ut_t.latitude', '>', $away3LatMin],
                            ['ut_t.longitude', '<', $away3LongMax],
                            ['ut_t.longitude', '>', $away3LongMin]
                        ]);
                    $q->orWhere(
                        [
                            ['ut_t.fake_latitude', '<', $away3LatMax],
                            ['ut_t.fake_latitude', '>', $away3LatMin],
                            ['ut_t.fake_longitude', '<', $away3LongMax],
                            ['ut_t.fake_longitude', '>', $away3LongMin]
                        ]);
                })
                ->limit(10)
                ->get();

            if (!$results) {
                Log::channel('error')->error('Nearly Taxies ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $results Message : Taksi tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Taksi tapılmadı']);
            }


        } catch (\Exception $e) {
            Log::channel('error')->error('Nearly Taxies ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'results' => $results]);
    }

    public function postCancelOrder(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'phone' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::channel('error')->error('Cancel Order ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $validator->errors());
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $phone = $request->get('phone');

            $customer = Ut_customer::where(['delete' => false, 'status' => true, 'phone' => $phone])->get()->last();
            if (!$customer) {
                Log::channel('error')->error('Cancel Order ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $results Message : Müştəri tapılmadı');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 403, 'message' => 'Müştəri tapılmadı']);
            }

            $order = Ut_order::with('orderDetailName')->where('customer', $customer->id)->whereNotIn('status', [8, 4])->get()->last();
            if (!$order) {
                Log::channel('error')->error('Cancel Order ---- Validator ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . ' $order Message : Sifaris tapilmadi');
                Log::channel('error')->info("----------------------------------------------------------------------");
                return response()->json(['status' => 'false', 'error' => 'Sifaris tapilmadi']);
            }

            $taxi = Ut_taxi::where(['status' => true, 'id' => $order->taxi])->get()->last();

            $order_status_id = Ut_order_status_history::insertGetId([
                'order' => $order->id,
                'taxi' => $taxi ? $taxi->id : 0,
                'user_id' => 13,
                'status' => 35,
                'reason' => 'Sifariş müştəri tərəfindən ləğv olundu',
                'date' => date('Y-m-d H:i:s'),
                'location' => $order->orderDetailName->locationName()
            ]);


            Ut_order::where('id', $order->id)
                ->update([
                    'status' => 35,
                    'sort' => 7,
                ]);

            if ($taxi) {
                Ut_taxi::where('id', $taxi->id)
                    ->update([
                        'action' => 0,
                    ]);

                FcmController::notification(35, $taxi->fcm_registered_id, 'Basliq', 'Metn', $order);
            }

            if ($order->public) {
                $orderOpens = StaticController::getTaxiOrderPublicOrFuture(600);

                FcmController::notification(600, '/topics/taxi', 'Basliq', 'Metn', $orderOpens);
            }


        } catch (\Exception $e) {
            Log::channel('error')->error('Cancel Order ---- Exception ---- Log tarixi : ' . date("Y-md-d h:i:s") . '-----' . 'Error : ' . $e->getMessage() . '-----' . 'Error : ' . 'line : ' . $e->getLine());
            Log::channel('error')->info("----------------------------------------------------------------------");
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json([
            'status' => 'true',
            'error' => '',
            'success' => 200,
        ]);

    }


}
