@extends('main.layout')
@section('content')
<div class="page-container">
    <div class="page-content">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-white">
                        <div class="panel-heading">
                            <h5 class="panel-title">Küçəni Redaktə et</h5>
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
                         <form id="form" action="{{ route('postRegionStreetEdit',['id' => $result->id??0,'code' => $module->code]) }}" class="form-horizontal" method="post" accept-charset="utf-8">
                            @csrf
                            <fieldset class="content-group">
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Ad</label>
                                    <div class="col-lg-10">
                                        <input name="name" type="text" required class="form-control" value="{{ $result->name??'' }}" placeholder="Ad">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Bölgə</label>
                                    <div class="col-lg-10">
                                        <select name="region" class="select-search" required>
                                            <option  value="0">Boş</option>
                                            @foreach($regions as $region)
                                            <option @if(isset($result->region ) && $result->region ==$region->id) selected @endif  value="{{$region->id}}">{{$region->name}}</option>
                                            @endforeach
                                    </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Şəhər</label>
                                    <div class="col-lg-10">
                                        <select name="city" class="select-search" required>
                                            <option  value="0">Boş</option>
                                            @foreach($cities as $city)

                                            <option @if(isset($result->city ) && $result->city ==$city->id) selected @endif  value="{{$city->id}}">{{$city->name}}</option>
                                            @endforeach

                                    </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Açar söz</label>
                                    <div class="col-lg-10">
                                        <input name="keyword" type="text" required class="form-control" value="{{$result->keyword??''}}" placeholder="Açar söz">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Prioritet</label>
                                    <div class="col-lg-10">
                                        <input name="priority" type="text" required class="form-control" value="{{$result->priority??''}}" placeholder="Prioritet">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Sıralama</label>
                                    <div class="col-lg-10">
                                        <input name="sort" type="number" required class="form-control" value="{{$result->sort??''}}" placeholder="Sıralama">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">Status</label>
                                    <div class="col-lg-10">
                                        <select name="status" class="select" required>
                                             <option @if(isset($result->status) && $result->status) selected @endif value="1">Aktiv
                                                </option>
                                             <option @if(isset($result->status) && !$result->status) selected @endif value="0">Deaktiv
                                                </option>
                                        </select>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Redaktə et <i class="icon-arrow-right14 position-right"></i></button>
                            </div>

                            </form>                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
