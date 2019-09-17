@extends('layouts/backend')
@section('title', trans('app.payment_report'))
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.payment_report') }}</h3>
            <form method="get" action="{{ route('report.client_payment') }}">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-6 col-md-4">
                                <label for="start_date">{{ trans('app.start_date') }}</label>
                                <input type="text" name="start_date" id="start_date" class="form-control date-picker"
                                       value="{{ request('start_date') }}" placeholder="{{ trans('app.date_placeholder') }}">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label for="end_date">{{ trans('app.end_date') }}</label>
                                <input type="text" name="end_date" id="end_date" class="form-control date-picker"
                                       value="{{ request('end_date') }}" placeholder="{{ trans('app.date_placeholder') }}">
                            </div>
                            <div class="col-sm-6 col-md-4">
                                @include('partial.button-search', [
                                    'class' => 'mt-4'
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <br>
            @include('partial.item-count-label')
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans('app.no_sign') }}</th>
                            <th>@sortablelink('payment_date', trans('app.payment_date'))</th>
                            <th>@sortablelink('payment_amount', trans('app.paid_amount'))</th>
                            <th>@sortablelink('payment_method', trans('app.payment_method'))</th>
                            <th>{{ trans('app.client_code') }}</th>
                            <th>{{ trans('app.client') }}</th>
                            <th>@sortablelink('reference_number', trans('app.reference_number'))</th>
                            <th>{{ trans('app.receiver') }}</th>
                            <th>{{ trans('app.note') }}</th>
                            <th>{{ trans('app.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $offset++ }}</td>
                                <td>{{ displayDate($payment->payment_date) }}</td>
                                <td><b>$ {{ decimalNumber($payment->payment_amount, true) }}</b></td>
                                <td>{{ paymentMethods($payment->payment_method) }}</td>
                                <td>@include('partial.loan-detail-link', ['loan' => $payment->loan])</td>
                                <td>@include('partial.client-detail-link', ['client' => $payment->client])</td>
                                <td>{{ $payment->reference_number }}</td>
                                <td>{{ $payment->user->name ?? trans('app.n/a') }}</td>
                                <td>{{ $payment->note }}</td>
                                <td class="text-center">
                                    {{--<a href="{{ route('report.client_payment_receipt', $payment) }}" class="btn btn-info btn-sm mb-1" target="_blank">
                                        {{ trans('app.print_receipt') }}
                                    </a>
                                    <br>--}}
                                    <a href="{{ route('report.loan_portfolio', $payment->client) }}" class="btn btn-info btn-sm mb-1">
                                        {{ trans('app.loan_portfolio') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {!! $payments->appends(Request::except('page'))->render() !!}
            </div>
        </div>
    </main>
@endsection

@section('js')
    <script src="{{ asset('js/jquery-mask.min.js') }}"></script>
    <script src="{{ asset('js/date-time-picker.js') }}"></script>
@endsection
