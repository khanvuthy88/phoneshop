@extends('layouts.backend')
@section('title', trans('app.purchase'))
@section('content')

@endsection
<main class="app-content">
    <div class="tile">
        <h3 class="page-heading">{{ trans('app.purchase') . ' - ' . trans('app.detail') }}</h3>
        @include('partial.flash-message')

        <div class="row">
            <div class="col-lg-6">
                <h5>{{ trans('app.purchase_info') }}</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <tbody>
                            <tr>
                                <td width="30%">{{ trans('app.purchase_date') }}</td>
                                <td>{{ displayDate($purchase->purchase_date) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.purchase_status') }}</td>
                                <td>@include('partial.purchase-status-label')</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.location') }}</td>
                                <td>@include('partial.branch-detail-link', ['branch' => $purchase->warehouse])</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.invoice_id') }}</td>
                                <td>{{ $purchase->reference_no }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.total_cost') }}</td>
                                <td>$ {{ decimalNumber($purchase->total_cost, true) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.discount') }}</td>
                                <td>$ {{ decimalNumber($purchase->total_discount, true) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.shipping_cost') }}</td>
                                <td>$ {{ decimalNumber($purchase->shipping_cost, true) }}</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.document') }}</td>
                                <td>@include('partial.purchase-doc-view')</td>
                            </tr>
                            <tr>
                                <td>{{ trans('app.note') }}</td>
                                <td>{!! $purchase->note !!}</td>
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
                                {{--<th>{{ trans('app.purchase_status') }}</th>--}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->details as $purchaseDetail)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>@include('partial.product-detail-link', ['product' => $purchaseDetail->product])</td>
                                    <td>{{ $purchaseDetail->quantity }}</td>
                                    {{--<td>{{ '' }}</td>--}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
