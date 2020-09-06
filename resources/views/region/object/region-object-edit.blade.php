@extends('main.layout')
@section('content')

    <style>

        .search_result {
            width: 500px;
            display: block;
            position: absolute;
            z-index: 999;
            padding-left: 0;
            background: white;
            border: 1px solid #ddd;
            display: none;
        }

        .search_result li {
            list-style: none;
            padding: 10px;
            cursor: pointer;
        }

        .search_result li:hover {
            background-color: #f5f5f5 !important;
        }

    </style>

    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h5 class="panel-title">Obyekt Yarat</h5>
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
                                <form
                                    action="{{ route('postRegionObjectEdit',['id' => $result->id??'0','code' => $module->code]) }}"
                                    class="form-horizontal"
                                    method="post" accept-charset="utf-8" id="form">
                                    @csrf
                                    <fieldset class="content-group">
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Ad</label>
                                            <div class="col-lg-10">
                                                <input name="name" type="text" class="form-control"
                                                       value="{{ $result->name??'' }}"
                                                       placeholder="Ad" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Açıqlama</label>
                                            <div class="col-lg-10">
                                                <input name="description" type="text" class="form-control"
                                                       value="{{ $result->description??'' }}"
                                                       placeholder="Açıqlama" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Region</label>
                                            <div class="col-lg-10">
                                                <select name="region" class="form-control" required>
                                                    <option value="0">Boş</option>
                                                    @foreach($regions as $region)
                                                        <option
                                                            @if(isset($result->region) && $result->region == $region->id) selected
                                                            @endif value="{{ $region->id }}">{{ $region->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Şəhər</label>
                                            <div class="col-lg-10">
                                                <select name="city" class="form-control" required>
                                                    <option value="0">Boş</option>
                                                    @foreach($cities as $city)
                                                        <option
                                                            @if(isset($result->city) && $result->city== $city->id) selected
                                                            @endif value="{{ $city->id }}">{{ $city->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Rayon</label>
                                            <div class="col-lg-10">
                                                <select name="district" class="form-control" required>
                                                    <option value="0">Boş</option>
                                                    @foreach($districts as $district)
                                                        <option
                                                            @if(isset($result->district) && $result->district == $district->id) selected
                                                            @endif value="{{ $district->id }}">{{ $district->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Küçə</label>
                                            <div class="col-lg-10">
                                                <input data-lat="{{ $result->latitude??'40.3880362' }}"
                                                       data-lng="{{ $result->longitude??'49.838729' }}"
                                                       id="street"
                                                       type="text" name="street_name"
                                                       value="{{ $result->streetName->name ?? '' }}"
                                                       class="form-control destinationIdInput"
                                                       autocomplete="off " required>
                                                <input type="hidden" name="street" value="{{ $result->street??'' }}"
                                                       class="form-control destinationIdInputHidden"
                                                       autocomplete="off" id="destination">
                                                <ul class="search_result"></ul>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Kategoriya</label>
                                            <div class="col-lg-10">
                                                <select name="type" class="form-control" required>
                                                    <option value="0">Boş</option>
                                                    @foreach($types as $type)
                                                        <option
                                                            @if(isset($result->type) && $result->type == $type->id) selected
                                                            @endif value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Açar söz</label>
                                            <div class="col-lg-10">
                                                <input name="keyword" type="text" class="form-control"
                                                       value="{{ $result->keyword??'' }}"
                                                       placeholder="Açar söz" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Nömrə</label>
                                            <div class="col-lg-10">
                                                <input name="number" type="number" class="form-control"
                                                       value="{{ $result->number??'' }}"
                                                       placeholder="Nömrə" required>
                                            </div>
                                        </div>


                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Prioritet</label>
                                            <div class="col-lg-10">
                                                <input name="priority" type="text" class="form-control"
                                                       value="{{ $result->priority??'' }}"
                                                       placeholder="Prioritet" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">En</label>
                                            <div class="col-lg-10">
                                                <input id="lat" name="latitude" type="number" class="form-control"
                                                       value="{{ $result->latitude??'' }}"
                                                       placeholder="En" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Uzunluq</label>
                                            <div class="col-lg-10">
                                                <input id="lng" name="longitude" type="number" class="form-control"
                                                       value="{{ $result->longitude??'' }}"
                                                       placeholder="Uzunluq" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Sıralama</label>
                                            <div class="col-lg-10">
                                                <input name="sort" type="number" class="form-control"
                                                       value="{{ $result->sort??'' }}"
                                                       placeholder="Sıralama" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Status</label>
                                            <div class="col-lg-10">
                                                <select name="status" class="form-control" required>
                                                    <option @if(isset($result->status) && $result->status??'') selected
                                                            @endif value="1">Aktiv
                                                    </option>
                                                    <option @if(isset($result->status) && !$result->status??'') selected
                                                            @endif value="0">Deaktiv
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <div class="text-right">
                                        <button type="submit" class="btn btn-primary">Yarat <i
                                                class="icon-arrow-right14 position-right"></i></button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>


        @endsection


        @section('script')
            <script>
                $(document).ready(function () {
                    let text = $('#street').val() ? $('#street').val() : 'Hilal elektrik';
                    let lat = $('#street').attr('data-lat');
                    let lng = $('#street').attr('data-lng');

                    initMap(text, lat, lng);
                });
            </script>
            <script>


                $('.destinationIdInput').on('keyup', function (e) {
                    e.preventDefault();

                    let text = $(this).val();

                    $('.search_result').empty();
                    $('.search_result').hide();

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        type: 'post',
                        url: '{{ route('postDestinationSearchStreet') }}',
                        dataType: 'json',
                        data: {
                            text: text,
                        },
                        success: function (data) {
                            if (data.success !== false) {

                                $.each(data.results, function (v, result) {
                                        let html = '';
                                        html += "<li onclick='parseDestionation(this);' data-lat=" + result.latitude + "  data-lng=" + result.longitude + " data-id=" + result.id + ">" + result.name + "</li>";
                                        if (html != false) {
                                            $('.search_result').show();
                                            $('.search_result').append(html);
                                        }
                                    }
                                );
                            }
                        }
                    });


                })


            </script>

            <script type="text/javascript"
                    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCCZWy2YH-P1SUd4wbCz4gteGoX3aXSd1c&libraries=places"></script>

            <script type="text/javascript">

                function parseDestionationStreet(t) {
                    let text = $(t).text();
                    let id = $(t).attr('data-id');

                    $('.destinationIdInput').val(text);
                    $('.search_result').empty();
                    $('.search_result').hide();
                    $('.destinationIdInputHidden').val(id);

                }


                function initMap(text, lat, lng) {
                    var sydney = new google.maps.LatLng(lat, lng);

                    infowindow = new google.maps.InfoWindow();

                    map = new google.maps.Map(
                        document.getElementById('map'), {center: sydney, zoom: 15});

                    var request = {
                        query: text,
                        fields: ['name', 'geometry'],
                    };

                    service = new google.maps.places.PlacesService(map);

                    service.findPlaceFromQuery(request, function (results, status) {
                        if (status === google.maps.places.PlacesServiceStatus.OK) {

                            for (var i = 0; i < results.length; i++) {
                                createMarker(results[i]);
                            }

                            map.setCenter(results[0].geometry.location);
                        }
                    });
                }

                function createMarker(place) {
                    var marker = new google.maps.Marker({
                        map: map,
                        position: place.geometry.location,
                        draggable: true
                    });

                    google.maps.event.addListener(marker, 'click', function () {
                        infowindow.setContent(place.name);
                        infowindow.open(map, this);
                    });

                    google.maps.event.addListener(marker, 'dragend', function (event) {

                        document.getElementById("lat").value = event.latLng.lat();

                        document.getElementById("lng").value = event.latLng.lng();

                    });

                }

            </script>


@endsection
