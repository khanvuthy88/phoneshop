@extends('layouts.backend')
@section('title', trans('app.stock_transfer'))
@section('content')

@endsection
<main class="app-content">
    <div class="tile">
        <h3 class="page-heading">{{ trans('app.stock_transfer') . ' - ' . trans('app.detail') }}</h3>
        @include('partial.flash-message')

        <div class="row">
            <div class="col-lg-6">
                <h5>{{ trans('app.transfer_info') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <tbody>
                            <tr>
                                <td width="30%">{{ trans('app.transfer_date') }}</td>
                                <td>{{ displayDate($transfer->transfer_date) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.transfer_status') }}</td>
                                <td>@include('partial.transfer-status-label')</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.original_location') }}</td>
                                <td>@include('partial.branch-detail-link', ['branch' => $transfer->fromWarehouse])</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.target_location') }}</td>
                                <td>@include('partial.branch-detail-link', ['branch' => $transfer->toWarehouse])</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.invoice_id') }}</td>
                                <td>{{ $transfer->reference_no }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.shipping_cost') }}</td>
                                <td>$ {{ decimalNumber($transfer->shipping_cost, true) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.document') }}</td>
                                <td>@include('partial.transfer-doc-view')</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.note') }}</td>
                                <td>{!! $transfer->note !!}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-6">
                <h5>{{ trans('app.product_table') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ trans('app.no_sign') }}</th>
                                <th>{{ trans('app.product') }}</th>
                                <th>{{ trans('app.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transfer->details as $transferDetail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>@include('partial.product-detail-link', ['product' => $transferDetail->product])</td>
                                    <td>{{ $transferDetail->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
