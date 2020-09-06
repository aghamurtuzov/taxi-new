
@extends('main.layout')
@section('content')

<!-- sms -->
<div class="page-container">
    <div class="page-content">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h5 class="panel-title">Prioritet Strategiyası Yarat</h5>
                        </div>

                        <div class="panel-body">

                            @if(Session::has('success-message'))
                                <p class="alert {{ Session::get('alert-class', 'alert-success') }}">{{ Session::get('success-message') }}</p>
                            @endif
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <p class="alert alert-class alert-danger">{{ $error }}</p>
                                @endforeach
                            @endif
                           <form id="form" action="{{ route('postSettingPriorityStrategyEdit',['id' => $result->id??0,'code' => $module->code]) }}" class="form-horizontal" method="post" accept-charset="utf-8">
                            @csrf
                            <fieldset class="content-group">

                                <div class="form-group">
                                    <label class="control-label col-lg-2">Prioritet</label>
                                    <div class="col-lg-10">
                                        <input name="priority" type="number" required class="form-control" value="{{ $result->priority??'' }}" placeholder = "Prioritet">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Əməliyyat</label>
                                    <div class="col-lg-10">
                                        <select name="action" class="select-fixed-single destinationType" required>
                                                <option @if(isset($result->action) && $result->action == 1) selected @endif  value="1">Tamamlanıb</option>
                                                <option @if(isset($result->action) && $result->action == 2) selected @endif  value="2">Ləğv edilib</option>
                                                <option @if(isset($result->action) && $result->action == 3) selected @endif  value="3">Vaxtı bitib</option>
                                                <option @if(isset($result->action) && $result->action == 4) selected @endif  value="4">Dispetçer tərəfindən və ya taksi özünin sifarişi ləğv etməsi</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Ödəmə methodu</label>
                                    <div class="col-lg-10">
                                        <select name="payment_method" class="select-fixed-single destinationType" required>
                                            <option @if(isset($result->payment_method) && $result->payment_method == 1 ) selected @endif value="1">Nəğd</option>
                                            <option @if(isset($result->payment_method) && $result->payment_method == 2 ) selected @endif value="2">Nəğdsiz</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Vacib</label>
                                    <div class="col-lg-10">
                                        <select name="important" class="select-fixed-single destinationType" required>
                                            <option @if(isset($result->important) && $result->important == 1 ) selected @endif value="1">Vacib</option>
                                            <option @if(isset($result->important) && $result->important == 0 ) selected @endif value="0">Vacib deyil</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Qüvvədə olduğu vaxt</label>
                                    <div class="col-lg-10">
                                        <select name="for" class="select-search form-control" required>
                                            <option @if(!isset($result->weekday)) selected @endif value="1">
                                                Tarix
                                            </option>
                                            <option @if(isset($result->weekday)) selected @endif  value="2">
                                                Həftə Günləri
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div id="weekdaySection" class="form-group">
                                    <label class="control-label col-lg-2">Həftə Günləri</label>
                                    <div class="col-lg-10">
                                        <select name="weekday[]" multiple class="select-search form-control" required>
                                            <option
                                                @if(isset($result->weekday) && count($result->dataWeek()) && in_array("1",$result->dataWeek())) selected
                                                @endif
                                                value="1">Bazar ertəsi
                                            </option>
                                            <option
                                                @if(isset($result->weekday) && count($result->dataWeek()) && in_array("2",$result->dataWeek())) selected
                                                @endif
                                                value="2">Çərşənbə axşamı
                                            </option>
                                            <option
                                                @if(isset($result->weekday) && count($result->dataWeek()) && in_array("3",$result->dataWeek())) selected
                                                @endif
                                                value="3">Çərşənbə
                                            </option>
                                            <option
                                                @if(isset($result->weekday) && count($result->dataWeek()) && in_array("4",$result->dataWeek())) selected
                                                @endif
                                                value="4">Cümə axşamı
                                            </option>
                                            <option
                                                @if(isset($result->weekday) && count($result->dataWeek()) && in_array("5",$result->dataWeek())) selected
                                                @endif
                                                value="5">Cümə
                                            </option>
                                            <option
                                                @if(isset($result->weekday) && count($result->dataWeek()) && in_array("6",$result->dataWeek())) selected
                                                @endif
                                                value="6">Şənbə
                                            </option>
                                            <option
                                                @if(isset($result->weekday) && count($result->dataWeek()) && in_array("7",$result->dataWeek())) selected
                                                @endif
                                                value="7">Bazar
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Başlama Tarixi </label>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="icon-watch2"></i></span>
                                            <input name = "from_time" type="text" required class="form-control" id="anytime-time" value="{{ $result->from_time ?? '00:00' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Bitmə Tarixi</label>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="icon-watch2"></i></span>
                                            <input name= "to_time" type="text" required class="form-control" id="anytime-time1" value="{{ $result->to_time ?? '23:59' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Status</label>
                                    <div class="col-lg-10">
                                        <select name="status" class="select-fixed-single destinationType" required>
                                            <option @if(isset($result->status) && $result->status == 0) selected @endif
                                              value="0">Deaktiv</option>
                                            <option @if(isset($result->status) && $result->status == 1) selected @endif value="1">Aktiv</option>
                                        </select>
                                    </div>
                                </div>

                            </fieldset>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Yarat <i class="icon-arrow-right14 position-right"></i></button>
                            </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection


@section('script')
    <script>
        $('select[name="for"]').on('change', function () {

            if ($('select[name="for"] option:selected').val() == "1") {
                $('#dateSection').show();
                $('#weekdaySection').hide();
                $('#weekdaySection option').attr('selected', false);
                $(".select2-selection__rendered .select2-selection__choice").remove();
            } else {
                $('[name="date"]').val(null);
                $('#dateSection').hide();
                $('#weekdaySection').show();
            }
        });


        // Time picker
        $("#anytime-time").AnyTime_picker({
            format: "%H:%i",
            clearText: 'clear',
            buttonClear: 'picker__button--clear',
        });
        $("#anytime-time1").AnyTime_picker({
            format: "%H:%i",

        });

    </script>

@endsection
