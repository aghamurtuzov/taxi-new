@extends('main.layout')
@section('content')





    <!-- Page container -->
    <div class="page-container">

        <!-- Page content -->
        <div class="page-content">

            <!-- Main sidebar -->
            <div class="sidebar sidebar-main sidebar-default sidebar-separate" style="width: 280px;">
                <div class="sidebar-content">

                    <!-- User details -->
                    <div class="content-group">
                        <div class="panel-body bg-indigo-400 border-radius-top text-center"
                             style="background-image: url(http://otos.ru/assets/images/bg.png); background-size: contain;">
                            <div class="content-group-sm">
                                <h6 class="text-semibold no-margin-bottom">
                                    {{ $result->fullname() }}                            </h6>
                            </div>

                            <a href="#" class="display-inline-block content-group-sm">
                                <img src="http://otos.ru/upload/taxi/default.png" class="img-circle img-responsive"
                                     alt="" style="width: 110px; height: 110px;">
                            </a>

                            <div class="content-group-sm">
                                <span class="label label-default"> {{ $result->code }}</span>
                                <span class="label {{($result->live=="1") ? "label-success":"label-danger"}}">{{($result->live=="1") ? "Online":"Offline"}}</span>
                            </div>

                            <div class="content-group-sm">
                                <span class="label label-default"> {{ $result->date }}</span>
                            </div>
                        </div>

                        <div class="panel no-border-top no-border-radius-top">
                            <ul class="navigation">
                                <li class="navigation-header">Navigation</li>
                                <li class="active"><a href="#profile" data-toggle="tab"><i class="icon-files-empty"></i>
                                        Profil</a></li>
                                <li><a href="#orders" data-toggle="tab"><i class="icon-cart "></i> Sifarişlər</a></li>
                                <li><a href="#transactions" data-toggle="tab"><i class="icon-coin-dollar"></i>
                                        Əməliyyatlar</a></li>
                                <li><a href="#priorityTransactions" data-toggle="tab"><i class="icon-files-empty"></i>
                                        Prioritetlər</a></li>
                                <li><a href="#messages" data-toggle="tab"><i class="icon-bell3"></i> Mesajlar</a></li>
                                <li><a href="#taxi_devices" data-toggle="tab"><i class="icon-enter5"></i> Giriş cəhtləri</a>
                                </li>
                                <li><a href="#foto_nezaret" data-toggle="tab"><i class="icon-gallery"></i> Foto nəzarət</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- /user details -->
                </div>
            </div>
            <!-- /main sidebar -->


            <!-- Main content -->
            <div class="content-wrapper">

                <!-- Tab content -->
                <div class="tab-content">

                    <div class="tab-pane fade in active" id="profile">
                        <!-- Profile info -->
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Profile information</h6>
                                <div class="heading-elements">
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Adı</label>
                                            <input type="text" value="{{ $result->firstname }}" readonly
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Soyadı</label>
                                            <input type="text" value="{{ $result->lastname }}" readonly
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Ata adı</label>
                                            <input type="text" value="{{ $result->fathername }}" readonly
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Cinsi</label>
                                            <input type="text" value="{{ $result->sex ? 'Kişi' : 'Qadın' }}" readonly
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Doğum günü</label>
                                            <input type="text" value="{{ $result->birthday }}" readonly
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Qeydiyyat ünvanı</label>
                                            <input type="text" value="{{ $result->address }}" readonly
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Mobil</label>
                                            <input type="text" value="{{ $result->phone }}" readonly
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Mobil 2</label>
                                            <input type="text" value="{{ $result->mobile }}" readonly
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>E-mail</label>
                                            <input type="text" value="{{ $result->email }}" readonly
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>Balans</label>
                                            <input type="text" value="{{ $result->account ? number_format($result->account->balance, 2) : '' }} ₼" readonly
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Prioritet</label>
                                            <input type="text" value="{{ $result->priority }}" readonly
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>Seçimlər</label>
                                            <input type="text" value="{{ $result->optionName() }}" readonly
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /profile info -->

                    </div>


                    <div class="tab-pane fade" id="orders">

                        <!-- Available hours -->
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Sifarişlər</h6>
                                <div class="heading-elements">
                                </div>
                            </div>

                            <div class="panel-body">
                                <div>
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th><span>#</span></th>
                                            <th><span>Müştəri</span></th>
                                            <th><span>Tarif</span></th>
                                            <th><span>Başlanğıc nöqtəsi</span></th>
                                            <th><span>Bitiş nöqtəsi</span></th>
                                            <th><span>Sifarişin qiyməti</span></th>
                                            <th><span>Status</span></th>
                                            <th><span>Tarix</span></th>

                                            <th>Əməliyyat</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($result->ordersName as $order)
                                                <tr>
                                                    <td><a>{{$result->id}}</a></td>
                                                    <td>{{$result->firstname." ".$result->lastname." ".$result->phone}}</td>
                                                    <td>{{$order->orderDetailName->tariffName->name}}</td>
                                                    <td>{{$order->orderDetailName->routeName()[0]}}</td>
                                                    <td>{{$order->orderDetailName->routeName()[$order->orderDetailName->routeName()['last']]}}</td>
                                                    <td>{{$order->orderDetailName->price}}</td>
                                                    <td><span class="label">
                                                            @if($order->is_edited)
                                                                <button
                                                                    class="btn btn-danger">
                                                        Dispetçer
                                                    </button>
                                                            @elseif($order->status == 0)
                                                                <img src="{{ asset('assets/images/loader.gif') }}" width="30">
                                                            @elseif($order->status == 4)
                                                                <i class="icon-checkmark"></i>
                                                            @else
                                                                <button
                                                                    class="btn btn-{{ $order->colorName() }}">
                                                        {{$order->statusName()}}
                                                    </button>
                                                            @endif</span></td>
                                                    <td>{{$order->orderDetailName->order_date}}</td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a class=" btn btn-default"
                                                               href="{{ route('getOrderView',['id' => $order->id]) }}"><i
                                                                    class="icon-eye"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /available hours -->

                    </div>

                    <div class="tab-pane fade" id="transactions">
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Taksidən</h6>
                                <div class="heading-elements">
                                </div>
                            </div>

                            <div class="panel-body">
                                <div>
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th><span>ID</span></th>
                                            <th><span>Göndərən</span></th>
                                            <th><span>Hesabın növü</span></th>
                                            <th><span>Qəbul edən</span></th>
                                            <th><span>Sifariş</span></th>
                                            <th><span>Növ</span></th>
                                            <th><span>Məbləğ</span></th>
                                            <th><span>Tarix</span></th>

{{--                                            <th><strong>Əməliyyat</strong></th>--}}
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($transactionsFrom as $tF)
                                            <tr>
                                                <td>{{ $tF->id }}</td>
                                                <td>{{ $tF->from_account == 1 ? 'Şirkət' : $result->fullNameWithCodeAndNumber() }}</td>
                                                <td>{{ $tF->accountTypeName($tF->from_account_type) }}</td>
                                                <td>{{ $tF->to_account == 1 ? 'Şirkət' : $tF->getAccountName('accountToName') }}</td>
                                                <td>{{ $tF->order }}</td>
                                                <td>{{ $tF->typeName()}}</td>
                                                <td>{{ number_format($tF->amount, 2, '.', '') }}<span
                                                            class="azn"> AZN</span></td>
                                                <td>{{ $tF->date }}</td>
{{--                                                <td>--}}
{{--                                                    <div class="btn-group">--}}
{{--                                                        <a class="btn btn-default"--}}
{{--                                                           href="http://otos.ru/az/administrator/transaction/return_amount/46367"><i--}}
{{--                                                                    class="icon-arrow-left8"></i></a>--}}
{{--                                                    </div>--}}
{{--                                                </td>--}}
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Taksiyə</h6>
                                <div class="heading-elements">
                                </div>
                            </div>

                            <div class="panel-body">
                                <div>
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th><span>ID</span></th>
                                            <th><span>Göndərən</span></th>
                                            <th><span>Hesabın növü</span></th>
                                            <th><span>Qəbul edən</span></th>
                                            <th><span>Sifariş</span></th>
                                            <th><span>Növ</span></th>
                                            <th><span>Məbləğ</span></th>
                                            <th><span>Tarix</span></th>

{{--                                            <th><strong>Əməliyyat</strong></th>--}}
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($transactionsTo as $to)
                                            <tr>
                                                <td>{{ $to->id }}</td>
                                                <td>{{ $to->from_account == 1 ? 'Şirkət' : $to->getAccountName('accountFromName') }}</td>
                                                <td>{{ $to->accountTypeName($to->from_account_type) }}</td>
                                                <td>{{ $to->to_account == 1 ? 'Şirkət' : $result->fullNameWithCodeAndNumber()  }}</td>
                                                <td>{{ $to->order }}</td>
                                                <td>{{ $to->typeName()}}</td>
                                                <td>{{ number_format($to->amount, 2, '.', '') }}<span
                                                            class="azn"> AZN</span></td>
                                                <td>{{ $to->date }}</td>
{{--                                                <td>--}}
{{--                                                    <div class="btn-group">--}}
{{--                                                        <a class="btn btn-default"--}}
{{--                                                           href="http://otos.ru/az/administrator/transaction/return_amount/46367"><i--}}
{{--                                                                    class="icon-arrow-left8"></i></a>--}}
{{--                                                    </div>--}}
{{--                                                </td>--}}
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="tab-pane fade" id="priorityTransactions">
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Prioritet əməliyyatı</h6>
                                <div class="heading-elements">
                                </div>
                            </div>

                            <div class="panel-body">
                                <div>
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th>
                                                <span> Prioritet</span>
                                            </th>
                                            <th>
                                                <span>Açıqlama</span>
                                            </th>
                                            <th>
                                                <span>Tarix</span>
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach ($priorities as $priority)
                                            <tr>
                                                <td>{{ $priority->priority }}</td>
                                                <td>{{ $priority->description }}</td>
                                                <td>{{ $priority->date }}</td>
                                            </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /available hours -->

                    </div>

                    <div class="tab-pane fade" id="messages">

                        <!-- Orders history -->
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Mesajlar</h6>
                                <div class="heading-elements">
                                </div>
                            </div>

                            <div class="panel-body">
                                <div>
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                        <tr>
                                            <th><span>Göndərən</span></th>
                                            <th><span>Başlıq</span></th>
                                            <th><span>Mesaj</span></th>
                                            <th><span>Oxunmağı</span></th>
                                            <th><span>Tarix</span></th>

                                        </tr>
                                        </thead>
                                        <tbody>

                                        @foreach($messages as $message)
                                            <tr>
                                                <td>{{ $message->user_id }}</td>
                                                <td>{{ $message->title }}</td>
                                                <td>{!! $message->message !!}</td>
                                                <td>{{ $message->read ? 'Oxunub' : 'Oxunmayıb' }}</td>
                                                <td>{{ $message->date }}</td>
                                            </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /orders history -->

                    </div>

                    <div class="tab-pane fade" id="taxi_devices">

                        <!-- Orders history -->
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Device</h6>
                                <div class="heading-elements">
                                </div>
                            </div>

                            <div class="panel-body">
                                <div>
                                </div>
                            </div>
                        </div>
                        <!-- /orders history -->

                    </div>



                    <div class="tab-pane fade" id="foto_nezaret">

                        <!-- Orders history -->
                        <div class="panel panel-flat">
                            <div class="panel-heading">
                                <h6 class="panel-title">Foto nəzarət</h6>
                                <div class="heading-elements">
                                </div>
                            </div>

                            <div class="panel-body" >
                                <div class="row">
                                    @for($i=1;$i <= 4; $i++)
                                        <div class="col-md-6">
                                            <div class="thumbnail">
                                                <a class="image-photo-control"
                                                   href="javascript:;">
                                                    <img
                                                        src="/taxi_photo_control/{{$result ? $result->code ."-".$i.".jpg" : "" }}"
                                                        style="height: 300px;"/>
                                                </a>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>









                        </div>
                        <!-- /orders history -->

                    </div>


                </div>
                <!-- /tab content -->

            </div>
            <!-- /main content -->

        </div>
        <!-- /page content -->

    </div>
    <!-- /page container -->

@endsection




<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
               <img src="https://images.unsplash.com/photo-1505761671935-60b3a7427bad?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&w=1000&q=80" width="100%">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</div>


@section('script')
<script>
    var order_id = '';
    var edit_pencil = '';
    var cancel_order = '';
    var status_icon = ''
    $("tbody").on('click', '.cancel-order', function () {
        $('input[name="is_balance_penalty"]').prop('checked', false);
        $('input[name="is_priority_penalty"]').prop('checked', false);
        $("#remove-order-span").html($(this).attr('data-order-id'));
        order_id = $(this).attr('data-order-id');
        console.log(order_id);
        cancel_order = $(this);
        edit_pencil = $(this).siblings('.edit-pencil');
        status_icon = $(this).parents('tr').find('.status-text');

    });
    $('.cancel-order-buttons').on('click', function (e) {
        e.preventDefault();


        var is_balance_penalty = '';
        var is_priority_penalty = '';
        if ($('input[name="is_balance_penalty"]').prop("checked")) {
            is_balance_penalty = 1;
        } else {
            is_balance_penalty = 0;
        }

        if ($('input[name="is_priority_penalty"]').prop("checked")) {
            is_priority_penalty = 1;
        } else {
            is_priority_penalty = 0;
        }

        $('.closee').click();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            type: 'post',
            url: '{{ route('postOrderDispetcerOrOperatorCancel') }}',
            dataType: 'json',
            data: {
                order_id: order_id,
                is_balance_penalty: is_balance_penalty,
                is_priority_penalty: is_priority_penalty,
            },

            success: function (data) {
                if (data['success']) {
                    swal({
                        title: "Silindi",
                        type: "success",
                        confirmButtonColor: "#4CAF50"
                    });
                    edit_pencil.hide();
                    cancel_order.hide();
                    status_icon.html('<button class="btn btn-danger">Ləğv</button>');
                    console.log(data);
                } else {
                    swal({
                        title: "Silə bilməzsiniz",
                        text: data['message'],
                        type: "error",
                        confirmButtonColor: "#F44336"
                    });
                }
            }
        });

    });
    $(".taxi_img").click(function(){
        $(".modal-body img").attr("src",$(this).attr("src"));
    });
</script>

@endsection
