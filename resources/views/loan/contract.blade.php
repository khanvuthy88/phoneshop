<!DOCTYPE html>
<html lang="en">
<head>
	<title>{{ $generalSetting->site_title . ' - ' . trans('app.contract') }}</title>
	<meta charset="utf-8">
	<meta name="keywords" content="">
	<meta name="description" content="">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset($generalSetting->site_logo) }}" sizes="32x32">
    <link  rel="stylesheet" href="https://fonts.googleapis.com/css?family=Battambang|Moul">
	<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
	<style>
        .content-wrapper {
            margin: 0 auto;
            width: 800px;
            padding: 10px;
        }

        .moul-font {
            font-family: 'Moul', 'Arial Black', sans-serif !important;
        }
        
        table thead th {
            text-align: center;
        }

        .table-bordered {
            border: 1px solid #000;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Moul', 'Arial Black', sans-serif !important;

        }

        p, th, td, li, span {
            font-size: 12px !important;
            font-family: 'Battambang', Arial, sans-serif !important;
        }

        p, li, span {
            line-height: 1.8;
        }

        .content-header {
            margin-bottom: 20px;
        }

        .content-header .left-logo-wrapper {
            padding-right: 0 !important;
        }

        .content-header .right-logo-wrapper {
            padding-left: 0 !important;
        }
        .content-header .branch-name {
            margin-bottom: 25px;
            font-size: 24px;
        }

        .content-header .sub-title {
            margin-bottom: 30px;
            font-size: 16px;
        }

        .content-body .table-info td {
            padding-right: 7px;
            padding-bottom: 5px;
        }

        .content-body #table-schedule {
            margin-top: 20px;
            margin-bottom: 15px;
        }

        #table-schedule caption {
            font-weight: 700;
            font-size: 13px !important;
            font-family: 'Battambang', Arial, sans-serif !important;
            color: #333;
        }

        .content-body .contract-text {
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .content-body .date-wrapper {
            position: relative;
            right: 108px;
        }

        .content-body .thumbprint-content {
            margin-bottom: 110px;
        }

        .thumbprint-content p {
            font-weight: 600;
            font-size: 16px;
        }

        .thumbprint-footer h6 {
            font-weight: 700;
            font-size: 13px;
            font-family: 'Battambang', Arial, sans-serif !important;
        }

        .content-footer .footer-ruler {
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #000;
        }

        .pl-0 {
            padding-left: 0 !important;
        }

		@media print {
			body {
				width: 21cm;
				height: 29.7cm;
				margin: 5mm;
			}

            .content-footer {
                position: fixed;
                bottom: 0;
                right: 0;
                width: 100%;
            }
		}
	</style>
</head>
<body>
    @php
        $branch = $loan->branch;
        $client = $loan->client;
    @endphp

	<div class="content-wrapper">
        <div class="row">
            <div class="col-xs-12">
                <div class="content-header">
                    <div class="row">
                        <div class="col-xs-2 left-logo-wrapper">
                            <img src="{{ asset($branch->logo ?? 'images/contract-phone-1.jpg') }}"
                                 width="90" height="120" alt="" class="pull-right">
                        </div>
                        <div class="col-xs-8 text-center">
                            <h1 class="branch-name">{{ $loan->branch->name }}</h1>
                            <h6 class="sub-title">{{ trans('app.contract_and_payment_schedule') . ' ' . $loan->branch->location }}</h6>
                        </div>
                        <div class="col-xs-2 right-logo-wrapper">
                            <img src="{{ asset($branch->logo_2 ?? 'images/contract-phone-2.jpg') }}" width="90" height="120" alt="">
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <div class="row">
                        <div class="col-xs-8">
                            <table class="table-info" border="0">
                                <tbody>
                                    <tr>
                                        <td>{{ trans('app.loan_code') }}</td>
                                        <td>: {!! $loan->account_number . '/<b>' . $loan->client_code . '</b>' !!}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.wing_account_number') }}</td>
                                        <td>: {{ $loan->wing_code }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.client_name') }}</td>
                                        <td>
                                            : <span class="moul-font">{{ $client->name }}</span>,
                                            {{ trans('app.id_card_number') }} : {{ $client->id_card_number }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.phone_number') }}</td>
                                        <td>
                                            : {{ $client->first_phone }}
                                            {{ isset($client->second_phone) ? ' / ' . $client->second_phone : '' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>{{ trans('app.address') }}</td>
                                        <td>:
                                            @if (isset($client->province_id) || isset($client->district_id) || isset($client->commune_id))
                                                {{ isset($client->commune->name) ? $client->commune->name . ', ' : '' }}
                                                {{ isset($client->district->name) ? $client->district->name . ', ' : '' }}
                                                {{ isset($client->province->name) ? $client->province->name : '' }}
                                            @else
                                                {{ trans('app.n/a') }}
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Sponsor info --}}
                                    @if (!empty($client->sponsor_name))
                                        <tr>
                                            <td>{{ trans('app.sponsor_name') }}</td>
                                            <td>
                                                : <span class="moul-font">{{ $client->sponsor_name }}</span>,
                                                {{ trans('app.id_card_number') }} : {{ $client->sponsor_id_card ?? trans('app.none') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ trans('app.phone_number') }}</td>
                                            <td>
                                                @if (!empty($client->sponsor_phone) || !empty($client->sponsor_phone_2))
                                                    : {{ $client->sponsor_phone }}
                                                    {{ !empty($client->sponsor_phone) && !empty($client->sponsor_phone_2) ? ' / ' : '' }}
                                                    {{ $client->sponsor_phone_2 }}
                                                @else
                                                    {{ trans('app.none') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="col-xs-4 pl-0">
                            <table class="table-info" border="0">
                                <tbody>
                                    <tr>
                                        <td>{{ trans('app.product') }}</td>
                                        <td>: {{ $loan->product->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.product_price') }}</td>
                                        <td>: $ {{ decimalNumber($loan->loan_amount, true) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.depreciation_amount') }}</td>
                                        <td>: $ {{ decimalNumber($loan->depreciation_amount, true) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.loan_amount') }}</td>
                                        <td>: $ {{ decimalNumber($loan->down_payment_amount, true) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.start_date') }}</td>
                                        <td>: {{ displayDate($loan->loan_start_date) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.end_date') }}</td>
                                        <td>: {{ displayDate($loan->schedules[$loan->installment - 1]->payment_date) }}</td>
                                    </tr>
                                    <tr>
                                        <td>{{ trans('app.duration') }}</td>
                                        <td>: {{ $loan->installment . ' ' . trans('app.month') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="table-schedule" class="table table-bordered">
                            {{--<caption class="text-center">{{ trans('app.payment_schedule') }}</caption>--}}
                            <thead>
                                <tr>
                                    <th>{{ trans('app.no_sign') }}</th>
                                    <th>{{ trans('app.payment_date') }}</th>
                                    @include('partial.schedule-type-table-header', ['scheduleType' => $loan->schedule_type])
                                    <th>{{ trans('app.outstanding') }}</th>
                                    <th>{{ trans('app.paid_date') }}</th>
                                    <th>{{ trans('app.paid_amount') }}</th>
                                    <th>{{ trans('app.signature') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($loan->schedules as $schedule)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ displayDate($schedule->payment_date) }}</td>
                                        @include('partial.schedule-type-table-data', [
                                            'scheduleType' => $loan->schedule_type,
                                            'currencySign' => '$ '
                                        ])
                                        <td>$ {{ decimalNumber($schedule->outstanding, true) }}</td>
                                        <td>{{ displayDate($schedule->paid_date) }}</td>
                                        <td>{{ isset($schedule->paid_total) ? '$ ' . decimalNumber($schedule->paid_total, true) : '' }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="contract-text">
                        {!! $loan->branch->contract_text !!}
                    </div>
                    <div class="date-wrapper text-right">
                        <p>ភ្នំពេញ, ថ្ងៃទី {{ date('d') }} ខែ {{ date('m') }} ឆ្នាំ {{ date('Y') }}</p>
                    </div>
                    <div class="thumbprint-content">
                        <div class="row">
                            <div class="col-xs-4">
                                <p>{{ trans('app.client_name') }}</p>
                            </div>
                            <div class="col-xs-4">
                                <p>{{ trans('app.sponsor_name') }}</p>
                            </div>
                            <div class="col-xs-4">
                                <p>{{ trans('app.company_owner_name') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="thumbprint-footer">
                        <div class="row">
                            <div class="col-xs-4">
                                <h5>{{ $client->name }}</h5>
                            </div>
                            <div class="col-xs-4">
                                <h5>{{ $client->sponsor_name }}</h5>
                            </div>
                            <div class="col-xs-4">
                                <h5>ប៊ុន រ៉ូនី</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-footer">
                    <hr class="footer-ruler">
                    <div class="bottom-footer">
                        <div class="row">
                            <div class="col-xs-6">
                                <p>{{ trans('app.address') . ': ' . $loan->branch->address }}</p>
                            </div>
                            <div class="col-xs-6 text-right">
                                <p>Tel:
                                    {{ $loan->branch->phone_1
                                       . (isset($loan->branch->phone_2) ? '/' . $loan->branch->phone_2 : '')
                                       . (isset($loan->branch->phone_3) ? '/' . $loan->branch->phone_3 : '')
                                       . (isset($loan->branch->phone_4) ? '/' . $loan->branch->phone_4 : '')
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
