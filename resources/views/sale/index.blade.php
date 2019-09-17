@extends('layouts/backend')
@section('title', trans('app.sale'))
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.sale') }}</h3>
            @include('partial.flash-message')

            <div class="card">
                <div class="card-header">
                    <form method="get" action="">
                        <div class="row">
                            <div class="col-lg-6">
                                @include('partial.anchor-create', [
                                    'href' => route('sale.create')
                                ])
                            </div>
                            <div class="col-lg-6">
                                @include('partial.search-input-group')
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <br>

            @include('partial.item-count-label')
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('app.no_sign') }}</th>
                            <td>@sortablelink('sale_date', trans('app.sale_date'))</td>
                            <!-- <td>@sortablelink('sale_code', trans('app.sale_code'))</td> -->
                            <th>{{ trans('app.location') }}</th>
                            <th>{{ trans('app.client') }}</th>
                            <th>{{ trans('app.agent') }}</th>
                            <th>{{ trans('app.total_amount') }}</th>
                            <th>{{ trans('app.paid_amount') }}</th>
                            <th>{{ trans('app.status') }}</th>
                            <th>{{ trans('app.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>{{ displayDate($sale->sale_date) }}</td>
                                <!-- <td>{{ $sale->sale_code }}</td> -->
                                <td>@include('partial.branch-detail-link', ['branch' => $sale->warehouse])</td>
                                <td>@include('partial.client-detail-link', ['client' => $sale->client])</td>
                                <td>@include('partial.staff-detail-link', ['staff' => $sale->staff])</td>
                                <td>$ {{ decimalNumber($sale->grand_total, true) }}</td>
                                <td>$ {{ decimalNumber($sale->paid_amount, true) }}</td>
                                <td>@include('partial.sale-status-label', ['sale' => $sale])</td>
                                <td class="text-center">
                                    <!-- @include('partial.anchor-show', [
                                        'href' => route('sale.show', $sale->id)
                                    ]) -->
                                    @include('partial.anchor-edit', [
                                        'href' => route('sale.edit', $sale->id),
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $sales->appends(Request::except('page'))->render() !!}
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
@endsection
