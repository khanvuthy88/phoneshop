@extends('layouts/backend')
@section('title', trans('app.loan'))
@section('css')
    <link rel="stylesheet" href="{{ asset('css/bootstrap4-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert.css') }}">
@endsection
@section('content')
    @php
        $isFormShowType = ($formType == FormType::SHOW_TYPE);
        $disabledFormType = ($isFormShowType ? 'disabled' : '');
        $requiredFormType = ($formType != FormType::SHOW_TYPE ? '<span class="required">*</span>' : '');
    @endphp

    <main class="app-content">
        <div class="tile">
            <h3 class="page-heading">{{ trans('app.loan') . ' - ' . $title }}</h3>
            @include('partial/flash-message')
            <form id="loan-form" method="post" class="no-auto-submit" action="{{ route('loan.save', $loan) }}">
                @csrf

                <input type="hidden" name="form_type" value="{{ $formType }}">
                @isset($loan->id)
                    <input type="hidden" name="id" value="{{ $loan->id }}">
                @endisset

                {{-- Loan info --}}
                <div class="row">
                    <fieldset class="col-lg-12">
                        <legend><h5>{{ trans('app.loan_information') }}</h5></legend>
                        <div class="row">
                            @if (isAdmin())
                                {{-- Branch --}}
                                <div class="col-lg-4 form-group">
                                    <label for="branch" class="control-label">
                                        {{ trans('app.branch') }} {!! $requiredFormType !!}
                                    </label>
                                    @if ($isFormShowType)
                                        <input type="text" class="form-control" value="{{ $loan->branch->location }}" disabled>
                                    @else
                                        <select name="branch" id="branch" class="form-control select2" required {{ $disabledFormType }}>
                                            <option value="">{{ trans('app.select_option') }}</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ selectedOption($branch->id, old('branch'), $loan->branch_id) }}>
                                                    {{ $branch->location }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endif

                            {{-- Account number --}}
                            <div class="col-lg-8 form-group">
                                <label for="account_number_append" class="control-label">
                                    {{ trans('app.account_number') }} <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    {{-- Loan code auto-generated --}}
                                    <input type="text" name="account_number" id="account_number" class="form-control"
                                           value="{{ $loan->account_number ?? '' }}"
                                           placeholder="{{ trans('app.loan_code') }}" disabled>
                                    {{-- Wing code --}}
                                    <input type="text" name="wing_code" id="wing_code" class="form-control"
                                           value="{{ old('wing_code') ?? $loan->wing_code  }}" required
                                           placeholder="{{ trans('app.wing_code') . ' *' }}" {{ $disabledFormType }}>
                                    {{-- Client code --}}
                                    <input type="text" name="client_code" id="client_code" class="form-control"
                                           value="{{ old('client_code') ?? $loan->client_code }}" required
                                           placeholder="{{ trans('app.client_code') . ' *' }}" {{ $disabledFormType }}>
                                </div>
                            </div>

                            @if (isAdmin())
                                {{-- Agent --}}
                                <div class="col-lg-4 form-group">
                                    <label for="agent" class="control-label">
                                        {{ trans('app.agent') }} {!! $requiredFormType !!}
                                    </label>
                                    @if ($isFormShowType)
                                        <input type="text" class="form-control" value="{{ $loan->staff->name }}" disabled>
                                    @else
                                        <select name="agent" id="agent" class="form-control select2" required {{ $disabledFormType }}>
                                            <option value="">{{ trans('app.select_option') }}</option>
                                            @foreach ($agents as $agent)
                                                <option value="{{ $agent->id }}" {{ selectedOption($agent->id, old('agent'), $loan->staff_id) }}>
                                                    {{ $agent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            @endif

                            {{-- Client --}}
                            <div class="col-lg-4 form-group">
                                <label for="client" class="control-label">
                                    {{ trans('app.client') }} {!! $requiredFormType !!}
                                </label>
                                @if ($isFormShowType)
                                    <input type="text" class="form-control" value="{{ $loan->client->name }}" disabled>
                                @else
                                    <select name="client" id="client" class="form-control select2" required {{ $disabledFormType }}>
                                        <option value="">{{ trans('app.select_option') }}</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" {{ selectedOption($client->id, old('client'), $loan->client_id) }}>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            {{-- Product --}}
                            <div class="col-lg-4 form-group">
                                <label for="product" class="control-label">
                                    {{ trans('app.product') }} {!! $requiredFormType !!}
                                </label>
                                @if ($isFormShowType)
                                    <input type="text" class="form-control" value="{{ $loan->product->name }}" disabled>
                                @else
                                    <select name="product" id="product" class="form-control select2" required {{ $disabledFormType }}>
                                        <option data-product-price="" value="">{{ trans('app.select_option') }}</option>
                                        @foreach ($products as $product)
                                            <option data-product-price="{{ $product->price }}" value="{{ $product->id }}"
                                                    {{ selectedOption($product->id, old('product'), $loan->product_id) }}>
                                                {{ $product->name . ' - $ ' . decimalNumber($product->price, true) }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            {{-- Product price --}}
                            <div class="col-lg-4 form-group">
                                <label for="product_price" class="control-label">
                                    {{ trans('app.product_price') }} ($)
                                </label>
                                <input type="text" name="product_price" id="product_price" class="form-control decimal-input"
                                       value="{{ old('product_price') ?? $loan->product_price }}" {{ $disabledFormType }}>
                            </div>

                            {{-- Product IME --}}
                            <div class="col-lg-4 form-group">
                                <label for="product_ime" class="control-label">
                                    {{ trans('app.product_ime') }}
                                </label>
                                <input type="text" name="product_ime" id="product_ime" class="form-control"
                                       value="{{ old('product_ime') ?? $loan->product_ime }}" {{ $disabledFormType }}>
                            </div>

                            {{-- Note --}}
                            <div class="col-lg-4 form-group">
                                <label for="note" class="control-label">
                                    {{ trans('app.note') }}
                                </label>
                                <input type="text" name="note" id="note" class="form-control"
                                       value="{{ old('note') ?? $loan->note }}" {{ $disabledFormType }}>
                                {{--<textarea name="note" id="note" class="form-control" {{ $disabledFormType }} style="min-height: 50px;">{{ $loan->note ?? old('note') }}</textarea>--}}
                            </div>
                        </div>
                    </fieldset>
                </div>
                <br>

                {{-- Payment info --}}
                <div class="row">
                    <fieldset class="col-lg-12">
                        <legend><h5>{{ trans('app.payment_information') }}</h5></legend>
                        <div class="row">
                            {{-- Payment schedule type --}}
                            <div class="col-lg-4 form-group">
                                <label for="schedule_type" class="control-label">
                                    {{ trans('app.payment_schedule_type') }} {!! $requiredFormType !!}
                                </label>
                                <select name="schedule_type" id="schedule_type" class="form-control select2-no-search" required {{ $disabledFormType }}>
                                    <option value="{{ PaymentScheduleType::EQUAL_PAYMENT }}">
                                        {{ trans('app.equal_payment') }}
                                    </option>
                                    {{--<option value="">{{ trans('app.select_option') }}</option>
                                    @foreach (paymentScheduleTypes() as $typeKey => $typeTitle)
                                        <option value="{{ $typeKey }}"
                                            {{ !is_null(old('schedule_type'))
                                                ? (old('schedule_type') == $typeKey ? 'selected' : '')
                                                : ($loan->schedule_type == $typeKey ? 'selected' : '')
                                            }}>
                                            {{ $typeTitle }}
                                        </option>
                                    @endforeach--}}
                                </select>
                            </div>

                            {{-- Loan amount --}}
                            <div class="col-lg-4 form-group">
                                <label for="loan_amount" class="control-label">
                                    {{ trans('app.loan_amount') }} ($) {!! $requiredFormType !!}
                                </label>
                                <input type="text" name="loan_amount" id="loan_amount" class="form-control decimal-input" required
                                       value="{{ old('loan_amount') ?? $loan->loan_amount }}" readonly>
                            </div>

                            {{-- Depreciation amount --}}
                            <div class="col-lg-4 form-group">
                                <label for="depreciation_amount" class="control-label">
                                    {{ trans('app.depreciation_amount') }} ($) {!! $requiredFormType !!}
                                </label>
                                <input type="text" name="depreciation_amount" id="depreciation_amount" class="form-control decimal-input"
                                       value="{{ old('depreciation_amount') ?? $loan->depreciation_amount }}" required {{ $disabledFormType }}>
                            </div>
                        </div>
                        <div class="row">
                            {{-- Down payment amount --}}
                            <div class="col-lg-4 form-group">
                                <label for="down_payment_amount" class="control-label">
                                    {{ trans('app.down_payment_amount') }} ($)
                                </label>
                                <input type="text" name="down_payment_amount" id="down_payment_amount" class="form-control decimal-input"
                                       value="{{ old('down_payment_amount') ?? $loan->down_payment_amount }}" readonly {{ $disabledFormType }}>
                            </div>

                            {{-- Interest rate --}}
                            <div class="col-lg-4 form-group">
                                <label for="interest_rate" class="control-label">
                                    <span id="rate_text">{{ trans('app.interest_rate') }}</span> (%)
                                    <span id="rate_sign" class="required"></span>
                                </label>
                                <input type="text" name="interest_rate" id="interest_rate" class="form-control decimal-input"
                                       value="{{ old('interest_rate') ?? $loan->interest_rate }}" required min="0" {{ $disabledFormType }}
                                       {{-- $isFormShowType || in_array($loan->schedule_type, ['', PaymentScheduleType::FLAT_INTEREST]) ? 'disabled' : '' --}}>
                            </div>

                            {{-- Installment --}}
                            <div class="col-lg-4 form-group">
                                <label for="installment" class="control-label">
                                    {{ trans('app.installment') }} {!! $requiredFormType !!}
                                </label>
                                <input type="text" name="installment" id="installment" class="form-control integer-input"
                                       value="{{ old('installment') ?? $loan->installment }}" required {{ $disabledFormType }}>
                            </div>
                        </div>
                        <div class="row">
                            {{-- Payment frequency --}}
                            <div class="col-lg-4 form-group">
                                <label for="payment_per_month" class="control-label">
                                    {{ trans('app.number_payment_per_month') }} {!! $requiredFormType !!}
                                </label>
                                <select name="payment_per_month" id="payment_per_month" class="form-control" required disabled>
                                    <option value="1">{{ trans('app.once') }}</option>
                                    <option value="2" {{ $loan->payment_per_month == 2 || old('payment_per_month') == 2 ? 'selected' : '' }}>
                                        {{ trans('app.twice') }}
                                    </option>
                                </select>
                                <input type="hidden" name="payment_per_month" value="1">
                            </div>

                            {{-- Loan start date --}}
                            <div class="col-lg-4 form-group">
                                <label for="loan_start_date" class="control-label">
                                    {{ trans('app.loan_start_date') }} {!! $requiredFormType !!}
                                </label>
                                <input type="text" name="loan_start_date" id="loan_start_date" class="form-control date-picker"
                                       placeholder="{{ trans('app.date_placeholder') }}" required {{ $disabledFormType }}
                                       value="{{ old('loan_start_date') ?? displayDate($loan->loan_start_date) ?? date('d-m-Y') }}">
                            </div>

                            {{-- First payment date --}}
                            <div class="col-lg-4 form-group">
                                <label for="first_payment_date" class="control-label">
                                    {{ trans('app.first_payment_date') }}
                                </label>
                                <input type="text" name="first_payment_date" id="first_payment_date" class="form-control date-picker"
                                       placeholder="{{ trans('app.date_placeholder') }}" {{ $disabledFormType }}
                                       value="{{ old('first_payment_date') ?? displayDate($loan->first_payment_date ?? oneMonthIncrement(date('Y-m-d'))) }}">
                            </div>
                        </div>

                        <div class="row" {{ $isFormShowType ? 'style=display:none;' : '' }}>
                            {{-- Message to display when there is error in data validation --}}
                            <div class="col-lg-12 text-center">
                                <h6 id="error-msg" class="text-danger"></h6>
                            </div>

                            {{-- Calculate payment schedule button --}}
                            <div class="col-lg-12 text-center">
                                <button type="button" id="calculate-payment" class="btn btn-info">
                                    {{ trans('app.calculate_payment_schedule') }}
                                </button>
                            </div>
                        </div>
                        <br>

                        {{-- Payment schedule table --}}
                        <div class="row">
                            <div class="col-lg-12 table-responsive">
                                <table style="display: none;" id="schedule-table" class="table table-bordered table-hover table-striped">
                                </table>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <br>

                {{-- Misc buttons --}}
                <div class="row">
                    <div class="col-lg-12 text-center">
                        @if ($isFormShowType)
                            {{-- Pending loan --}}
                            @if ($loan->status == LoanStatus::PENDING)
                                {{-- Reject loan --}}
                                <button type="button" id="reject_loan" class="btn btn-danger mb-1"
                                    data-url="{{ route('loan.change_status', [$loan->id, LoanStatus::REJECTED]) }}">
                                    <i class="fa fa-times pr-1"></i> {{ trans('app.cancel') }}
                                </button>

                                {{-- Approve loan --}}
                                <button type="button" id="approve_loan" class="btn btn-success mb-1"
                                    data-url="{{ route('loan.change_status', [$loan->id, LoanStatus::ACTIVE]) }}">
                                    <i class="fa fa-check pr-1"></i> {{ trans('app.approve') }}
                                </button>
                            @endif

                            @if (isAdmin() && ($loan->status == LoanStatus::PENDING || !isPaidLoan($loan->id)))
                                <a href="{{ route('loan.edit', $loan->id) }}" class="btn btn-primary mb-1">
                                    <i class="fa fa-pencil-square-o pr-1"></i> {{ trans('app.edit') }}
                                </a>
                            @endif

                            {{-- Print contract --}}
                            @if (in_array($loan->status, [LoanStatus::ACTIVE, LoanStatus::PAID]))
                                <a class="btn btn-success mb-1" target="_blank" href="{{ route('loan.print_contract', $loan) }}">
                                    <i class="fa fa-print pr-1"></i> {{ trans('app.print_contract') }}
                                </a>
                            @endif
                        @else
                            @include('partial/button-save')
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </main>
@endsection
@section('js')
    <script>
        var formType = '{{ $formType }}';
        var formShowType = '{{ FormType::SHOW_TYPE }}';
        var equalPaymentSchedule = '{{ PaymentScheduleType::EQUAL_PAYMENT }}';
        var flatInterestSchedule = '{{ PaymentScheduleType::FLAT_INTEREST }}';
        var declineInterestSchedule = '{{ PaymentScheduleType::DECLINE_INTEREST }}';
        var scheduleRetrievalUrl = '{{ route('loan.get_payment_schedule') }}';

        var loanRateLabel = '{{ trans('app.loan_rate') }}';
        var interestRateLabel = '{{ trans('app.interest_rate') }}';
        var noLabel = '{{ trans('app.no_sign') }}';
        var paymentDateLabel = '{{ trans('app.payment_date') }}';
        var paymentAmountLabel = '{{ trans('app.payment_amount') }}';
        var totalLabel = '{{ trans('app.total') }}';
        var principalLabel = '{{ trans('app.principal') }}';
        var interestLabel = '{{ trans('app.interest') }}';
        var outstandingLabel = '{{ trans('app.outstanding') }}';

        // When change branch
        var agentSelectLabel = emptyOptionElm;
        var agentRetrievalUrl = '{{ route('staff.get_agents', ':branchId') }}';
    </script>
    <script src="{{ asset('plugins/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('js/tinymce.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/select-box.js') }}"></script>
    <script src="{{ asset('js/bootstrap4-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('js/jquery-mask.min.js') }}"></script>
    <script src="{{ asset('js/date-time-picker.js') }}"></script>
    <script src="{{ asset('js/jquery-number.min.js') }}"></script>
    <script src="{{ asset('js/number.js') }}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('js/agent-retrieval.js') }}"></script>
    <script src="{{ asset('js/loan.js') }}"></script>
@endsection
