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
        }

        .search_result li:hover {
            background-color: #f5f5f5;
        }

        .selected {
            background: #56b0ff;
        }

        .taxi {
            padding: 10px 0;
            border: 1px dashed #bdb9b9;
            background: #f5f5f5;
            cursor: move;
        }
    </style>

    <div class="page-container">
        <div class="page-content">
            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-white">
                            <div class="panel-heading">
                                <h5 class="panel-title">Taksi blok et</h5>
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
                                <form id="form" action="{{ route('postTaxiBlockedEdit',['code' => $module->code]) }}"
                                      class="form-horizontal"
                                      method="post" accept-charset="utf-8">
                                    @csrf
                                    <fieldset class="content-group">
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Taxsi</label>
                                            <div class="col-lg-10">
                                                <input @if($result) disabled @endif   data-type="1" type="text"
                                                       name="taxi_name"
                                                       value="@if($result){{ $result->code . '-' . $result->firstname . ' ' . $result->lastname }}@endif"
                                                       class="form-control destinationIdInput"
                                                       autocomplete="off" required>
                                                <input type="hidden" name="taxi_id"
                                                       value="{{ $result->id??'' }}"
                                                       class="form-control destinationIdInputHidden"
                                                       autocomplete="off" id="destination">
                                                <ul class="search_result"></ul>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-lg-2">Açıqlama</label>
                                            <div class="col-lg-10">
                                                <textarea name="description" class="form-control" rows="5" id="comment"
                                                          required="required"
                                                          placeholder="Açıqlama" required></textarea>
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
                </div>

            </div>
        </div>
    </div>


@endsection
