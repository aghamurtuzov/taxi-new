@extends('main.layout')
@section('content')



    <style>
        .search_result, #search_result_taxi {
            background: #FFF;
            border: 1px #ccc solid;
            border-top: 0px;
            width: 400px;
            max-height: 581px;
            overflow-y: scroll;
            display: none;
            position: absolute;
            z-index: 999;
        }

        .search_result li, #search_result_taxi li {
            list-style: none;
            padding: 5px 10px;
            margin: 0 0 0 -25px;
            color: #0896D3;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
        }

        .search_result li:hover, #search_result_taxi li:hover {
            background-color: #f5f5f5;
        }

        .selected {
            background: #FFE4C2;
        }

        .destination {
            padding: 10px 0;
            border: 1px dashed #bdb9b9;
            background: #f5f5f5;
            cursor: move;
        }
    </style>


    <div class="page-container">
        <div class="page-content">
            <form action="#" class="form-horizontal" autocomplete="off"
                  name="createForm" method="post" accept-charset="utf-8" id="form-order">
                <div class="sidebar sidebar-main sidebar-default">
                    <div class="sidebar-content">

                        <div class="sidebar-category sidebar-category-visible">
                            <div class="sidebar-category">

                                <div class="category-title">
                                    <span>Tarif</span>
                                </div>

                                <div class="category-content">
                                    <div class="form-group">
                                        <select name="tariff" class="select">
                                            @foreach($tariffs as $tariff)
                                                <option value="{{ $tariff->id }}">{{ $tariff->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                        {{--                        <div class="sidebar-category sidebar-category-visible">--}}
                        {{--                            <div class="sidebar-category">--}}

                        {{--                                <div class="category-title">--}}
                        {{--                                    <span>Sifarişin növü</span>--}}
                        {{--                                </div>--}}

                        {{--                                <div class="category-content">--}}
                        {{--                                    <div class="form-group">--}}
                        {{--                                        <select name="order_type" class="select">--}}
                        {{--                                            <option value="1">Adi</option>--}}
                        {{--                                            <option value="2">Vaxt</option>--}}
                        {{--                                        </select>--}}
                        {{--                                    </div>--}}
                        {{--                                </div>--}}

                        {{--                            </div>--}}

                        {{--                        </div>--}}
                        <div class="sidebar-category sidebar-category-visible">
                            <div class="sidebar-category">

                                <div class="category-title">
                                    <span>Ödəmə</span>
                                </div>

                                <div class="category-content">
                                    <div class="form-group">
                                        <select name="payment_method" class="select payment_method">
                                            <option value="1">Nəğd</option>
                                            <option value="2">Nəğdsiz</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="sidebar-category sidebar-category-visible">
                            <div class="sidebar-category">

                                <div class="category-title">
                                    <span>Xüsusiyyətlər</span>
                                </div>

                                <div class="category-content">
                                    <div class="form-group">
                                        <select name="options[]" class="select" multiple>
                                            @foreach($options as $option)
                                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-wrapper">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-white">
                                <div class="panel-body">
                                    <fieldset class="content-group">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <span style="margin-bottom: 20px;display: none;"
                                                      id="customer_corporative_label"
                                                      class="label label-primary"></span>

                                                <div class="form-group">

                                                    <label
                                                        class="control-label col-lg-2"><strong>Telefon</strong></label>
                                                    <div class="col-lg-4">
                                                        <input x-required type="text" name="customer_phone" value=""
                                                               id="customer_phone" class="form-control"
                                                               placeholder="994556985479" autocomplete="new-password"
                                                               maxlength="12" minlength="12"/>
                                                        <input type="hidden" name="customer_id" id="customer_id"
                                                               class="form-control" value=""/>
                                                    </div>
                                                    <label class="control-label col-lg-2"><strong>Adı</strong></label>
                                                    <div class="col-lg-4">
                                                        <input type="text" name="customer_name" value=""
                                                               id="customer_name" class="form-control"
                                                               placeholder="Məmməd" autocomplete="new-password">
                                                    </div>
                                                </div>
                                                <hr>
                                                <div id="destinations">
                                                    <div class="form-group destination">
                                                        <div class="col-lg-10 has-feedback has-feedback-left">
                                                            <input x-required data-is-first="1"
                                                                   data-lat="40.3880362"
                                                                   data-lng="49.838729"
                                                                   id="address-1"
                                                                   type="text"
                                                                   name="name"
                                                                   value=""
                                                                   class="address-1 form-control input-roundless addressInput"
                                                                   autocomplete="off" onclick="this.select();">
                                                            <input type="hidden" name="destination_id" value=""
                                                                   class="destination-id">
                                                            <input type="hidden" name="destination_type" value=""
                                                                   class="type">
                                                            <input type="hidden" name="latitude" value=""
                                                                   class="latitude">
                                                            <input type="hidden" name="longitude" value=""
                                                                   class="longitude">
                                                            <div class="form-control-feedback">
                                                                <i class="icon-pin-alt"></i>
                                                            </div>
                                                            <ul class="search_result"></ul>
                                                        </div>
                                                        <div class="col-lg-3 number_street" style="display: none">
                                                            <select name="number_street"
                                                                    class="form-control select-search">
                                                                <option value=""></option>
                                                            </select></div>
                                                        <div class="col-lg-3 object_tourniquet" style="display: none">
                                                            <div
                                                                class="col-md-5 checkbox checkbox-switchery switchery-xs">
                                                                <label> <input type="checkbox"
                                                                               id="switchery_will_pay_689"
                                                                               name="tourniquet_will_pay" value="1"
                                                                               class="switchery-will-pay" checked=""
                                                                               data-switchery="true"> </label>
                                                            </div>
                                                            <label class="tourniquet_price_label">Turniket
                                                                (<span class="tourniquet-price-text">0.00</span>)
                                                            </label>
                                                            <input type="hidden" name="tourniquet_price" value="0"
                                                                   class="tourniquet_price"></div>
                                                        <div class="col-lg-2">
                                                            <button type="button"
                                                                    class="btn btn-block btn-danger deleteDestination">
                                                                <i class="icon-trash"></i></button>
                                                            <input type="hidden" name="marker_index" value=""></div>
                                                    </div>
                                                    <div class="form-group destination">
                                                        <div class="col-lg-10 has-feedback has-feedback-left">
                                                            <input x-required data-lat="40.3880362"
                                                                   data-lng="49.838729"
                                                                   id="address-1"
                                                                   type="text" name="name"
                                                                   value=""
                                                                   class="address-1 form-control input-roundless addressInput"
                                                                   autocomplete="off" onclick="this.select();">
                                                            <input type="hidden" name="destination_id" value=""
                                                                   class="destination-id">
                                                            <input type="hidden" name="destination_type" value=""
                                                                   class="type">
                                                            <input type="hidden" name="latitude" value=""
                                                                   class="latitude">
                                                            <input type="hidden" name="longitude" value=""
                                                                   class="longitude">
                                                            <div class="form-control-feedback">
                                                                <i class="icon-pin-alt"></i>
                                                            </div>
                                                            <ul class="search_result"></ul>
                                                        </div>
                                                        <div class="col-lg-3 number_street" style="display: none">
                                                            <select name="number_street"
                                                                    class="form-control select-search">
                                                                <option value=""></option>
                                                            </select></div>
                                                        <div class="col-lg-3 object_tourniquet" style="display: none">
                                                            <div
                                                                class="col-md-5 checkbox checkbox-switchery switchery-xs">
                                                                <label> <input type="checkbox"
                                                                               id="switchery_will_pay_689"
                                                                               name="tourniquet_will_pay" value="1"
                                                                               class="switchery-will-pay" checked=""
                                                                               data-switchery="true"
                                                                               style="display: none;">
                                                                </label>
                                                            </div>
                                                            <label class="tourniquet_price_label">Turniket
                                                                (<span class="tourniquet-price-text">0.00</span>)
                                                            </label>
                                                            <input type="hidden" name="tourniquet_price" value="0"
                                                                   class="tourniquet_price"></div>
                                                        <div class="col-lg-2">
                                                            <button type="button"
                                                                    class="btn btn-block btn-danger deleteDestination">
                                                                <i class="icon-trash"></i></button>
                                                            <input type="hidden" name="marker_index" value=""></div>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-lg-12">
                                                        <a id="createDestination"
                                                           class="btn btn-default btn-icon btn-block"><i
                                                                class="icon-plus3"></i> Başqa ünvan</a>
                                                        <button type="button" class="hidden" id="draw">Xəritədə yol
                                                        </button>
                                                    </div>
                                                </div>


                                                <div class="form-group">
                                                    <div id="orderValue">
                                                        <label
                                                            class="control-label col-lg-2"><strong>Məsafə</strong></label>
                                                        <div class="col-lg-2">
                                                            <input type="text" name="order_value" value=""
                                                                   id="new_distance" class="form-control"
                                                                   autocomplete="off" readonly="readonly">
                                                        </div>
                                                    </div>
                                                    <label
                                                        class="control-label col-lg-2"><strong>Qiymət</strong></label>
                                                    <div class="col-lg-2">
                                                        <input type="text" name="price" required value="0.00" id="price"
                                                               class="form-control" readonly autocomplete="off">
                                                    </div>
                                                    <label class="control-label col-lg-2"><strong>Gözləmə
                                                            Müddəti</strong></label>
                                                    <div class="col-lg-2">
                                                        <input type="text" name="timeout" required value="10"
                                                               id="timeout" class="form-control" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class=col-lg-12>
                                                        <div class="col-lg-4">
                                                            <button id="add_new_price" class="btn btn-success"
                                                                    type="button">Qiyməti dəyiş
                                                            </button>
                                                        </div>
                                                        <div class="col-lg-3">
                                                            <input class="new_price form-control" type="text"
                                                                   id="operatorPrice" value="0" name="operatorPrice"
                                                                   class="form-control" autocomplete="off"
                                                                   style="display: none">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-lg-3"><strong>Ön
                                                            sifariş</strong></label>
                                                    <div class="col-lg-2">
                                                        <div class="checkbox checkbox-switchery">
                                                            <label>
                                                                <input name="isCurrentTime" id="isCurrentTime"
                                                                       value="0" type="checkbox"
                                                                       class="switchery-future">
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div id="futureOrderDate" style="display: none;" class="col-lg-7">
                                                        <div class="col-lg-8"><input type="text" name="order_date"
                                                                                     value="{{ date('Y-m-d H:i') }}"
                                                                                     placeholder=""
                                                                                     id="order_date"
                                                                                     class="form-control"/></div>
                                                    </div>


                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-lg-2"><strong>Qeyd</strong></label>
                                                    <div class="col-lg-10">
                                                        <textarea name="description" rows="5" cols="5"
                                                                  class="form-control"
                                                                  placeholder="Qeyd ..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                    <div class="form-group">
                                        <label class="control-label col-lg-3"><strong>Dispetçerə göndər</strong></label>
                                        <div class="col-lg-3">
                                            <div class="checkbox checkbox-switchery">
                                                <label>
                                                    <input type="checkbox" id="autoSearchChanger"
                                                           class="switchery-search">
                                                    <input type="hidden" name="auto_search" value="1">
                                                </label>
                                            </div>
                                        </div>
                                        <label class="control-label col-lg-3"><strong>Açıq sifariş</strong></label>
                                        <div class="col-lg-3">
                                            <div class="checkbox checkbox-switchery">
                                                <label>
                                                    <input type="checkbox" id="publicChanger" class="switchery-public">
                                                    <input type="hidden" name="is_public" value="0">
                                                </label>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="text-center">
                                        {{--                                        <button value="0" type="button" class="btn btn-danger col-lg-6 executing">--}}
                                        {{--                                            İcraya göndərma <i class="icon-arrow-right14 position-right"></i></button>--}}
                                        <button style="font-size: 20px;" disabled value="1" type="submit"
                                                class="btn btn-primary col-lg-12 executing sendAction">
                                            Təsdiqlə<i class="icon-arrow-right14 position-right"></i></button>
                                        <input type="hidden" name="to_execute">
                                        {{--                                        <a data-type="1" style="margin-top: 10px;margin-right: 10px;" href="javacript:;"--}}
                                        {{--                                           id="findTaxiTest"--}}
                                        {{--                                           type="button"--}}
                                        {{--                                           class="btn btn-primary col-lg-6 executing">Taksi tap</a>--}}

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-white">
                                <div class="panel-heading" style="display: flex;align-items: center;">
                                    <div class="col-lg-2">
                                        <h5 class="panel-title">Xəritə</h5>
                                    </div>
                                    <div class="col-lg-10" style="display: flex;justify-content: flex-end;">
                                        <h6 class="panel-title" style="margin-right: 10px;margin-top: 10px;">Xəritədən
                                            istifadə</h6>
                                        <div class="checkbox checkbox-switchery">
                                            <label>
                                                <input name="map_check" id="map_check"
                                                       type="checkbox" class="switchery-map-check">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-body" id="map-body">
                                    <div id="map"></div>
                                </div>

                                {{--                                <div class="container" style="margin-top: 20px;">--}}
                                {{--                                    <div class="row">--}}
                                {{--                                        <div id="right-panel">--}}
                                {{--                                            <p>Total Distance: <span id="total"></span></p>--}}
                                {{--                                        </div>--}}
                                {{--                                    </div>--}}
                                {{--                                </div>--}}
                            </div>
                            <div id="taxi_panel" class="panel panel-white" style="display: block;">
                                <div class="panel-heading" style="display: flex;align-items: center;">
                                    <div class="col-lg-2">
                                        <h5 class="panel-title">Taksi</h5>
                                    </div>
                                    <div class="col-lg-10" style="display: flex;justify-content: flex-end;">
                                        <h6 class="panel-title" style="margin-right: 10px;margin-top: 10px;">Məcburi
                                            sifariş</h6>
                                        <div class="checkbox checkbox-switchery">
                                            <label>
                                                <input name="is_important" id="is_important"
                                                       type="checkbox" class="switchery-important" value="0">
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel-body">
                                    <div id="taxiSection">
                                        <div class="form-group">
                                            <label class="control-label col-lg-2"><strong>Taksi</strong></label>
                                            <div class="col-lg-10">
                                                <input data-type="1" data-is-order="1" type="text"
                                                       name="taxi" value=""
                                                       class="form-control destinationIdInput"
                                                       autocomplete="off">
                                                <input type="hidden" name="taxi_id"
                                                       value=""
                                                       class="form-control destinationIdInputHidden"
                                                       autocomplete="off" id="destination">
                                                <ul class="search_result"></ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{--                <div class="row">--}}
                {{--                    <div class="col-md-12">--}}
                {{--                        <div class="panel panel-flat">--}}
                {{--                            <div class="panel-heading">--}}
                {{--                                <h6 class="panel-title">ƏLAVƏLƏR</h6>--}}
                {{--                                <div class="heading-elements">--}}
                {{--                                    <ul class="icons-list">--}}
                {{--                                        <li><a data-action="collapse"></a></li>--}}
                {{--                                        <li><a data-action="close"></a></li>--}}
                {{--                                    </ul>--}}
                {{--                                </div>--}}
                {{--                            </div>--}}

                {{--                            <div class="panel-body">--}}
                {{--                                <div class="tabbable">--}}
                {{--                                    <ul class="nav nav-tabs nav-justified">--}}
                {{--                                        <li class="active"><a href="#basic-justified-tab1" data-toggle="tab">SİFARİŞLƏRİN--}}
                {{--                                                TARİXİ</a></li>--}}
                {{--                                    </ul>--}}

                {{--                                    <div class="tab-content">--}}
                {{--                                        <div class="tab-pane active table-responsive" id="basic-justified-tab1">--}}
                {{--                                            <table class="table table-bordered table-striped table-hover">--}}
                {{--                                                <thead>--}}
                {{--                                                <tr>--}}
                {{--                                                    <th>#</th>--}}
                {{--                                                    <th>Tayinatı</th>--}}
                {{--                                                    <th>Tarif/Dəyər</th>--}}
                {{--                                                    <th>Qiymət</th>--}}

                {{--                                                    <th>Əməliyyat</th>--}}
                {{--                                                </tr>--}}
                {{--                                                </thead>--}}
                {{--                                                <tbody id="order_history_body">--}}

                {{--                                                </tbody>--}}
                {{--                                            </table>--}}
                {{--                                        </div>--}}
                {{--                                    </div>--}}
                {{--                                </div>--}}
                {{--                            </div>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                </div>--}}
            </form>
        </div>
        <table class="table table-bordered table-striped table-hover">
            <thead>
            <tr>
                <th>NÖMRƏ</th>
                <th>SİFARİŞ TARİXİ</th>
                <th>QİYMƏT</th>
                <th>ÜNVAN</th>
                <th>CALL İD</th>
                <th><strong>Əməliyyat</strong></th>
            </tr>
            </thead>
            <tbody id="orders_list">

            </tbody>
        </table>
    </div>

@endsection


@section('script')






    <script type="text/javascript">


        $(document).ready(function () {
            var cidnum = '{{ request()->get('cidnum')??'' }}';
            cidnum = '994' +  cidnum.substr(1);
            var exten = '{{ request()->get('exten')??'' }}';
            if (cidnum != '' && exten != '') {
                var object = $("#customer_phone");
                $("#customer_phone").focus();
                $("#customer_phone").val(cidnum);
                getCallData(object);
            }
        });


        dragula([document.getElementById('destinations')], {
            revertOnSpill: true
        }).on('out', function (el) {
            $('#draw').click();
        });

        $('#findTaxiTest').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                type: 'post',
                url: "{{ route('postFindTaxiTest') }}",
                dataType: 'json',
                success: function (data) {
                    // console.log(data.result);
                }
            });
        });

        // $('#destinations input').on('change', function (e) {
        //     e.preventDefault();
        //     $('.sendAction').attr('disabled', true);
        // });


        // Time picker
        $("#order_date").AnyTime_picker({
            format: "%Y-%m-%d %H:%i",
            clearText: 'clear',
            buttonClear: 'picker__button--clear',
        });


        $('#publicChanger').on('change', function () {
            if ($('#publicChanger').is(':checked')) {
                $('input[name="is_public"]').val('1');

            } else {
                $('input[name="is_public"]').val('0');
            }
        });

        $('#autoSearchChanger').on('change', function () {
            if ($('#autoSearchChanger').is(':checked')) {
                $('input[name="auto_search"]').val('0')
            } else {
                $('input[name="auto_search"]').val('1')
            }
        });


        $('#is_important').on('change', function () {
            if ($('#is_important').is(':checked')) {
                $('input[name="is_important"]').val('1');

            } else {
                $('input[name="is_important"]').val('0');
            }
        });

        $('#map_check').on('change', function () {
            if (!$(this).is(":checked")) {
                $('#map-body').css('pointer-events', 'none');
            } else {
                $('#map-body').css('pointer-events', 'unset');
            }
        });

        $('input[name="isCurrentTime"]').on('change', function () {
            if (!$('input[name="isCurrentTime"]').is(':checked')) {
                $('#futureOrderDate').hide();
                $('input[name="isCurrentTime"]').val('0');
            } else {
                $('#futureOrderDate').show();
                $('input[name="isCurrentTime"]').val('1');
            }

            priceCalculate();
        });

        $('body').delegate('.switchery-will-pay', "change", function () {
            if ($(this).is(':checked')) {
                $(this).val(1);
            } else {
                $(this).val(0);
            }
        });

        $('select[name="tariff"]').on('change', function () {
            priceCalculate();
        });

        $('select[name="options[]"]').on('change', function () {
            priceCalculate();
        });

        $('#timeout').on('change', function () {
            priceCalculate();
        });

        $('body').delegate('.switchery-will-pay', 'change', function () {
            priceCalculate();
        });

        $('input[name="order_value"]').on('change', function () {
            priceCalculate();
        });

        $('input[name="order_date"]').on('change', function () {
            priceCalculate();
        });

        function priceCalculate() {

            tariff = $('select[name="tariff"] option:checked').val();
            orderType = $('select[name="order_type"] option:checked').val();

            var options = [];
            $('select[name="options[]"] option:checked').each(function () {
                options.push($(this).val());
            });

            timeout = $('input[name="timeout"]').val();

            km = $('input[name="order_value"]').val();

            customer_phone = $('input[name="customer_phone"]').val();

            if (!km) {
                return false;
            }

            destinations = [];
            $('.destination-id').each(function () {
                if ($(this).val() != "") destinations.push($(this).val());
            });

            tourniquetWillPays = [];
            $('.switchery-will-pay').each(function () {
                tourniquetWillPays.push($(this).val());
            });

            tourniquetPrices = [];
            $('.tourniquet_price').each(function () {
                if ($(this).val() != "") tourniquetPrices.push($(this).val());
                else tourniquetPrices.push(0);
            });

            isCurrentTime = $('input[name="isCurrentTime"]').is(':checked');

            if (!isCurrentTime) {
                var date = new Date();
                var month = date.getMonth() + 1;
                month = month < 10 ? '0' + month : month;
                var day = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
                var hour = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
                var orderDate = date.getFullYear() + "-" + month + "-" + day + " " + hour + ":" + date.getMinutes();
                var orderWeekday = date.getDay();
            } else {
                var orderDate = $('input[name="order_date"]').val();
                var orderWeekday = (new Date(orderDate)).getDay();
            }

            if (true) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    type: 'post',
                    url: "{{ route('postOrderPriceCalculate') }}",
                    dataType: 'json',
                    data: {
                        customer_phone: customer_phone,
                        tariff: tariff,
                        orderType: orderType,
                        options: options,
                        timeout: timeout,
                        km: km,
                        destinations: destinations,
                        tourniquetWillPays: tourniquetWillPays,
                        tourniquetPrices: tourniquetPrices,
                        orderDate: orderDate,
                        orderWeekday: orderWeekday
                    },
                    success: function (data) {
                        if (data['success']) {
                            $('input[name="price"]').val(data['result']);
                        }
                    }
                });
            }
        }

        function formIsValid(form) {
            var ok = true;

            form.find('[x-required]').each(function () {
                $(this).css('border-color', '#ebedf2');

                var type = $(this).attr('type');


                if (type == 'text' || type == 'summernote' || type == 'password' || type == 'email' || type == 'date' || type == 'number' || type == 'file' || type == 'select') {
                    var value = $(this).val().trim();
                    if (!value.length) {
                        $(this).css('border-color', 'red');
                        let parent = $(this).parent();
                        parent.find('.select2-selection').css('border-color', 'red');
                        swal({
                            title: 'Bütün vacib sahələri doldurun!',
                            type: 'warning',
                            showConfirmButton: false,
                            timer: 1111
                        });

                        ok = false;
                    }
                }
            });

            return ok;
        }

        $('#form-order').on('submit', function (e) {
            e.preventDefault();

            $('.sendAction').attr('disabled', true);

            var route = [],
                destination_id = [],
                destination_type = [],
                lat = [],
                lng = [],
                number_street = [],
                tourniquet_price = [],
                tourniquet_type = [],
                tourniquet_will_pay = [];

            $('.addressInput').each(function () {
                lat.push($(this).attr('data-lat'));
                lng.push($(this).attr('data-lng'));
                destination_id.push($(this).attr('data-destination-id'));
                destination_type.push($(this).attr('data-type'));
                tourniquet_will_pay.push($(this).attr('data-tourniquet-will-pay'));
                tourniquet_type.push($(this).attr('data-tourniquet-type'));
                tourniquet_price.push($(this).attr('data-tourniquet-price'));
                number_street.push($(this).attr('data-number-street'));
                route.push($(this).val());
            });

            var tariff = $('select[name="tariff"] option:checked').val();
            var orderType = $('select[name="order_type"] option:checked').val();
            var payment_method = $('select[name="payment_method"] option:checked').val();


            var number = $('#customer_phone').val();
            var customer_name = $('#customer_name').val();
            var description = $('[name="description"]').val();
            var price = $('#price').val();
            var operatorPrice = $('#operatorPrice').val();
            var taxi_id = $('input[name="taxi_id"]').val();

            var options = [];
            $('select[name="options[]"] option:checked').each(function () {
                options.push($(this).val());
            });

            var timeout = $('input[name="timeout"]').val();

            var auto_search = $('input[name="auto_search"]').val();

            var is_important = $('input[name="is_important"]').val();

            var is_public = $('input[name="is_public"]').val();

            var isCurrentTimeValue = $('input[name="isCurrentTime"]').val();

            var km = $('input[name="order_value"]').val();

            var isCurrentTime = $('input[name="isCurrentTime"]').is(':checked');

            if (!isCurrentTime) {
                var date = new Date();
                var month = date.getMonth() + 1;
                month = month < 10 ? '0' + month : month;
                var day = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
                var hour = date.getHours() < 10 ? '0' + date.getHours() : date.getHours();
                var orderDate = date.getFullYear() + "-" + month + "-" + day + " " + hour + ":" + date.getMinutes();
                var orderWeekday = date.getDay();
            } else {
                var orderDate = $('input[name="order_date"]').val();
                var orderWeekday = (new Date(orderDate)).getDay();
            }

            if (!formIsValid($(this))) {
                return false;
            }


            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                type: 'post',
                url: '{{ route('postOrderNew') }}',
                dataType: 'json',
                data: {
                    lat: lat,
                    lng: lng,
                    destination_id: destination_id,
                    destination_type: destination_type,
                    tourniquet_price: tourniquet_price,
                    tourniquet_type: tourniquet_type,
                    tourniquet_will_pay: tourniquet_will_pay,
                    route: route,
                    number_street: number_street,
                    tariff: tariff,
                    orderType: orderType,
                    options: options,
                    timeout: timeout,
                    km: km,
                    description: description,
                    price: price,
                    operatorPrice: operatorPrice,
                    orderDate: orderDate,
                    orderWeekday: orderWeekday,
                    number: number,
                    customer_name: customer_name,
                    payment_method: payment_method,
                    auto_search: auto_search,
                    is_public: is_public,
                    taxi_id: taxi_id,
                    is_important: is_important,
                    isCurrentTimeValue: isCurrentTimeValue
                },
                success: function (data) {
                    var url = 'http://192.168.0.29/cc/public/dashboard';
                    window.location.replace(url);
                }
            });

        });

        function destinatonItemFunc(option = '', id = '', type = 1, latitude = '', longitude = '', price = 0) {

            if (type === 1) {
                nameCol = 10;
                numberStreetIsActive = 'none';
                numberStreetStr = '<option value=""></option>';
            } else {
                nameCol = 7;
                numberStreetIsActive = 'block';
                numberStreetStr = '';
            }

            switcheryRand = Math.ceil(Math.random() * 1000);

            if (price) {
                objectTourniquetIsActive = 'block';
            } else {
                objectTourniquetIsActive = 'none';
            }

            item =
                '                <div class="form-group destination">' +
                '                  <div class="col-lg-' + nameCol + ' has-feedback has-feedback-left">\n' +
                ' <input x-required data-lat="40.3880362"\n' +
                '                                                                   data-lng="49.838729"\n' +
                '                                                                   id="address-1"\n' +
                '                                                                   type="text" name="name"\n' +
                '                                                                   value=""\n' +
                '                                                                   class="address-1 form-control input-roundless addressInput"\n' +
                '                                                                   autocomplete="off" onclick="this.select();">' +
                '<input type="hidden" name="destination_id" value="" class="destination-id">' +
                '<input type="hidden" name="destination_type" value="" class="type">' +
                '<input type="hidden" name="latitude" value="" class="latitude">' +
                '<input type="hidden" name="longitude" value="" class="longitude">' +
                '<div class="form-control-feedback">' +
                '<i class="icon-pin-alt"></i>' +
                '</div>' +
                '<ul class="search_result"></ul>' +
                '                  </div>\n' +
                '                  <div class="col-lg-3 number_street" style="display: ' + numberStreetIsActive + '">' +
                '                     <select name="number_street[]" class="form-control select-search">' +
                numberStreetStr +
                '                     </select>' +
                '                  </div>' +
                '                  <div class="col-lg-3 object_tourniquet" style="display: ' + objectTourniquetIsActive + '">' +
                '                     <div class="col-md-5 checkbox checkbox-switchery switchery-xs">\n' +
                '                         <label>' +
                '                            <input type="checkbox" id="switchery_will_pay_' + switcheryRand + '" name="tourniquet_will_pay[]" value="1" class="switchery-will-pay" checked>' +
                '                         </label>\n' +
                '                     </div>' +
                '                     <label class="tourniquet_price_label">' +
                'Turniket (' + (price / 100).toFixed(2) + ')' +
                '                     </label>\n' +
                '                     <input type="hidden" name="tourniquet_price[]" value="' + price + '" class="tourniquet_price">' +
                '                  </div>' +
                '                  <div class="col-lg-2 btndelete">\n' +
                '                     <button type="button" class="btn btn-block btn-danger deleteDestination"><i class="icon-trash"></i></button>\n' +
                '                       <input type="hidden" name="marker_index[]" value="">' +
                '                  </div>' +
                '               </div>';

            $('#destinations').append(item);

            var primary = document.querySelector('#switchery_will_pay_' + switcheryRand);
            var switchery = new Switchery(primary, {color: '#2196F3'});

        }

        // $('#order_date').daterangepicker({
        //     singleDatePicker: true,
        //     timePicker: true,
        //     timePicker24Hour: true,
        //     locale: {
        //         format: 'YYYY-MM-DD HH:mm'
        //     }
        // });


        $('body').delegate('#createDestination', 'click', function () {
            destinatonItemFunc();
            var primary = document.querySelector('.switchery-will-pay');
            var switchery = new Switchery(primary, {color: '#2196F3'});
        });


        var future = document.querySelector('.switchery-future');
        var switchery = new Switchery(future, {color: '#2196F3'});

        var primary = document.querySelector('.switchery-search');
        var switchery = new Switchery(primary, {color: '#2196F3'});

        var public = document.querySelector('.switchery-public');
        var switchery = new Switchery(public, {color: '#2196F3'});

        var mapCheck = document.querySelector('.switchery-map-check');
        var switchery = new Switchery(mapCheck, {color: '#2196F3'});

        var important = document.querySelector('.switchery-important');
        var switchery = new Switchery(important, {color: '#2196F3'});

        // $('#taxi_panel').show()


    </script>

    <script type="text/javascript">
        var map;
        var directionsService;
        var directionsRenderer;
        var marker;
        var markers = [];

        $(document).ready(function () {
            let text = $('.addressInput').val() ? $('.addressInput').val() : 'Hilal elektrik';
            let lat = $('.addressInput').attr('data-lat');
            let lng = $('.addressInput').attr('data-lng');
            parent = $('.addressInput:first').parent();
            initMap(lat, lng, parent);
        });

        function initMap(lat, lng, parent, is_first_addressInput = false) {

            var geocoder = new google.maps.Geocoder;

            var address = new google.maps.LatLng(lat, lng);
            map = new google.maps.Map(
                document.getElementById('map'),
                {
                    center: address,
                    zoom: 13
                });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                draggable: true,
                map: map,
                panel: document.getElementById('right-panel')
            });

            var latlng = {lat: parseFloat(lat), lng: parseFloat(lng)};
            geocoder.geocode({'location': latlng}, function (results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        createMarker(results[0], parent, geocoder, latlng);
                    } else {
                        swal({
                            title: 'Ünvan düzgün deyil!',
                            type: 'warning',
                            showConfirmButton: false,
                            timer: 1111
                        });
                    }
                } else {
                    swal({
                        title: 'Ünvan düzgün deyil!',
                        type: 'warning',
                        showConfirmButton: false,
                        timer: 1111
                    });
                }
            });
        }


        function createMarker(place, parent, geocoder, latlng) {

            var infowindow = new google.maps.InfoWindow();

            marker = new google.maps.Marker({
                map: map,
                position: latlng,
                draggable: true
            });

            markers.push(marker);

            google.maps.event.addListener(marker, 'click', function () {
                infowindow.setContent(place.name);
                infowindow.open(map, this);
            });

            dragendMerker(marker, geocoder, parent);

        }


        function dragendMerker(marker, geocoder, parent) {

            google.maps.event.addListener(marker, 'dragend', function (event) {

                $(parent).find('.lat').value = event.latLng.lat();

                $(parent).find('.lng').value = event.latLng.lng();

                $(parent).find('.addressInput').attr('data-lat', event.latLng.lat());

                $(parent).find('.addressInput').attr('data-lng', event.latLng.lng());

                var latlng = {lat: parseFloat(event.latLng.lat()), lng: parseFloat(event.latLng.lng())};
                geocoder.geocode({'location': latlng}, function (results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            var addressLocation = results[0].formatted_address;
                            $(parent).find('.addressInput').val(addressLocation);
                            parent.parent().find('.has-feedback-left').css('width', '83.3%');
                            parent.parent().find('.number_street').hide();
                            parent.parent().find('.number_street select').empty();
                            parent.parent().find('.object_tourniquet').hide();
                            parent.parent().find('.tourniquet_price').val('');
                            parent.parent().find('.tourniquet-price-text').text('');
                            $(parent).find('.addressInput').attr('data-destination-id', 0);
                            $(parent).find('.addressInput').attr('data-tourniquet-will-pay', 0);
                            $(parent).find('.addressInput').attr('data-tourniquet-price', 0);
                            $(parent).find('.addressInput').attr('data-tourniquet-type', 0);

                            $(parent).find('input[name="destination_id"]').val(0);
                            $(parent).find('input[name="destination_type"]').val(0);
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                });

                // $(parent).find('.addressInput').removeClass('addressInput');

            });
        }

        $(function () {
            // addressOrderSearch();
            // $('.addressInput').trigger("keyup");
        });
        $('body').delegate('.addressInput', 'focusout', function () {
            $(this).parent().find(".search_result").fadeOut();
        });
        $('body').delegate('.addressInput', 'focusin', function () {
            $(this).trigger('keyup');
        });

        $('body').delegate('.deleteDestination', 'click', function () {
            $button = $(this);

            if ($('.destination').length > 2) {
                if ($button.next().val()) {
                    markers[$button.next().val()].setMap(null);
                }

                $(this).parent().parent().remove();
                // addFromMark(false, false);
                if ($('select[name="order_type"] option:checked').val() == '1') distanceCalculate();
                priceCalculate();
            } else {
                let parent = $(this).parent();

                parent.parent().find('.addressInput').val(" ");
                parent.parent().find('.has-feedback-left').css('width', '83.3%');
                parent.parent().find('.number_street').hide();
                parent.parent().find('.number_street select').empty();
                parent.parent().find('.object_tourniquet').hide();
                parent.parent().find('.tourniquet_price').val('');
                parent.parent().find('.tourniquet-price-text').text('');
                parent.parent().find('.addressInput').attr('data-lat', '');
                parent.parent().find('.addressInput').attr('data-lng', '');
                parent.parent().find('.addressInput').attr('data-destination-id', 0);
                parent.parent().find('.addressInput').attr('data-tourniquet-will-pay', 0);
                parent.parent().find('.addressInput').attr('data-tourniquet-price', 0);
                parent.parent().find('.addressInput').attr('data-tourniquet-type', 0);
                parent.parent().find('input[name="destination_id"]').val('');
                parent.parent().find('input[name="destination_type"]').val('');
                parent.parent().find('input[name="latitude"]').val('');
                parent.parent().find('input[name="longitude"]').val('');

            }

            $('#draw').click();

        });

        $('body').delegate('.addressInput', 'keyup', function (e) {
            e.preventDefault();

            parent = $(this).parent();

            let text = $(this).val();
            if (text.length < 4) {
                return false;
            }

            parent.find('.search_result').empty();
            parent.find('.search_result').hide();


            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                type: 'post',
                url: '{{ route('postDestinationSearchAddress') }}',
                dataType: 'json',
                data: {
                    text: text,
                },
                success: function (data) {
                    if (data.success !== false) {
                        setTimeout(function () {
                            $.each(data.results, function (v, result) {
                                    let html = '';
                                    html += "<li onclick='parseOrderDestinationStreet(this,parent);' data-tourniquet_del=" + result.del + " data-tourniquet_price=" + result.price + " data-tourniquet_type=" + result.tourniquet_type + " data-destionation_id=" + result.id + " data-type=" + result.type + "  data-lat=" + result.latitude + " data-lng=" + result.longitude + " data-id=" + result.id + ">" + result.name + "</li>";
                                    if (html != false) {
                                        parent.find('.search_result').show();
                                        parent.find('.search_result').append(html);
                                    }
                                }
                            );
                        }, 500);

                    }
                }
            });
        });

        $('#destinations .number_street').on('change', 'select', function () {

            lat = $(this).find('option:selected').attr('data-latitude');
            lng = $(this).find('option:selected').attr('data-longitude');

            $(this).parent().parent().find('.addressInput').attr('data-lat', lat);
            $(this).parent().parent().find('.addressInput').attr('data-lng', lng);
            $(this).parent().parent().find('.latitude').val(lat);
            $(this).parent().parent().find('.longitude').val(lng);

            $('#draw').click();
        });

        function parseOrderDestinationStreet(t, parent) {
            let text = $(t).text();
            let id = $(t).attr('data-id');
            let lat = $(t).attr('data-lat');
            let lng = $(t).attr('data-lng');
            let type = $(t).attr('data-type');
            let tourniquet_type = $(t).attr('data-tourniquet_type');
            let tourniquet_price = $(t).attr('data-tourniquet_price');
            let tourniquet_del = $(t).attr('data-tourniquet_del');
            let tourniquet_will_pay = 0;

            parent.parent().find('.has-feedback-left').css('width', '83.33%');
            parent.parent().find('.number_street').hide();
            parent.parent().find('.number_street select').empty();


            if (type == 2) {
                getDestinationSearchStreetNumber(id, parent);
            }

            parent.parent().find('.object_tourniquet').hide();

            if (tourniquet_price > 0 && tourniquet_del == 0) {
                parent.parent().find('.object_tourniquet').show();
                parent.parent().find('.has-feedback-left').css('width', '58%');
                parent.parent().find('.tourniquet_price').val(tourniquet_price);
                parent.parent().find('.tourniquet-price-text').text(tourniquet_price);
                tourniquet_will_pay = 1;
            }

            $(parent).find('.addressInput').val(text);


            $(parent).find('.addressInput').attr('data-destination-id', id);
            $(parent).find('.addressInput').attr('data-type', type);
            $(parent).find('.addressInput').attr('data-tourniquet-will-pay', tourniquet_will_pay);
            $(parent).find('.addressInput').attr('data-tourniquet-type', tourniquet_type);
            $(parent).find('.addressInput').attr('data-tourniquet-price', tourniquet_price);


            $(parent).find('.destination-id').val(id);
            $(parent).find('.type').val(type);
            $(parent).find('.search_result').empty();
            $(parent).find('.search_result').hide();


            if (type != 2) {
                $(parent).find('.addressInput').attr('data-lat', lat);
                $(parent).find('.addressInput').attr('data-lng', lng);
                $(parent).find('.latitude').val(lat);
                $(parent).find('.longitude').val(lng);

                var is_first_addressInput = $(parent).find('.addressInput').attr('data-is-first');
                initMap(lat, lng, parent, is_first_addressInput);
                $('#draw').click();
            }
        }

        //street lat lng olmadigi ucun null gelir bazadan ve yeniden hemin streetin id sine uygun datalari getirib lat lng parse edirik
        function getDestinationSearchStreetNumber(id, parent) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                type: 'post',
                url: '{{ route('postDestinationSearchStreetNumber') }}',
                dataType: 'json',
                cache: false,
                async: false,
                data: {
                    id: id,
                },
                success: function (data) {
                    if (data.success !== false) {
                        setTimeout(function () {
                            $.each(data.results, function (v, result) {
                                    let html = '';
                                    html += "<option data-id=" + result.id + " data-street=" + result.street + " data-number=" + result.number + " data-latitude=" + result.latitude + "  data-longitude=" + result.longitude + ">" + result.number + "</option>";
                                    if (html != false) {
                                        parent.parent().find('.has-feedback-left').css('width', '58%');
                                        parent.parent().find('.number_street select').append(html);
                                        parent.parent().find('.number_street').show();

                                    }
                                }
                            );

                            parent.parent().find('.number_street select option:eq(1)').attr('selected', 'selected');

                            //get address lat,lng because not have lat and lng in street
                            lat = parent.parent().find('.number_street select option:eq(1)').attr('data-latitude');
                            lng = parent.parent().find('.number_street select option:eq(1)').attr('data-longitude');
                            //

                            $(parent).find('.addressInput').attr('data-lat', lat);
                            $(parent).find('.addressInput').attr('data-lng', lng);
                            $(parent).find('.latitude').val(lat);
                            $(parent).find('.longitude').val(lng);


                            var is_first_addressInput = $(parent).find('.addressInput').attr('data-is-first');
                            initMap(lat, lng, parent, is_first_addressInput);
                            $('#draw').click();

                        }, 500);
                    }
                }
            });
        }


        var cityCircle;


        $('#draw').on('click', function (e) {
                e.preventDefault();
                let lat = [];
                let lng = [];
                var ok = true;


                if (markers.length) {
                    for (var i = 0; i < markers.length; i++) {
                        markers[i].setMap(null);
                    }
                    markers = [];
                }
                // inputda olan lat,lng elde edirik
                $('.addressInput').each(function (index, value) {
                    $(this).css('border-color', '#ddd');
                    if (!value.value || value.value == "") {
                        $(this).css('border-color', 'red');
                        ok = false;
                    }
                    lat.push(value.getAttribute('data-lat'));
                    lng.push(value.getAttribute('data-lng'));

                });

                if (!ok) {
                    return false;
                }
                //end

                // baslangic ve son addressler
                var address_start = new google.maps.LatLng(lat[0], lng[0]);
                var address_end = new google.maps.LatLng(lat[lat.length - 1], lng[lng.length - 1]);
                //end

                // dragend edende yeniden root cizir ve inputlara hemin addressler gelir
                directionsRenderer.addListener('directions_changed', function () {
                    computeTotalDistance(directionsRenderer.getDirections());
                });
                //end

                directionsRenderer.setMap(map);

                // waypointleri elde edirik
                let address = [];
                for (var i = 0; i < lat.length; i++) {
                    if (i != 0 && i != (lat.length - 1)) {
                        address.push({
                            location: new google.maps.LatLng(lat[i], lng[i]),
                            stopover: true
                        });
                    }
                }
                //end

                var request = {
                    origin: address_start,
                    destination: address_end,
                    waypoints: address,
                    travelMode: 'DRIVING'
                };
                directionsService.route(request, function (response, status) {
                    if (status == 'OK') {
                        directionsRenderer.setDirections(response);
                        $('.sendAction').removeAttr('disabled');
                    } else {
                        directionsRenderer.set('directions', null);
                        if (cityCircle !== undefined) {
                            cityCircle.setMap(null);
                        }
                        $('#new_distance').val('');
                        $('#price').val('0.00');
                        $('.sendAction').attr('disabled', true);
                    }
                });

            }
        );


        function computeTotalDistance(result) {
            var parent = $('.addressInput').parent();
            var total = 0;
            if (result) {
                var myroute = result.routes[0];
                for (var i = 0; i < myroute.legs.length; i++) {
                    total += myroute.legs[i].distance.value;
                    if ($('#map_check').is(':checked')) {
                        $('.addressInput').eq(i).val(myroute.legs[i].start_address);
                        $('.addressInput').eq(i).attr('data-lat', myroute.legs[i].start_location.lat());
                        $('.addressInput').eq(i).attr('data-lng', myroute.legs[i].start_location.lng());
                        $(parent).find('.latitude').eq(i).val(myroute.legs[i].end_location.lat());
                        $(parent).find('.longitude').eq(i).val(myroute.legs[i].end_location.lng());

                        $('.addressInput').eq(i + 1).val(myroute.legs[i].end_address);
                        $('.addressInput').eq(i + 1).attr('data-lat', myroute.legs[i].end_location.lat());
                        $('.addressInput').eq(i + 1).attr('data-lng', myroute.legs[i].end_location.lng());
                        $(parent).find('.latitude').eq(i + 1).val(myroute.legs[i].end_location.lat());
                        $(parent).find('.longitude').eq(i + 1).val(myroute.legs[i].end_location.lng());

                        deleteAllAddressInputParameters(parent);

                    }
                }


                total = total / 1000;
                // document.getElementById('total').innerHTML = total + ' km';
                $('#new_distance').val(total);

                //qiymeti hesabla
                priceCalculate();

                //daireni cek
                drawCircle(myroute);
                //end daire cek

            }

        }

        function drawCircle(myroute) {

            var address_start = new google.maps.LatLng(myroute.legs[0].start_location.lat(), myroute.legs[0].start_location.lng());

            if (cityCircle !== undefined) {
                cityCircle.setMap(null);
            }
            var circle = {
                center: address_start,
                map: map,
                radius: 3000,          // IN METERS.
                fillColor: '#FF6600',
                fillOpacity: 0.3,
                strokeColor: "#FFF",
                strokeWeight: 0         // DON'T SHOW CIRCLE BORDER.
            };
            cityCircle = new google.maps.Circle(circle);
        }


        function deleteAllAddressInputParameters(parent) {
            $(parent).find('.has-feedback-left').css('width', '83.3%');
            $(parent).find('.number_street').hide();
            $(parent).find('.number_street select').empty();
            $(parent).find('.object_tourniquet').hide();
            $(parent).find('.tourniquet_price').val('');
            $(parent).find('.tourniquet-price-text').text('');
            $(parent).find('.addressInput').attr('data-destination-id', 0);
            $(parent).find('.addressInput').attr('data-tourniquet-will-pay', 0);
            $(parent).find('.addressInput').attr('data-tourniquet-price', 0);
            $(parent).find('.addressInput').attr('data-tourniquet-type', 0);

            $(parent).find('input[name="destination_id"]').val(0);
            $(parent).find('input[name="destination_type"]').val(0);
        }

        //////////////////////////ROUTE


    </script>


    <script type="text/javascript"
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCZWy2YH-P1SUd4wbCz4gteGoX3aXSd1c&libraries=places&language=az"></script>

    <!-- Footer -->
    <div class="navbar navbar-default">
        <ul class="nav navbar-nav no-border visible-xs-block">
            <li><a class="text-center collapsed" data-toggle="collapse" data-target="#navbar-second"><i
                        class="icon-circle-up2"></i></a></li>
        </ul>

        <div class="navbar-collapse collapse" id="navbar-second">
            <div class="navbar-text">
                &copy; 2016-2019. <a href="#">Smart Taxi</a>
            </div>

            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    <li><a href="http://otos.ru/az/administrator/system_logs">System logs</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- /footer -->
    <script type="text/javascript">
        $('button.btnPriceStrategy').click(function () {
            window.location.href = 'http://otos.ru/az/administrator/price_strategy_fast/create';
        });
        $("#add_new_price").click(function () {
            $(".new_price").toggle();
            $('#operatorPrice').val(0);
        });

        $('body').delegate('#customer_phone', 'keyup', function () {
            var object = $(this);
            getCallData(object);
        });


        function getCallData(object) {
            let number = object.val();

            if (number.length < 12) {
                return false;
            }

            priceCalculate();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                type: 'post',
                url: "{{ route('getCustomername') }}",
                dataType: 'json',
                data: {
                    phone: number,
                },
                success: function (data) {
                    $('.payment_method option:selected').removeAttr('selected');
                    $("#customer_corporative_label").text('');
                    $("#customer_corporative_label").css('display', 'none');

                    if (data['success']) {
                        $("#customer_name").val(data["customer_name"]);

                        let backColor = data["group"] != 1 ? '#194bff' : '#888585';

                        $("#customer_corporative_label").css({'display': 'block', 'background-color': backColor});
                        $("#customer_corporative_label").text(data["groupName"]);
                        let txt = '';

                        if (data["group"] == 1 || data['status'] == "false") {
                            $(".payment_method option[value=1]").prop('selected', 'selected');
                        } else {
                            $(".payment_method option[value=2]").prop('selected', 'selected');
                        }

                        $.each(data['orders'], function (index, value) {
                            txt += '<tr>\n' +
                                '                <td>' + number + '</td>\n' +
                                '                <td>' + value.created_at + '</td>\n' +
                                '                <td>' + (value.pr + value.opr) + '</td>\n' +
                                '                <td>';
                            let routes = '';
                            if (value.route) {
                                $.each(JSON.parse(value.route), function (index2, value2) {
                                    routes += value2.name + '<br>';
                                });
                            }
                            txt += routes + '</td>\n' +
                                '                <td>' + value.callid + '</td>\n' +
                                '                <td>\n' +
                                '                    <div class="btn-group">\n' +
                                '                        <a class="btn btn-default" href="/order-view/' + value.id + '"><i class="icon-eye"></i></a>\n' +
                                '\n' +
                                '                    </div>\n' +
                                '                </td>\n' +
                                '            </tr>';
                        });
                        $('#orders_list').html(txt);
                    } else {
                        $("#customer_name").val('');
                        $('#orders_list').html('')
                    }
                    $('.payment_method').change();
                }
            });
        }
    </script>



@endsection
