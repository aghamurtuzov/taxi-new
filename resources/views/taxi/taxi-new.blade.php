@extends('main.layout')
@section('content')

    <!-- Form horizontal -->
    <div class="panel panel-flat">
        <div class="panel-heading">
            <h5 class="panel-title">Yeni taksi</h5>
            <div class="heading-elements">

            </div>
        </div>
        <div class="panel-body">
            @if(Session::has('success-message'))
                <p class="alert {{ Session::get('alert-class', 'alert-success') }}">{{ Session::get('success-message') }}</p>
            @endif
            @if ($errors->any())
                <ul class="alert alert-class alert-danger" style="padding-left: 60px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
            <form id="form" action="{{ route('postTaxiEdit',['id' => $result->id??'0','code' => $module->code]) }}"
                  class="form-horizontal"
                  method="post" accept-charset="utf-8">
                @csrf
                <div class="tabbable taxinew" id="taxi">
                    <div class="tab-content">
                        <div class="tab-panel active">
                            <h1 class="taxinew-head">AVTOMOBİL MƏLUMATLARI</h1>
                            <br>
                            <fieldset>

                                <div class="form-group">
                                    <label class="control-label col-lg-3">Nömrə nişanı <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <input type="text" name="number" required class="form-control"
                                               data-mask="99-aa-999"
                                               placeholder="10-AA-001" value="{{ old('number') }}"  />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Tarif <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <select name="tariff[]" class="form-control select-category"
                                                multiple="multiple" required>
                                            @foreach($tariffs as $tariff)
                                                <option @if(in_array($tariff->id,old('tariff')??[0] )) selected
                                                        @endif value="{{ $tariff->id }}">{{ $tariff->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Markası <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <select name="brand" class="form-control select-search" required>
                                            <option value="">Seçin</option>
                                            @foreach($brands as $brand)
                                                <option @if(old('brand') == $brand->id) selected
                                                        @endif value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Modeli <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <select name="model" class="form-control select-search" required>
                                            <option value="">Model seçin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Rəngi <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <select name="color" class="form-control" required>
                                            <option value="">Seçin</option>
                                            @foreach($colors as $color)
                                                <option @if(old('color') == $color->id) selected
                                                        @endif value="{{ $color->id }}">{{ $color->color_name }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">İli <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <input type="text" name="year" class="form-control" minlength="4"
                                               maxlength="4" value="{{ old('year') }}" required />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Ban növü <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <select name="body" class="form-control" required>
                                            <option value="">Seçin</option>
                                            @foreach($bodies as $body)
                                                <option @if(old('body') == $body->id) selected
                                                        @endif value="{{ $body->id }}">{{ $body->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </fieldset>
                        </div>
                        <div class="tab-panel">
                            <h1 class="taxinew-head">ŞƏXSİ MƏLUMATLAR</h1>
                            <br>
                            <fieldset class="content-group">
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Adı <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <input type="text" name="firstname" class="form-control"
                                               placeholder="Müşviq" value="{{ old('firstname') }}" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Soyadı <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <input type="text" name="lastname" class="form-control"
                                               placeholder="Manaflı" value="{{ old('lastname') }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-3">Cinsi <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <select name="sex" class="form-control" required>
                                            <option value="">Seçin</option>
                                            <option @if(old('sex') == 1) selected
                                                    @endif  value="1">Kişi
                                            </option>
                                            <option @if(old('sex') == 2) selected
                                                    @endif  value="2">Qadın
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Doğum günü <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <input type="date" name="birthday"
                                               class="form-control"
                                               value="{{ old('birthday') }}" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Mobil prefiks <sup>*</sup></label>
                                    <div class="col-lg-3">
                                        <select class="form-control" name="phone_prefix" required>
                                            <option value="">Seçin</option>
                                            <option @if(old('phone_prefix') == 50) selected
                                                    @endif value="50">050
                                            </option>
                                            <option @if(old('phone_prefix') == 51) selected
                                                    @endif value="51">051
                                            </option>
                                            <option @if(old('phone_prefix') == 55) selected
                                                    @endif value="55">055
                                            </option>
                                            <option @if(old('phone_prefix') == 70) selected
                                                    @endif value="70">070
                                            </option>
                                            <option @if(old('phone_prefix') == 77) selected
                                                    @endif value="77">077
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" name="phone" class="form-control"
                                               placeholder="9227710"
                                               value="{{ old('phone') }}" maxlength="7"
                                               minlength="7" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Cizhazınız təsdiq
                                        kodu<sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <input type="text" id="code" name="device_id"
                                               value="{{ old('device_id') }}"
                                               class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Tabel <sup>*</sup></label>
                                    <div class="col-lg-9">
                                        <input type="text" id="tabel" name="code"
                                               value="{{ $code }}"
                                               class="form-control" required>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="more_block">
                            <div class="more_btn" id="more_btn">
                                <div class="row">
                                    <div class="col-md-3"></div>
                                    <div class="col-md-9">
                                        <button type="button" class="btn btn-default btn-block" style="font-size: 13px;">Daha çox</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="more_block">
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Balanssız? </label>
                                    <div class="col-lg-9">
                                        <label class="radio-inline">
                                            <input @if(old('free') == 1) checked
                                                   @endif  type="radio" name="free" class="styled" value="1">BƏLİ
                                        </label>
                                        <label class="radio-inline">
                                            <input @if(old('free') == 0) checked
                                                   @endif type="radio" name="free" value="0" class="styled">
                                            XEYR </label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Qeydiyyat ünvanı
                                    </label>
                                    <div class="col-lg-9">
                                        <input type="text" name="address" class="form-control"
                                               placeholder="BAKI şəh., NİZAMİ ray., Q. QARAYEV PR, ev.57, m.88"
                                               value="{{ old('address') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Yanacaq növü </label>
                                    <div class="col-lg-9">
                                        <select name="fuel" class="form-control">
                                            <option value="">Seçin</option>
                                            @foreach($fuels as $fuel)
                                                <option @if(old('fuel') == $fuel->id) selected
                                                        @endif value="{{ $fuel->id }}">{{ $fuel->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Yanacaq sərfi</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="fuel_consumption" class="form-control"
                                               value="{{old('fuel_consumption') }}"
                                               placeholder="17" maxlength="2"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Sürət qutusu </label>
                                    <div class="col-lg-9">
                                        <select name="transmission" class="form-control">
                                            <option value="">Seçin</option>
                                            <option @if(old('transmission') == 1) selected
                                                    @endif value="1">Avtomatik
                                            </option>
                                            <option @if(old('transmission') == 2) selected
                                                    @endif value="2">Mexaniki
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Ş/V seriyası </label>
                                    <div class="col-lg-9">
                                        <input type="text" name="passport" class="form-control"
                                               value="{{ old('passport') }}"
                                               placeholder="16172520" minlength="8" maxlength="8"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Ş/V fin kodu </label>
                                    <div class="col-lg-9">
                                        <input type="text" name="pin_code" class="form-control"
                                               value="{{ old('pin_code') }}"
                                               placeholder="2GV5BCP" minlength="7" maxlength="7"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Sürücülük Vəsiqəsi
                                    </label>
                                    <div class="col-lg-9">
                                        <input type="text" name="driver_license" class="form-control"
                                               value="{{ old('driver_license') }}"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Müqavilə №</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="contract" class="form-control"
                                               value="{{ old('contract') }}"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">VÖEN</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="tax" class="form-control"
                                               placeholder="1602394702"
                                               value="{{ old('tax') }}"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Fərqlənmə nişanı</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="taxi_license" class="form-control"
                                               placeholder="1602394702" value="{{ old('taxi_license') }}"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Bitmə tarixi </label>
                                    <div class="col-lg-9">
                                        <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="icon-calendar22"></i>
                                                    </span>
                                            <input type="text" name="taxi_license_expiry"
                                                   class="form-control taxi_license_expiry" placeholder=""
                                                   value="{{ old('taxi_license_expiry') }}"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Texpasport</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="technical_passport" class="form-control"
                                               placeholder="1602394702"
                                               value="{{ old('technical_passport') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Mobil prefiks 2</label>
                                    <div class="col-lg-3">
                                        <select class="form-control" name="mobile_prefix">
                                            <option value="">Seçin</option>
                                            <option @if(old('mobile_prefix') == 50) selected
                                                    @endif value="50">050
                                            </option>
                                            <option @if(old('mobile_prefix') == 51) selected
                                                    @endif value="51">051
                                            </option>
                                            <option @if(old('mobile_prefix') == 55) selected
                                                    @endif value="55">055
                                            </option>
                                            <option @if(old('mobile_prefix') == 70) selected
                                                    @endif value="70">070
                                            </option>
                                            <option @if(old('mobile_prefix') == 77) selected
                                                    @endif value="77">077
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" name="mobile" class="form-control"
                                               placeholder="9227710"
                                               value="{{ old('mobile') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Ata adı00</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="fathername" class="form-control"
                                               placeholder="Mehman" value="{{ old('fathername') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">E-mail </label>
                                    <div class="col-lg-9">
                                        <input type="email" name="email" class="form-control"
                                               placeholder="nadjafzadeh@gmail.com"
                                               value="{{ old('email') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Qeyd</label>
                                    <div class="col-lg-9">
                                        <input type="text" name="description" class="form-control"
                                               placeholder="Sürücü barədə əlavə qeyd"
                                               value="{{ old('description') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Bildiyi Dillər
                                    </label>
                                    <div class="col-lg-9">
                                        <select name="language[]"
                                                class="form-control select-languages"
                                                multiple="multiple">
                                            <option value="">Seçin</option>
                                            @foreach($driver_languages as $driver_language)
                                                <option
                                                    @if(in_array($driver_language->id,old('language')??[0] )) selected
                                                    @endif value="{{ $driver_language->id }}">{{ $driver_language->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-3">Aqreqatlar </label>
                                    <div class="col-lg-9">
                                        <select name="option[]" class="form-control select-languages"
                                                multiple="multiple">
                                            <option value="">Seçin</option>
                                            @foreach($options as $option)
                                                <option @if(in_array($option->id,old('option')??[0] )) selected
                                                        @endif value="{{ $option->id }}">{{ $option->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row text-center mb-15 taxinew">


                    <button type="submit" class="btn btn-success taxibtn mb-20">Təsdiqlə</button>
                </div>
            </form>
        </div>
    </div>


@endsection

@section('script')
{{--    <script type="text/javascript" src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>--}}
{{--    <script type="text/javascript" src="{{ asset('assets/js/messages_az.js') }}"></script>--}}
{{--    <script>--}}
{{--        $("#form").validate();--}}
{{--    </script>--}}
    <script type="text/javascript">

        $("#more_btn").on("click", function () {
            $("#more_block").fadeToggle();
        });
        $("#btn-default").text("Daha çox");

        $('select[name=\'marka\']').trigger('change');

        $('.select-category').select2();
        $('.select-languages').select2();

        $('.taxi_license_expiry').daterangepicker({
            showDropdowns: true,
            singleDatePicker: true,
            applyClass: 'bg-slate-600',
            cancelClass: 'btn-default',
            locale: {
                format: 'YYYY-MM-DD'
            }
        });

    </script>


    <script>
        $('select[name="brand"]').change(function () {
            var brand = $(this).val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                type: 'post',
                url: "{{ route('getModels') }}",
                dataType: 'json',
                data: {
                    brand: brand,
                },
                success: function (data) {
                    if (data['success']) {

                        $('select[name="model"]').html('');
                        $.each(data['models'], function (index, value) {
                            $('select[name="model"]').append('<option value="' + value.id + '">' + value.name + '</optionid>')
                        });
                    } else {

                    }
                }
            });
        });

        $(window).load(function () {
            let brand = $('select[name="brand"]').children("option:selected").val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                type: 'post',
                url: "{{ route('getModels') }}",
                dataType: 'json',
                data: {
                    brand: brand,
                },
                success: function (data) {
                    if (data['success']) {

                        $('select[name="model"]').html('');
                        $.each(data['models'], function (index, value) {
                            if ("{{ old('model') }}" == value.id) {

                                $('select[name="model"]').append('<option selected value="' + value.id + '">' + value.name + '</option>')
                            } else {
                                $('select[name="model"]').append('<option value="' + value.id + '">' + value.name + '</option>')
                            }
                        });
                    } else {

                    }
                }
            });
        });
    </script>
@endsection



