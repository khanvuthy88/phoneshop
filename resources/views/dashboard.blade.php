@extends('layouts.backend')
@section('title', trans('app.dashboard'))
@section('content')
    <main class="app-content">
        @include('partial/flash-message')
        @if($view_data)
        {{-- 4 small boxes --}}
        <div class="row">
            <div class="col-sm-6 col-lg-3">
                <a href="{{ route('loan.index') }}">
                    <div class="widget-small primary coloured-icon">
                        <i class="icon fa fa-money fa-3x"></i>
                        <div class="info">
                            <h6>{{ trans('app.loan_list') }}</h6>
                            <b>{{ $activeLoanCount }}</b>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="{{ route('report.overdue_loan') }}">
                    <div class="widget-small warning coloured-icon">
                        <i class="icon fa fa-clock-o fa-3x"></i>
                        <div class="info">
                            <h6>{{ trans('app.overdue_loan') }}</h6>
                            <b>{{ $overdueLoanCount }}</b>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="{{ route('report.loan', ReportLoanStatus::REJECTED) }}">
                    <div class="widget-small danger coloured-icon">
                        <i class="icon fa fa-ban fa-3x"></i>
                        <div class="info">
                            <h6>{{ trans('app.rejected_loan') }}</h6>
                            <b>{{ $rejectedLoanCount }}</b>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-lg-3">
                <a href="{{ route('client.index') }}">
                    <div class="widget-small success coloured-icon">
                        <i class="icon fa fa-address-book fa-3x"></i>
                        <div class="info">
                            <h6>{{ trans('app.client') }}</h6>
                            <b>{{ $clientCount }}</b>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        {{-- Income chart --}}
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h3 class="card-title">{{ trans('app.payment_in_year') . ' ' . date('Y') }}</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex">
                    <p class="d-flex flex-column">
                        <strong>$ {{ decimalNumber($totalPaidAmount, true) }}</strong>
                    </p>
                </div>

                {{-- Graph chart of interest and principal --}}
                <div class="position-relative mb-4">
                    <canvas id="income-chart" height="300"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                    <span class="mr-2">
                        <i class="fa fa-square text-interest-chart"></i> {{ trans('app.interest') }}
                    </span>
                    <span>
                        <i class="fa fa-square text-principal-chart"></i> {{ trans('app.principal') }}
                    </span>
                </div>
            </div>
        </div>
        @else
            <h4></h4>
        @endif
    </main>
@endsection
@if($view_data)
@section('js')
    <script>
        $(function () {
            var ticksStyle = {
                fontFamily: 'Battambang, Verdana, Roboto, Arial, sans-serif',
                fontColor: '#495057',
                fontStyle: '600',
            };
            var mode = 'index';
            var intersect = true;
            var khmerMonths = <?php echo json_encode(khmerMonths()) ?>;
            var paidPrincipals = <?php echo json_encode($paidPrincipals) ?>;
            var paidInterests = <?php echo json_encode($paidInterests) ?>;

            var incomeChart = new Chart($('#income-chart'), {
                type: 'bar',
                data: {
                    labels: khmerMonths,
                    datasets: [
                        // Paid interest
                        {
                            backgroundColor: '#28a745',
                            borderColor: '#28a745',
                            // data: [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120],
                            data: paidInterests,
                        },
                        // Paid principal
                        {
                            backgroundColor: '#007bff',
                            borderColor: '#007bff',
                            // data: [8, 15, 20, 33, 50, 61, 65, 77, 95, 96, 101, 105],
                            data: paidPrincipals,

                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        mode: mode,
                        intersect: intersect,
                    },
                    hover: {
                        mode: mode,
                        intersect: intersect,
                    },
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            // display: false,
                            gridLines: {
                                display: true,
                                lineWidth: '4px',
                                color: 'rgba(0, 0, 0, .2)',
                                zeroLineColor: 'transparent',
                            },
                            ticks: $.extend({
                                beginAtZero: true,

                                // Include a dollar sign in the ticks
                                callback: function (value, index, values) {
                                    if (value >= 1000) {
                                        value /= 1000;
                                        value += 'k';
                                    }
                                    return '$ ' + value;
                                }
                            }, ticksStyle),
                        }],
                        xAxes: [{
                            display: true,
                            gridLines: {
                                display: false
                            },
                            ticks: ticksStyle,
                        }]
                    }
                }
            });
        });
    </script>
@endsection
@endif
