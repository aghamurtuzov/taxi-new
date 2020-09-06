<?php

namespace App\Http\Controllers;

use App\Blog;
use App\Http\Controllers\Api\FcmController;
use App\Http\Requests\TaxiBlockedRequest;
use App\Http\Requests\TaxiCategoryRequest;
use App\Http\Requests\TaxiCharacteristicsRequest;
use App\Http\Requests\TaxiDriverRequest;
use App\Http\Requests\TaxiRequest;
use App\Modules;
use App\User;
use App\Ut_account;
use App\Ut_banned_taxi;
use App\Ut_body;
use App\Ut_brand;
use App\Ut_colors;
use App\Ut_device;
use App\Ut_driver_language;
use App\Ut_driver_setting;
use App\Ut_fuel;
use App\Ut_messages;
use App\Ut_model;
use App\Ut_options;
use App\Ut_order;
use App\Ut_priority_transactions;
use App\Ut_setting;
use App\Ut_tariff;
use App\Ut_taxi;
use App\Ut_taxi_categories;
use App\Ut_transaction;
use Auth;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
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
        App::setLocale('az');
    }


    public function testSearchTaxi($order, $km, $lat, $lng, $bannedTaxi = [], $taxi_future_id = false)
    {
        $away3LatMax = (number_format(($lat + $km), 2, '.', ""));
        $away3LatMin = (number_format(($lat - $km), 2, '.', ""));
        $away3LongMax = (number_format(($lng + $km), 2, '.', ""));
        $away3LongMin = (number_format(($lng - $km), 2, '.', ""));

        $price = $order->orderDetailName ? $order->orderDetailName->price + $order->orderDetailName->operator_price : 0; //4.27
        //tarifin faizi
        $distance_fee = $order->orderDetailName ? $order->orderDetailName->tariffName->distance_fee : 0; //10
        //cixilacaq mebleb
        $fee = round(($price * $distance_fee) / 100, 2); //0.472

        $taxi = [];
        $results = [];
        $categories = [];
        if ($taxi_future_id) {
            $taxi = Ut_taxi::where(['id' => $taxi_future_id])->first();
        } else {

            $fcm_taxies = DB::table('ut_taxi as ut_t')
                ->join('ut_accounts as ut_a', 'ut_t.id', 'ut_a.destination')
                ->where([
                    'ut_t.delete' => false,
                    'ut_t.status' => true,
                    'ut_t.action' => false,
                    'ut_t.live' => true,
                    'ut_a.type' => 1
                ])
                ->where('ut_a.balance', '<', $fee)
                ->whereNotIn('ut_t.id', $bannedTaxi)
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
                ->select('ut_t.*', 'ut_a.type', 'ut_a.balance')
                ->limit(20)->get();

            $results = DB::table('ut_taxi as ut_t')
                ->join('ut_accounts as ut_a', 'ut_t.id', 'ut_a.destination')
                ->where([
                    'ut_t.delete' => false,
                    'ut_t.status' => true,
                    'ut_t.action' => false,
                    'ut_t.live' => true,
                    'ut_a.type' => 1
                ])
                ->where('ut_a.balance', '>', $fee)
                ->whereNotIn('ut_t.id', $bannedTaxi)
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
                ->select('ut_t.*', 'ut_a.type', 'ut_a.balance')
                ->limit(20)->get();


            $resultsCollection = collect($results);

            $categories = Ut_taxi_categories::orderBy('sort', 'ASC')->get();

            if (count($results)) {
                foreach ($categories as $category) {
                    $filtered = $resultsCollection->where('category', $category->id);

                    if (count($filtered)) {
                        //priority si en cox olan taxi
                        $maxPriorityTaxi = $filtered->sortBy('priority')->values()->last();
                        return $maxPriorityTaxi->id;

                        // priority eyni olarsa
                        $samePriorityTaxies = $resultsCollection->filter(function ($value, $key) use ($maxPriorityTaxi) {
                            if ($value->priority == $maxPriorityTaxi->priority) {
                                return true;
                            }
                        });

                        $samePriorityTaxies->all();

                        //yaxin olan taxi
                        if (count($samePriorityTaxies)) {
                            $maxKilometer = 0;
                            $maxKilometerTaxi = null;
                            //en yaxin olani secmek
                            foreach ($samePriorityTaxies as $samePriorityTaxy) {
                                $samePriorityTaxy->latitude = number_format($samePriorityTaxy->latitude, 2, '.', "");
                                $samePriorityTaxy->longitude = number_format($samePriorityTaxy->longitude, 2, '.', "");
                                $kilometer = number_format((sqrt(pow(abs($samePriorityTaxy->latitude - $lat), 2) + pow(abs($samePriorityTaxy->longitude - $lng), 2))), 2, '.', "");
                                if ($kilometer > $maxKilometerTaxi) {
                                    $maxKilometer = $kilometer;
                                    $maxKilometerTaxi = $samePriorityTaxy;
                                }
                                $taxi[$samePriorityTaxy->id]['taxi'] = $samePriorityTaxy;
                            }

                            $taxi = $maxKilometerTaxi;

                        } else {
                            $taxi = $maxPriorityTaxi;
                        }
                        break;
                    }
                }
            }
        }

        if ($taxi) {
            StaticController::findedTaxi($taxi, $order, $lat, $lng, 0);
            FcmController::notification(1, $taxi->fcm_registered_id, 'Basliq', 'Metn', $order);
        }

        //fcm gedecek pulu catmayan taxilere
        foreach ($fcm_taxies as $fcm_taxy) {
            FcmController::notification(5000, $fcm_taxy->fcm_registered_id, 'Ulduz taxi', 'Metn', $order);
        }


        return ['taxi' => $taxi, 'results' => $results, 'categories' => $categories];

    }

    public function getTaxi()
    {

//        $reached_time = Ut_setting::where('setting_key', 'reached_time')->value('setting_value');
//
//        $to = Carbon::now()->addMinutes($reached_time)->format('Y-m-d H:i:s');
//
//        $order = Ut_order::with(['orderDetailName', 'orderDetailName.tariffName'])->join('ut_order_detail as utd', 'ut_order.id', 'utd.order_id')
//            ->whereIn('ut_order.status', [0, 700])
//            ->where('utd.order_date', '<', $to)
//            ->where('ut_order.taxi', false)
//            ->where('ut_order.delete', false)
//            ->select('ut_order.*', 'utd.order_date')
//            ->first();
//
//        if ($order) {
//            $orderStatusBannedTaxi = explode(',', $order->refused_taxies);
//
//            $km = Ut_setting::where('setting_key', 'order_radius')->value('setting_value') / 100;
//
//            $location = json_decode($order->orderDetailName->route, true)[0];
//
//            $taxi = $this->testSearchTaxi($order, $km,$location['lat'], $location['lng'], $orderStatusBannedTaxi, $taxi_future_id = false);
//        }
//        return $taxi;


        $results = Ut_taxi::with(['brandName', 'colorName', 'categoryName', 'account'])->where('delete', false)->paginate(20);

        $colors = Ut_colors::where('delete', false)->get();

        $module = Modules::where('table_name', 'ut_taxi')->first();

        $brands = Ut_brand::where('delete', false)->get();

        $models = Ut_model::where('delete', false)->get();

        $bodies = Ut_body::where('delete', false)->get();

        $fuels = Ut_fuel::where('delete', false)->get();

        $tariffs = Ut_tariff::where('delete', false)->where('status', true)->get();

        $categories = Ut_taxi_categories::where('delete', false)->get();

        $driver_languages = Ut_driver_language::where('delete', false)->get();

        $options = Ut_options::where('delete', false)->get();

        return view('taxi.taxi', [
            'results' => $results,
            'module' => $module,
            'brands' => $brands,
            'models' => $models,
            'bodies' => $bodies,
            'colors' => $colors,
            'fuels' => $fuels,
            'tariffs' => $tariffs,
            'categories' => $categories,
            'driver_languages' => $driver_languages,
            'options' => $options,
        ]);
    }

    public function getTaxiView($id)
    {
        $result = Ut_taxi::where('id', $id)->where('delete', false)->first();
        $transactionsFrom = [];
        $transactionsTo = [];

        if ($result) {
            $result->phone_prefix = substr($result->phone, 3, 2);
            $result->phone = substr($result->phone, 5);

            $result->mobile_prefix = substr($result->mobile, 3, 2);
            $result->mobile = substr($result->mobile, 5);
        }

        $priorities = Ut_priority_transactions::where('delete', false)->where('taxi_id', $id)->get();

        $messages = Ut_messages::where('delete', false)->where('destination_id', $id)->where('destination_type', 1)->get();

        $module = Modules::where('table_name', 'ut_taxi')->first();


        $account = Ut_account::where('type', 1)->where('destination', $id)->first();
        if ($account) {
            $transactionsFrom = Ut_transaction::where('delete', false)->where('from_account', $account->id)->orderBy('id', 'DESC')->paginate(20);
            $transactionsTo = Ut_transaction::where('delete', false)->where('to_account', $account->id)->orderBy('id', 'DESC')->paginate(20);
        }

        $data = [
            'result' => $result,
            'module' => $module,
            'priorities' => $priorities,
            'messages' => $messages,
            'transactionsFrom' => $transactionsFrom,
            'transactionsTo' => $transactionsTo,
        ];

        return view('taxi.taxi-view', $data);
    }


    public function getTaxiEdit($id)
    {
        $result = Ut_taxi::where('id', $id)->where('delete', false)->first();

        if ($result) {
            $result->phone_prefix = substr($result->phone, 3, 2);
            $result->phone = substr($result->phone, 5);

            $result->mobile_prefix = substr($result->mobile, 3, 2);
            $result->mobile = substr($result->mobile, 5);
        }


        $colors = Ut_colors::where('delete', false)->get();

        $brands = Ut_brand::where('delete', false)->get();

        $models = Ut_model::where('delete', false)->get();

        $bodies = Ut_body::where('delete', false)->get();

        $fuels = Ut_fuel::where('delete', false)->get();

        $tariffs = Ut_tariff::where('delete', false)->get();

        $categories = Ut_taxi_categories::where('delete', false)->get();

        $driver_languages = Ut_driver_language::where('delete', false)->get();

        $devices = Ut_device::where('delete', false)->get();

        $options = Ut_options::where('delete', false)->get();

        $module = Modules::where('table_name', 'ut_taxi')->first();

        $code = DB::table('ut_taxi')->latest('code')->value("code");

        $code += 1;

        $code = strlen($code) == 1 ? '00' . $code : (strlen($code) == 2 ? '0' . $code : $code);

        $data = [
            'result' => $result,
            'module' => $module,
            'colors' => $colors,
            'brands' => $brands,
            'models' => $models,
            'bodies' => $bodies,
            'fuels' => $fuels,
            'tariffs' => $tariffs,
            'categories' => $categories,
            'devices' => $devices,
            'driver_languages' => $driver_languages,
            'options' => $options,
            'code' => $code
        ];


        if ($result) {
            return view('taxi.taxi-edit', $data);
        } else {
            return view('taxi.taxi-new', $data);
        }
    }

    public function postTaxiEdit(TaxiRequest $request, $id, $code)
    {
        $request->validated();

        $default_priority = Ut_setting::where('setting_key', 'default_priority')->value('setting_value');

        $request->merge([
            'tariff' => implode(',', $request->tariff),
            'option' => $request->option ? implode(',', $request->option) : '',
            'language' => $request->language ? implode(',', $request->language) : '',
            'phone' => '994' . $request->phone_prefix . $request->phone,
            'mobile' => '994' . $request->mobile_prefix . $request->mobile,
            'date' => Carbon::now(),
            'free' => $request->free,
            'transmission' => 1,
            'priority' => $default_priority,
        ]);

        $request->request->remove('phone_prefix');
        $request->request->remove('mobile_prefix');

        InsertOrUpdateController::postModuleEdit($request->all(), $id, $code, "Taksi ");

        return Redirect::back();
    }

    public function getTaxiDriverSetting($id)
    {
        $result = Ut_driver_setting::where('taxi_id', $id)->where('delete', false)->first();

        $module = Modules::where('table_name', 'ut_driver_setting')->first();

        return view('taxi.taxi-driver-setting', ['result' => $result, 'module' => $module, 'id' => $id]);
    }

    public function postTaxiDriverSetting(TaxiDriverRequest $request, $id, $code)
    {
        $request->validated();

        $request->merge([
            'navigator' => implode(',', $request->navigator),
            'show_price' => $request->show_price ? 1 : 0,
            'show_destination' => $request->show_destination ? 1 : 0,
            'offline_location' => $request->offline_location ? 1 : 0,
            'public_order_show_destination' => $request->public_order_show_destination ? 1 : 0,
            'public_order_show_price' => $request->public_order_show_price ? 1 : 0,
            'public_order_show_orign' => $request->public_order_show_orign ? 1 : 0,
            'future_order_show_destination' => $request->future_order_show_destination ? 1 : 0,
            'future_order_show_price' => $request->future_order_show_price ? 1 : 0,
            'future_order_show_orign' => $request->future_order_show_orign ? 1 : 0,
            'show_price_in_order' => $request->show_price_in_order ? 1 : 0,
            'show_time' => $request->show_time ? 1 : 0,
            'show_destination_in_order' => $request->show_destination_in_order ? 1 : 0,
            'show_distance' => $request->show_distance ? 1 : 0,
        ]);

        InsertOrUpdateController::postModuleEdit($request->all(), $id, $code, "Taksi parametrləri");

        return Redirect::back();
    }


    public function postTaxiDriverSettingStandard($id)
    {
        DB::table('ut_driver_setting')
            ->where('id', $id)
            ->update([
                'show_price' => 0,
                'show_destination' => 0,
                'navigator' => '1,2,3',
                'request_second' => 5,
                'order_radius' => 2,
                'offline_location' => 1,
                'public_order_show_destination' => 0,
                'public_order_show_price' => 0,
                'public_order_show_orign' => 0,
                'public_order_radius' => 2,
                'future_order_show_destination' => 0,
                'future_order_show_price' => 0,
                'future_order_show_orign' => 0,
                'future_order_radius' => 2,
                'show_price_in_order' => 0,
                'show_time' => 0,
                'show_destination_in_order' => 0,
                'show_distance' => 0,
            ]);

        Session::flash('success-message', 'Taksi parametrləri standarta gətirildi');

        return Redirect::back();
    }


//////////////////////////////////////////////////////// END Taxi Category /////////////////////////////////////////////
    public function getTaxiCategory()
    {
        $results = Ut_taxi_categories::where('delete', false)->get();

        $module = Modules::where('table_name', 'ut_taxi_categories')->first();

        return view('taxi.taxi-category', ['results' => $results, 'module' => $module]);
    }

    public function getTaxiCategoryEdit($id)
    {
        $result = Ut_taxi_categories::where('id', $id)->where('delete', false)->first();

        $module = Modules::where('table_name', 'ut_taxi_categories')->first();

        return view('taxi.taxi-category-edit', ['result' => $result, 'module' => $module]);
    }

    public function postTaxiCategoryEdit(TaxiCategoryRequest $request, $id, $code)
    {
        $request->validated();

        InsertOrUpdateController::postModuleEdit($request->all(), $id, $code, "Taksi kateqoriyası");

        return Redirect::back();
    }

//////////////////////////////////////////////////////// END Taxi Category /////////////////////////////////////////////

//////////////////////////////////////////////////////// Taxi Characteristic /////////////////////////////////////////////

    public function getTaxiCharacteristics()
    {
        $results = Ut_options::where('delete', false)->get();

        $module = Modules::where('table_name', 'ut_options')->first();

        return view('taxi.taxi-characteristics', ['results' => $results, 'module' => $module]);
    }

    public function getTaxiCharacteristicsEdit($id)
    {
        $result = Ut_options::where('id', $id)->where('delete', false)->first();

        $module = Modules::where('table_name', 'ut_options')->first();

        return view('taxi.taxi-characteristics-edit', ['result' => $result, 'module' => $module]);
    }

    public function postTaxiCharacteristicsEdit(TaxiCharacteristicsRequest $request, $id, $code)
    {
        $request->validated();

        InsertOrUpdateController::postModuleEdit($request->all(), $id, $code, "Taksi xarakteristikası");

        return Redirect::back();
    }

//////////////////////////////////////////////////////// END Taxi Characteristic /////////////////////////////////////////////


//////////////////////////////////////////////////////// Taxi Banned /////////////////////////////////////////////
    public function getTaxiBlocked()
    {
        $results = Ut_banned_taxi::with('taxiName')->get();

        $module = Modules::where('table_name', 'ut_banned_taxi')->first();

        return view('taxi.taxi-blocked', ['results' => $results, 'module' => $module]);
    }

    public function getTaxiBlockedEdit($id)
    {
        $result = Ut_taxi::where('delete', false)->where('id', $id)->first();

        if (!$result) {
            $result = [];
        }

        $module = Modules::where('table_name', 'ut_banned_taxi')->first();

        return view('taxi.taxi-blocked-edit', ['module' => $module, 'result' => $result]);
    }

    public function postTaxiBlockedEdit(TaxiBlockedRequest $request)
    {
        $request->validated();
        $date = Carbon::now();

        $taxi_id = $request->get('taxi_id');
        $description = $request->get('description');

        $taxi = Ut_taxi::where('delete', false)->where('id', $taxi_id)->first();

        if (!$taxi) {

            Session::flash('success-message', 'Sms göndərildi');

        } else {

            DB::table('ut_banned_taxi')->insert([
                'taxi_id' => $taxi_id,
                'status' => 1,
                'start_time' => $date,
                'date' => $date,
                'description' => $description,
            ]);

            Session::flash('success-message', 'Taksi Bloklandı');

        }


        return Redirect::back();

    }

    public function getTaxiMap($id = 0)
    {
        $categories = Ut_taxi_categories::where('delete', false)->get();

        $options = Ut_options::where('delete', false)->get();

        $module = Modules::where('table_name', 'ut_taxi')->first();

        if ($id) {
            $taxi = Ut_taxi::where('id', $id)->get()->last();
        } else {
            $taxi = false;
        }

        return view('taxi.taxi-map', [
            'module' => $module,
            'categories' => $categories,
            'options' => $options,
            'taxi' => $taxi,
        ]);
    }


    public function postTaxiMap(Request $request)
    {

        try {

            $validator = Validator::make($request->all(), [
                'taxi_category_id' => 'integer',
                'taxi_option_id' => 'integer',
                'taxi_id' => 'integer',
                'region_id' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'false', 'error' => 403, 'message' => $validator->errors()]);
            }

            $taxi_category_id = $request->get('taxi_category_id');
            $taxi_option_id = $request->get('taxi_option_id');
            $taxi_id = $request->get('taxi_id');
            $region_id = $request->get('region_id');
            $date = $request->get('date');

            $data = $this->getMapContent($taxi_category_id, $taxi_option_id, $taxi_id, $region_id, $date);

        } catch
        (\Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json([
            'status' => 'true',
            'error' => '',
            'success' => 200,
            'taxies' => $data['transactions'],
            'taxiInfo' => $data['taxiInfo']
        ]);
    }

    private function getMapContent($taxi_category_id = false, $taxi_option_id = false, $taxi_id = false, $region_id = false, $date = false)
    {
        date_default_timezone_set('Asia/Baku');


        $taxies = DB::table('ut_taxi as u')
            ->where('u.delete', false)
            ->where('u.status', true);

        if ($date) {
            $taxies->leftJoin('ut_taxi_geolocation as utg', 'u.id', 'utg.taxi_id')
                ->where(DB::raw("DATE_FORMAT(utg.date,'%Y-%m-%d %H:%i')"), '=', $date);
        } else {
            $taxies->where('u.last_coordinate_date', '>=', date('Y-m-d H:i:s', strtotime("-1 minutes")));
        }

        if ($taxi_category_id) {
            $taxies->where('u.category', $taxi_category_id);
        }

        if ($taxi_option_id) {
            $taxies->where('u.option', 'LIKE', '%' . $taxi_option_id . '%');
        }

        if ($taxi_id) {
            $taxies->where('u.id', $taxi_id);
        }

        if ($region_id) {
            $taxies->where('u.region_id', '!=', 0);
        }


        $taxies = $date ? $taxies->select('u.*', 'utg.latitude', 'utg.longitude')->limit(20)->get() : $taxies->select('u.*')->limit(20)->get();


        $transactions = collect($taxies);
        $transactions = $transactions->unique('id');

        $transactions->each(function ($item, $key) {
            $order = Ut_order::where(['delete' => false, 'taxi' => $item->id])->get()->last();
            if ($order) {
                $order_status = $order->status;
            } else {
                $order_status = null;
            }

            $item->order_status = $order_status;
            return true;
        });

        $taxi_free = $taxies->where('action', 0)->count();
        $taxi_not_free = $taxies->where('live', 0)->count();
        $taxi_accepted = DB::table('ut_taxi as t')
            ->join('ut_order as ut', 't.id', 'ut.taxi')
            ->where('ut.status', 2)
            ->where('t.delete', false)
            ->where('t.action', true)
            ->count();

        $taxi_reached = DB::table('ut_taxi as t')
            ->join('ut_order as ut', 't.id', 'ut.taxi')
            ->where('ut.status', 3)
            ->where('t.delete', false)
            ->where('t.action', true)
            ->count();

        $taxi_pickup = DB::table('ut_taxi as t')
            ->join('ut_order as ut', 't.id', 'ut.taxi')
            ->where('ut.status', 8)
            ->where('t.delete', false)
            ->where('t.action', true)
            ->count();


        $transactions->all();

        $taxiInfo = [
            'taxi_free' => $taxi_free,
            'taxi_not_free' => $taxi_not_free,
            'taxi_accepted' => $taxi_accepted,
            'taxi_reached' => $taxi_reached,
            'taxi_pickup' => $taxi_pickup,
            'taxi_all_taxi' => count($taxies),
        ];

        return ['transactions' => $transactions, 'taxiInfo' => $taxiInfo];
    }

    public function getModels(Request $request)
    {
        try {
            $models = Ut_model::where('delete', false)->where('brand', $request->get('brand'))->get();
        } catch
        (\Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json([
            'status' => 'true',
            'error' => '',
            'success' => 200,
            'models' => $models,

        ]);
    }


    //////////////////////////////////////////////////////// END Taxi Banned /////////////////////////////////////////////


    public function postTaxiTest(Request $request)
    {
        try {
            $taxi_id = $request->get('taxi_id');

            $taxi = Ut_taxi::where(['status' => true, 'id' => $taxi_id])->get()->last();

            $order = Ut_order::where('test', 1)->get()->last();

            if ($taxi) {
                FcmController::notification(1, $taxi->fcm_registered_id, 'Basliq', 'Metn', $order);
            }

        } catch
        (\Exception $e) {
            return response()->json(['status' => 'false', 'message' => $e->getMessage(), 'line' => $e->getLine()]);
        }

        return response()->json(['status' => 'true', 'error' => '', 'success' => 200, 'result' => true]);

    }


    public function getTaxiActionReset($id)
    {
        $result = Ut_taxi::findOrFail($id);

        $result->update([
            'action' => false,
        ]);

        return Redirect::back();
    }

}
