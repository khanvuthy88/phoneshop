@extends('layouts/backend')
@section('title', trans('app.sale'))
@section('css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap-fileinput.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert.css') }}">
@endsection
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.sale') . ' - ' . $title }}</h3>
            @include('partial.flash-message')

            <form method="post" id="sale-form" class="validated-form no-auto-submit"
                  action="{{ route('sale.save', SaleType::SALE) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="sale_id" value="{{$sale->id}}">
                <div class="row">
                    {{-- Client --}}
                    <div class="col-lg-4 form-group">
                        <label for="client" class="control-label">
                            {{ trans('app.client') }} <span class="required">*</span>
                        </label>
                        <div class="input-group">
                            <select name="client" id="client" class="form-control select2" required>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ selectedOption($client->id, old('client', $sale->client_id)) }} @if($client->is_default) selected @endif>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        <button type="button" id="add-client" class="btn btn-primary">{{ trans('app.add') }}</button>
                        </div>
                    </div>
                    <div class="col-lg-4 form-group client-addition hide">
                        <label for="client_name" class="control-label">
                            {{ trans('app.client_name') }}
                        </label>
                        <input type="text" name="client_name" id="client_name" class="form-control"
                               placeholder="{{ trans('app.client_name') }}" value="{{ old('client_name') }}">
                    </div>
                    <div class="col-lg-4 form-group client-addition hide">
                        <label for="phone_number" class="control-label">
                            {{ trans('app.phone_number') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="phone_number" id="phone_number" class="form-control" required
                               placeholder="{{ trans('app.phone_number') }}" value="{{ old('phone_number') }}">
                    </div>
                </div>
                <div class="row">
                    @if (isAdmin())
                        {{-- Branch --}}
                        <div class="col-lg-4 form-group">
                            <label for="branch" class="control-label">
                                {{ trans('app.branch') }} <span class="required">*</span>
                            </label>
                            <select name="branch" id="branch" class="form-control select2" required>
                                <option value="">{{ trans('app.select_option') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ selectedOption($branch->id, old('branch', $sale->warehouse_id)) }}>
                                        {{ $branch->location }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Agent --}}
                        <div class="col-lg-4 form-group">
                            <label for="agent" class="control-label">
                                {{ trans('app.agent') }} <span class="required">*</span>
                            </label>
                            <select name="agent" id="agent" class="form-control select2" required>
                                <option value="">{{ trans('app.select_option') }}</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ selectedOption($agent->id, old('agent', $sale->staff_id)) }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    {{-- Product --}}
                    <div class="col-lg-4 form-group">
                        <label for="product" class="control-label">
                            {{ trans('app.product') }}
                        </label>
                        <div class="input-group">
                            <select name="product" id="product" class="form-control select2">
                                <option value="">{{ trans('app.select_option') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" {{ selectedOption($product->id, old('product')) }}
                                        data-name="{{ $product->name }}" data-code="{{ $product->code }}"
                                        data-stock-qty="{{ $product->quantity }}" data-price="{{$product->price}}">
                                        {{ $product->name }} ({{ trans('app.code') . ' : ' . ($product->code ?? trans('app.none')) }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="add-product" class="btn btn-primary">{{ trans('app.insert') }}</button>
                        </div>
                    </div>
                </div>

                {{-- Product list --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>{{ trans('app.product_table') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <table id="sale-product-table" class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('app.name') }}</th>
                                                <th>{{ trans('app.code') }}</th>
                                                <th>{{ trans('app.in-stock_quantity') }}</th>
                                                <th>{{ trans('app.sale_quantity') }}</th>
                                                <th>{{ trans('app.unit_price') }}</th>
                                                <th>{{ trans('app.sub_total') }}</th>
                                                <th>{{ trans('app.delete') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($details as $item)
                                                <tr data-id="{{$item->product_id}}"> 
                                                    <input type="hidden" name="products[item_id]" value="{{$item->id}}">
                                                    <input type="hidden" name="products[{{$item->product_id}}][id]" value="{{$item->product_id}}">
                                                    <input type="hidden" name="products[{{$item->product_id}}][name]" value="{{$item->product->name}}">
                                                    <input type="hidden" name="products[{{$item->product_id}}][code]" value="{{$item->product->code}}">
                                                    <td>{{$item->product->name}}</td>
                                                    <td>{{$item->product->code ?? trans('app.none')}}</td>
                                                    <td></td>
                                                    <td width="25%"><input type="text" name="products[{{$item->product_id}}][quantity]" class="form-control integer-input quantity" min="1" max="10000" required value="{{$item->quantity}}"></td>
                                                    <td width="25%"><input type="text" name="products[{{$item->product_id}}][price]" class="form-control integer-input unit_price" min="1" max="10000" required value="{{$item->unit_price}}"></td>
                                                    <td width="25%"><input type="text" name="products[{{$item->product_id}}][sub_total]" class="form-control integer-input sub_total" min="1" max="10000" required value="{{$item->grand_total}}" readonly></td>
                                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="rmProduct(this)"><i class="fa fa-trash-o"></i></button></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5" align="right"><b>{{ trans('app.grand_total') }}</b></td>
                                                <td colspan="2"><span class="shown_total_price"></span></td>
                                                <input type="hidden" name="total_price" class="total_price" value="0">
                                            </tr>
                                            <tr>
                                                <td colspan="5" align="right"><b>{{ trans('app.paid_amount') }}</b></td>
                                                <td colspan="2"><input type="text" name="paid_amount" class="form-control integer-input paid_amount" required value="0"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" align="right"><b>{{ trans('app.balance') }}</b></td>
                                                <td colspan="2"><span class="shown_balance_amount"></span></td>
                                                <input type="hidden" name="balance_amount" class="balance_amount" value="0">
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Transfer date --}}
                    <div class="col-lg-4 form-group">
                        <label for="sale_date" class="control-label">
                            {{ trans('app.sale_date') }} <span class="required">*</span>
                        </label>
                        <input type="text" name="sale_date" id="sale_date" class="form-control date-picker" required
                               placeholder="{{ trans('app.date_placeholder') }}" value="{{ old('sale_date', displayDate($sale->sale_date)) ?? date('d-m-Y') }}">
                    </div>
                    <div class="col-lg-4 form-group">
                        <label for="sale_date" class="control-label">
                            {{ trans('app.sale_status') }} <span class="required">*</span>
                        </label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">{{ trans('app.select_option') }}</option>
                            @foreach (saleStatuses() as $k => $_sta)
                                <option value="{{ $k }}" {{ selectedOption($k, old('status', $sale->status)) }}>
                                    {{ $_sta }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    {{-- Note --}}
                    <div class="col-lg-4 form-group">
                        <label for="note" class="control-label">
                            {{ trans('app.note') }}
                        </label>
                        <input type="text" name="note" id="note" class="form-control" value="{{ old('note', $sale->note) }}">
                    </div>
                </div>
                {{-- Button save or edit --}}
                <div class="row">
                    <div class="col-lg-12 text-right">
                       @include('partial.button-save')
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection
@section('js')
    <script>
        var codeLabel = '{{ trans('app.code') }}';
        var noneLabel = '{{ trans('app.none') }}';

        // When change branch
        var agentSelectLabel = emptyOptionElm;
        var agentRetrievalUrl = '{{ route('staff.get_agents', ':branchId') }}';
    </script>
    <script src="{{ asset('js/bootstrap-fileinput.js') }}"></script>
    <script src="{{ asset('js/bootstrap-fileinput-fa-theme.js') }}"></script>
    <script src="{{ asset('js/init-file-input.js') }}"></script>
    <script src="{{ asset('js/jquery-number.min.js') }}"></script>
    <script src="{{ asset('js/number.js') }}"></script>
    <script src="{{ asset('js/jquery-mask.min.js') }}"></script>
    <script src="{{ asset('js/mask.js') }}"></script>
    <script src="{{ asset('js/date-time-picker.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/form-validation.js') }}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('js/agent-retrieval.js') }}"></script>
    <script src="{{ asset('js/sale.js') }}"></script>
@endsection
