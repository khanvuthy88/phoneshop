<!DOCTYPE html>
<html lang="km">
<head>
    <title>{{ $generalSetting->site_title }} @hasSection('title') - @endif @yield('title')</title>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="បង់​រំលោះ​ទូរសព្ទ​ដៃ">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <link rel="icon" href="{{ asset($generalSetting->site_logo) }}" sizes="32x32">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Battambang">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/listswap.css') }}">
    <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/planit.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    @yield('css')
</head>
<body class="app sidebar-mini rtl">
    <!-- Navbar -->
    <header class="app-header">
        <a class="app-header__logo" href="{{ route('dashboard') }}" style="padding: 0; line-height: 50px;">
            {{--<img src="" height="50">--}}
            <h4 class="app-header-title">{{ $generalSetting->site_title }}</h4>
        </a>
        
        <!-- Sidebar Toggle Button -->
        <a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
        <!-- Navbar Right Menu -->
        <ul class="app-nav">
            <!-- User Menu -->
            <li class="dropdown">
                <a class="app-nav__item" href="#" data-toggle="dropdown" aria-label="Open Profile Menu">
                    <i class="fa fa-user fa-lg"></i>
                </a>
                <ul class="dropdown-menu settings-menu dropdown-menu-right">
                    {{-- Profile --}}
                    <li>
                        <a class="dropdown-item {{ activeMenu('profile') }}" href="{{ route('user.show_profile', auth()->user()->id) }}">
                            <i class="fa fa-user fa-lg"></i> {{ trans('app.profile') }}
                        </a>
                    </li>

                    @permission('app.setting')
                        {{-- General setting --}}
                        <li>
                            <a class="dropdown-item {{ activeMenu('general', 2) }}" href="{{ route('general_setting.index') }}">
                                <i class="fa fa-gear fa-lg"></i> {{ trans('app.general_setting') }}
                            </a>
                        </li>
                    @endpermission

                    {{-- Logout --}}
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            <i class="fa fa-sign-out fa-lg"></i> {{ trans('app.log_out') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="post" class="d-none">@csrf</form>
                    </li>
                </ul>
            </li>
            <!-- End User Menu -->
        </ul>
    </header>

    <!-- Sidebar Menu -->
    <div class="app-sidebar__overlay" data-toggle="sidebar"></div>
    <aside class="app-sidebar">
        <div class="app-sidebar__user">
            <img class="app-sidebar__user-avatar" src="{{ asset('images/user.png') }}" alt="">
            <div>
                <p class="app-sidebar__user-name">{{ Auth::user()->name }}</p>
            </div>
        </div>
        
        <!-- Sidebar Block -->
        <ul class="app-menu">
            {{-- Dashboard --}}
            <li>
                <a class="app-menu__item {{ activeMenu('dashboard') }}" href="{{ route('dashboard') }}">
                    <i class="app-menu__icon fa fa-dashboard"></i>
                    <span class="app-menu__label">{{ trans('app.dashboard') }}</span>
                </a>
            </li>

            @permission('customer.browse')
            {{-- Client --}}
            <li>
                <a class="app-menu__item {{ activeMenu('client') }}" href="{{ route('client.index') }}">
                    <i class="app-menu__icon fa fa-address-book" aria-hidden="true"></i>
                    <span class="app-menu__label">{{ trans('app.client') }}</span>
                </a>
            </li>
            @endpermission
            @if(auth()->user()->can(['product.browse', 'product-type.browse', 'brand.browse']))
                {{-- Product management --}}
                <li class="treeview {{ activeTreeviewMenu(['product', 'product-category', 'brand']) }}">
                    <a class="app-menu__item" href="#" data-toggle="treeview">
                        <i class="app-menu__icon fa fa-product-hunt"></i>
                        <span class="app-menu__label">{{ trans('app.product') }}</span>
                        <i class="treeview-indicator fa fa-angle-left"></i>
                    </a>

                    <ul class="treeview-menu">
                        @permission('product.browse')
                        {{-- Product --}}
                        <li>
                            <a class="treeview-item {{ activeMenu('product') }}" href="{{ route('product.index') }}">
                                <i class="icon fa fa-product-hunt pr-1"></i>{{ trans('app.product') }}
                            </a>
                        </li>
                        @endpermission
                        @permission('product-type.browse')
                        {{-- Product category --}}
                        <li>
                            <a class="treeview-item {{ activeMenu('product-category') }}" href="{{ route('product_category.index') }}">
                                <i class="icon fa fa-indent pr-1"></i>{{ trans('app.product_category') }}
                            </a>
                        </li>
                        @endpermission
                        @permission('brand.browse')
                        {{-- Brand --}}
                        <li>
                            <a class="treeview-item {{ activeMenu('brand') }}" href="{{ route('brand.index') }}">
                                <i class="icon fa fa-bandcamp pr-1"></i>{{ trans('app.brand') }}
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
            @endif
            @permission('sale.browse')
            <li class="treeview {{ activeTreeviewMenu(['sale']) }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-shopping-cart"></i>
                    <span class="app-menu__label">{{ trans('app.sale') }}</span>
                    <i class="treeview-indicator fa fa-angle-left"></i>
                </a>

                <ul class="treeview-menu">
                    {{-- Sale --}}
                    <li>
                        <a class="treeview-item {{ activeMenu('sale') }}" href="{{ route('sale.index') }}">
                            <i class="icon fa fa-shopping-cart pr-1"></i>{{ trans('app.sale') }}
                        </a>
                    </li>

                </ul>
            </li>
            @endpermission
            @if(auth()->user()->can(['loan.browse', 'loan.pay', 'staff.commission']))
            <li class="treeview {{ activeTreeviewMenu(['loan', 'repayment', 'commission-payment']) }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-money"></i>
                    <span class="app-menu__label">{{ trans('app.loan') }}</span>
                    <i class="treeview-indicator fa fa-angle-left"></i>
                </a>

                <ul class="treeview-menu">
                    @permission('loan.browse')
                    {{-- Loan --}}
                    <li>
                        <a class="treeview-item {{ activeMenu('loan') }}" href="{{ route('loan.index') }}">
                            <i class="icon fa fa-money pr-1"></i>{{ trans('app.loan') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('loan.pay')
                    {{-- Client payment --}}
                    <li>
                        <a class="treeview-item {{ activeMenu('repayment') }}" href="{{ route('repayment.index') }}">
                            <i class="icon fa fa-credit-card pr-1"></i>{{ trans('app.payment') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('staff.commission')
                    {{-- Commission payment --}}
                    <li>
                        <a class="treeview-item {{ activeMenu('commission-payment') }}" href="{{ route('commission-payment.index') }}">
                            <i class="icon fa fa-credit-card pr-1"></i>{{ trans('app.commission_payment') }}
                        </a>
                    </li>
                    @endpermission
                </ul>
            </li>
            @endif
            @permission('po.browse')
                {{-- Purchase --}}
                <li>
                    <a class="app-menu__item {{ activeMenu('purchase') }}" href="{{ route('purchase.index') }}">
                        <i class="app-menu__icon fa fa-cart-plus" aria-hidden="true"></i>
                        <span class="app-menu__label">{{ trans('app.purchase') }}</span>
                    </a>
                </li>
            @endpermission
            @permission('stock.transfer.browse')
                {{-- Stock transfer --}}
                <li>
                    <a class="app-menu__item {{ activeMenu('transfer') }}" href="{{ route('transfer.index') }}">
                        <i class="app-menu__icon fa fa-exchange" aria-hidden="true"></i>
                        <span class="app-menu__label">{{ trans('app.stock_transfer') }}</span>
                    </a>
                </li>
            @endpermission
            @permission('stock.adjust.browse')
                {{-- Stock adjustment --}}
                <li>
                    <a class="app-menu__item {{ activeMenu('adjustment') }}" href="{{ route('adjustment.index') }}">
                        <i class="app-menu__icon fa fa-adjust" aria-hidden="true"></i>
                        <span class="app-menu__label">{{ trans('app.stock_adjustment') }}</span>
                    </a>
                </li>
            @endpermission
            
            @if(auth()->user()->can(['report.loan-approval', 'report.loan-expired', 'report.loan', 'report.financial', 'report.customer', 'report.payment', 'report.agent', 'report.commission-pay']))
            {{-- Reports --}}
            <li class="treeview {{ activeTreeviewMenu('report') }}">
                <a class="app-menu__item" href="#" data-toggle="treeview">
                    <i class="app-menu__icon fa fa-book"></i>
                    <span class="app-menu__label">{{ trans('app.report') }}</span>
                    <i class="treeview-indicator fa fa-angle-left"></i>
                </a>

                <ul class="treeview-menu">
                    @permission('report.loan-approval')
                        {{-- Loan disbursement --}}
                        <li>
                            <a class="treeview-item {{ activeMenu('disbursed-loan', 2) }}" href="{{ route('report.disbursed_loan') }}">
                                <i class="icon fa fa-list-alt pr-1"></i>{{ trans('app.loan_disbursement') }}
                            </a>
                        </li>
                    @endpermission
                    @permission('report.loan-expired')
                    {{-- Overdue loan --}}
                    <li>
                        <a class="treeview-item {{ activeMenu('overdue-loan', 2) }}" href="{{ route('report.overdue_loan') }}">
                            <i class="icon fa fa-clock-o pr-1"></i>{{ trans('app.overdue_loan') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('report.loan')
                        {{-- loan --}}
                        <li>
                            <a class="treeview-item {{ activeMenu('loan', 2) }}" href="{{ route('report.loan', ReportLoanStatus::PENDING) }}">
                                <i class="icon fa fa-money pr-1"></i>{{ trans('app.loan') }}
                            </a>
                        </li>
                    @endpermission
                    @permission('report.financial')
                        {{-- Financial statement --}}
                        <li>
                            <a class="treeview-item {{ activeMenu('financial-statement', 2) }}" href="{{ route('report.financial_statement') }}">
                                <i class="icon fa fa-credit-card-alt pr-1"></i>{{ trans('app.financial_statement') }}
                            </a>
                        </li>
                    @endpermission
                    @permission('report.customer')
                        {{-- Client list --}}
                        <li>
                            <a class="treeview-item {{ activeMenu(['client-registration', 'loan-portfolio'], 2) }}" href="{{ route('report.client_registration') }}">
                                <i class="icon fa fa-address-book pr-1"></i>{{ trans('app.client_registration') }}
                            </a>
                        </li>
                    @endpermission
                    @permission('report.payment')
                    {{-- Client payment --}}
                    <li>
                        <a class="treeview-item {{ activeMenu('client-payment', 2) }}" href="{{ route('report.client_payment') }}">
                            <i class="icon fa fa-credit-card pr-1"></i>{{ trans('app.payment') }}
                        </a>
                    </li>
                    @endpermission
                    @permission('report.agent')
                        {{-- Agent and commission --}}
                        <li>
                            <a class="treeview-item {{ activeMenu(['agent', 'agent-commission'], 2) }}" href="{{ route('report.agent') }}">
                                <i class="icon fa fa-address-book pr-1"></i>{{ trans('app.agent') }}
                            </a>
                        </li>
                    @endpermission
                    @permission('report.commission-pay')
                        {{-- Commission payment --}}
                        <li>
                            <a class="treeview-item {{ activeMenu('commission-payment', 2) }}" href="{{ route('report.commission_payment') }}">
                                <i class="icon fa fa-credit-card pr-1"></i>{{ trans('app.commission_payment') }}
                            </a>
                        </li>
                    @endpermission
                </ul>
            </li>
            @endif
            @permission('branch.browse')
                {{-- Branch --}}
                <li>
                    <a class="app-menu__item {{ activeMenu('branch') }}" href="{{ route('branch.index') }}">
                        <i class="app-menu__icon fa fa-code-fork fa-lg" aria-hidden="true"></i>
                        <span class="app-menu__label">{{ trans('app.branch') }}</span>
                    </a>
                </li>
            @endpermission
            @if(auth()->user()->can(['staff.browse', 'position.browse']))
                {{-- Staff and position --}}
                <li class="treeview {{ activeTreeviewMenu(['staff', 'position']) }}">
                    <a class="app-menu__item" href="#" data-toggle="treeview">
                        <i class="app-menu__icon fa fa-users"></i>
                        <span class="app-menu__label">{{ trans('app.staff') }}</span>
                        <i class="treeview-indicator fa fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        @permission('staff.browse')
                        <li>
                            <a class="treeview-item {{ activeMenu('staff') }}" href="{{ route('staff.index') }}">
                                <i class="icon fa fa-user pr-1"></i>{{ trans('app.staff') }}
                            </a>
                        </li>
                        @endpermission
                        @permission('position.browse')
                        <li>
                            <a class="treeview-item {{ activeMenu('position') }}" href="{{ route('position.index') }}">
                                <i class="icon fa fa-bandcamp pr-1"></i>{{ trans('app.position') }}
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
            @endif
            @if(auth()->user()->can(['user.browse', 'role.browse']))
                {{-- User and role --}}
                <li class="treeview {{ activeTreeviewMenu(['user', 'role']) }}">
                    <a class="app-menu__item" href="#" data-toggle="treeview">
                        <i class="app-menu__icon fa fa-users"></i>
                        <span class="app-menu__label">{{ trans('app.user') }}</span>
                        <i class="treeview-indicator fa fa-angle-left"></i>
                    </a>
                    <ul class="treeview-menu">
                        @permission('user.browse')
                        <li>
                            <a class="treeview-item {{ activeMenu('user') }}" href="{{ route('user.index') }}">
                                <i class="icon fa fa-user pr-1"></i>{{ trans('app.user') }}
                            </a>
                        </li>
                        @endpermission
                        @permission('role.browse')
                        <li>
                            <a class="treeview-item {{ activeMenu('role') }}" href="{{ route('role.index') }}">
                                <i class="icon fa fa-briefcase pr-1"></i>{{ trans('app.role') }}
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
            @endif
            @permission('app.setting')
                {{-- Settings --}}
                <li class="treeview {{ activeTreeviewMenu('setting') }}">
                    <a class="app-menu__item" href="#" data-toggle="treeview">
                        <i class="app-menu__icon fa fa-gears"></i>
                        <span class="app-menu__label">{{ trans('app.setting') }}</span>
                        <i class="treeview-indicator fa fa-angle-left"></i>
                    </a>

                    <ul class="treeview-menu">
                        {{-- General setting --}}
                        <li>
                            <a class="treeview-item {{ activeMenu('general', 2) }}" href="{{ route('general_setting.index') }}">
                                <i class="icon fa fa-gear pr-1"></i>{{ trans('app.general_setting') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endpermission
        </ul>
    </aside>

    @yield('content')

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('js/pace.min.js') }}"></script>
    <script src="{{ asset('js/jquery.listswap.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}"></script>
    <script src="{{ asset('js/modernizr.min.js') }}"></script>
    <script src="{{ asset('js/Chart.min.js') }}"></script>
    <script src="{{ asset('js/moment.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
    <script>
        var emptyOptionElm = '<option value="">{{ trans('app.select_option') }}</option>';
        var sweetAlertTitle = '{{ trans('app.confirmation') }}';
        var sweetAlertText = '{{ trans('message.confirm_perform_action') }}';

        $.ajaxSetup({
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            }
        });
    </script>
    @yield('js')
</body>
</html>
