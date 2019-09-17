@extends('layouts/backend')
@section('title', $title)
@section('css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap4-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert.css') }}">
@endsection
@section('content')
    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ $title  }}</h3>
            @include('partial/flash-message')
            <form method="post" id="payment-form" class="no-auto-submit" action="{{ route('repayment.save', $loan->id) }}">
                @csrf
                <input type="hidden" name="repay_type" value="{{ $repayType }}">

                <div class="row">
                    <div class="col-lg-8">
                        <h5>{{ trans('app.client_information') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <tr>
                                    <td>{{ trans('app.client_name') }} :</td>
                                    <td>@include('partial.client-detail-link', ['client' => $loan->client])</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('app.first_phone') }} :</td>
                                    <td>{{ $loan->client->first_phone }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('app.second_phone') }} :</td>
                                    <td>{{ $loan->client->second_phone ?? trans('app.none') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('app.id_card_number') }} :</td>
                                    <td>{{ $loan->client->id_card_number ?? trans('app.none') }}</td>
                                </tr>
                                <tr>
                                    <td>{{ trans('app.client_code') }} :</td>
                                    <td>@include('partial.loan-detail-link', ['loan' => $loan])</td>
                                </tr>
                            </table>
                        </div>
                        <br>

                        {{-- Payment schedule --}}
                        <h5>{{ trans('app.remaining_payments') }}</h5>
                        @php $isFlatInterestSchedule = ($loan->schedule_type == PaymentScheduleType::FLAT_INTEREST) @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        @if ($repayType == RepayType::ADVANCE_PAY)
                                            <th width="10%">{{ trans('app.advance_pay') }}</th>
                                        @endif
                                        <th>{{ trans('app.payment_date') }}</th>
                                        @if ($isFlatInterestSchedule)
                                            <th>{{ trans('app.payment_amount') }}</th>
                                        @else
                                            <th>{{ trans('app.total') }}</th>
                                            <th>{{ trans('app.principal') }}</th>
                                            <th>{{ trans('app.interest') }}</th>
                                        @endif
                                        <th>{{ trans('app.outstanding') }}</th>
                                        <th>{{ trans('app.paid_date') }}</th>
                                        <th>{{ trans('app.paid_amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($loan->schedules as $schedule)
                                        @php $decimalNumber = ($schedule->interest == 0 ? 2 : 0) @endphp
                                        <tr>
                                            @if ($repayType == RepayType::ADVANCE_PAY)
                                                <td>
                                                    @if ($schedule->paid_interest == null || $schedule->paid_interest == 0)
                                                        <div class="custom-control custom-checkbox text-center">
                                                            <input type="checkbox" name="schedules[]" id="schedule{{ $schedule->id }}"
                                                                   class="custom-control-input schedule" data-principal="{{ $schedule->principal }}"
                                                                   data-schedule-id="{{ $schedule->id }}">
                                                            <label for="schedule{{ $schedule->id }}" class="custom-control-label"></label>
                                                        </div>
                                                    @endif
                                                </td>
                                            @endif
                                            <td>{{ displayDate($schedule->payment_date) }}</td>
                                            @if ($isFlatInterestSchedule)
                                                <td>$ {{ decimalNumber($schedule->principal) }}</td>
                                            @else
                                                <td>$ {{ number_format($schedule->total, $decimalNumber) }}</td>
                                                <td>$ {{ number_format($schedule->principal, $decimalNumber) }}</td>
                                                <td>$ {{ number_format($schedule->interest) }}</td>
                                            @endif
                                            <td>$ {{ decimalNumber($schedule->outstanding) }}</td>
                                            <td>{{ displayDate($schedule->paid_date) }}</td>
                                            <td>{{ isset($schedule->paid_total) ? '$ ' . decimalNumber($schedule->paid_total, true) : '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Payment form --}}
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="col-lg-12 form-group">
                                <label for="payment_date" class="control-label">
                                    {{ trans('app.paid_date') }} <span class="required">*</span>
                                </label>
                                <input type="text" name="payment_date" id="payment_date" class="form-control date-picker"
                                       value="{{ old('payment_date') ?? date('d-m-Y') }}" placeholder="{{ trans('app.date_placeholder') }}" required>
                            </div>
                            <div class="col-lg-12 form-group">
                                <label for="payment_amount" class="control-label">
                                    {{ trans('app.payment_amount') }} ($) <span class="required">*</span>
                                </label>
                                <input type="text" name="payment_amount" id="payment_amount" class="form-control decimal-input"
                                    value="{{ $payoffAmount ?? old('payment_amount') }}" required
                                    {{ in_array($repayType, [RepayType::PAYOFF, RepayType::ADVANCE_PAY]) ? 'readonly' : '' }}>
                            </div>
                            <div class="col-lg-12 form-group">
                                <label for="payment_method" class="control-label">
                                    {{ trans('app.payment_method') }} <span class="required">*</span>
                                </label>
                                <select name="payment_method" id="payment_method" class="form-control select2-no-search" required>
                                    @foreach (paymentMethods() as $methodKey => $methodValue)
                                        <option value="{{ $methodKey }}" {{ $methodKey == old('payment_method') ? 'selected' : '' }}>
                                            {{ $methodValue }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-12 form-group">
                                <label for="penalty_amount" class="control-label">
                                    {{ trans('app.penalty_amount') }} ($)
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control decimal-input" value="{{ $penaltyAmount }}" disabled>
                                    <input type="text" name="penalty_amount" id="penalty_amount" class="form-control decimal-input"
                                           value="{{ old('penalty_amount') }}">
                                </div>
                            </div>
                            <div class="col-lg-12 form-group">
                                <label for="reference_number" class="control-label">
                                    {{ trans('app.reference_number') }}
                                </label>
                                <input type="text" name="reference_number" id="reference_number" class="form-control"
                                       value="{{ old('reference_number') }}">
                            </div>
                            <div class="col-lg-12 form-group">
                                <label for="note" class="control-label">
                                    {{ trans('app.note') }}
                                </label>
                                <textarea name="note" id="note" class="form-control">{{ old('note') }}</textarea>
                            </div>
                            <div class="col-lg-12 text-right">
                                <button type="submit" class="btn btn-success" onclick="confirmFormSubmission($('#payment-form'))">
                                    {{ $repayLabel }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection
@section('js')
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap4-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/jquery-mask.min.js') }}"></script>
    <script src="{{ asset('js/date-time-picker.js') }}"></script>
    <script src="{{ asset('js/jquery-number.min.js') }}"></script>
    <script src="{{ asset('js/number.js') }}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('js/repayment.js') }}"></script>
@endsection
