<?php

namespace App\Http\Controllers;

use App\Constants\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use App\Constants\DurationType;
use App\Constants\LoanStatus;
use App\Constants\ReportLoanStatus;
use App\Models\AgentCommission;
use App\Models\Branch;
use App\Models\CommissionPayment;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Invoice;
use App\Models\Staff;
use App\Models\Schedule;
use App\Traits\AgentUtil;
use Auth;

class ReportController extends Controller
{
    use AgentUtil;
    protected $commissionPayment, $invoice;

    public function __construct(CommissionPayment $commissionPayment, Invoice $invoice)
    {
        //$this->middleware('role:'. UserRole::ADMIN)->except('overdueLoan', 'clientPayment');

        $this->commissionPayment = $commissionPayment;
        $this->invoice = $invoice;
    }

    /**
     * Display a listing of disbursed loans.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function disbursedLoan(Request $request)
    {
        if(!Auth::user()->can('report.loan-approval')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $branchTitle = Branch::find($request->branch)->location ?? trans('app.all_branches');
        $agentName = Staff::find($request->agent)->name ?? trans('app.all_agents');
        $startDate = dateIsoFormat($request->start_date) ?? date('Y-m-d');
        $endDate = dateIsoFormat($request->end_date) ?? date('Y-m-d');
        $disbursedLoans = Loan::whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PAID])
            ->whereBetween('approved_date', [$startDate, $endDate]);
        $agents = [];

        if ($request->branch !== null) {
            $disbursedLoans = $disbursedLoans->where('branch_id', $request->branch);
            $agents = Staff::where('branch_id', $request->branch)->orderBy('name')->get();
        }

        if ($request->agent !== null) {
            $disbursedLoans = $disbursedLoans->where('staff_id', $request->agent);
        }

        $totalLoanAmount = $disbursedLoans->sum('loan_amount');
        $totalDepreciation = $disbursedLoans->sum('depreciation_amount');
        $totalDownPayment = $disbursedLoans->sum('down_payment_amount');
        $itemCount = $disbursedLoans->count();
        $disbursedLoans = $disbursedLoans->sortable()->orderBy('client_code', 'desc')->paginate(paginationCount());
        $offset = offset($request->page);
        $branches = Branch::getAll();

        return view('report.disbursed-loan', compact(
            'agentName',
            'agents',
            'branches',
            'disbursedLoans',
            'branchTitle',
            'endDate',
            'itemCount',
            'loanCount',
            'offset',
            'startDate',
            'totalDepreciation',
            'totalDownPayment',
            'totalLoanAmount'
        ));
    }

    /**
     * Display a listing of overdue loans.
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function overdueLoan(Request $request)
    {
        if(!Auth::user()->can('report.loan-expired')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $agents = [];
        $overdueLoans = Loan::where('status', LoanStatus::ACTIVE)
            ->with(['schedules' => function ($query) {
                $query->where('paid_status', 0)->orderBy('payment_date');
            }])
            ->whereHas('schedules', function ($query) {
                $query->where('paid_status', 0)->whereRaw('payment_date < CURDATE()');
            });

        if (isAdmin()) {
            if (!empty($request->branch)) {
                $overdueLoans = $overdueLoans->where('branch_id', $request->branch);
                $agents = Staff::where('branch_id', $request->branch)->orderBy('name')->get();
            }

            if (!empty($request->agent)) {
                $overdueLoans = $overdueLoans->where('staff_id', $request->agent);
            }
        } else {
            $staff = auth()->user()->staff;
            $overdueLoans = $overdueLoans->where('branch_id', $staff->branch->id)->where('staff_id', $staff->id);
        }

        if (!empty($request->search)) {
            $searchText = $request->search;

            $overdueLoans = $overdueLoans->where(function ($query) use ($searchText) {
                $query->where('account_number', 'like', '%' . $searchText . '%')
                    ->orWhere('wing_code', 'like', '%' . $searchText . '%')
                    ->orWhere('client_code', 'like', '%' . $searchText . '%')

                    // Query client
                    ->orWhereHas('client', function ($query) use ($searchText) {
                        $query->where('name', 'like', '%' . $searchText . '%')
                            ->orWhere('id_card_number', 'like', '%' . $searchText . '%')
                            ->orWhere('first_phone', 'like', '%' . $searchText . '%')
                            ->orWhere('second_phone', 'like', '%' . $searchText . '%')
                            ->orWhere('sponsor_name', 'like', '%' . $searchText . '%')
                            ->orWhere('sponsor_phone', 'like', '%' . $searchText . '%');
                    })

                    // Query product
                    ->orWhereHas('product', function ($query) use ($searchText) {
                        $query->where('name', 'like', '%' . $searchText . '%');
                    });
            });
        }

        $itemCount = $overdueLoans->count();
        $overdueLoans = $overdueLoans->sortable()->paginate(paginationCount());
        $offset = offset($request->page);
        
        return view('report/overdue-loan', compact('agents', 'itemCount', 'offset', 'overdueLoans'));
    }

    /**
     * Display a listing of loans with a specific type: Pending, Active, Paid, and others.
     *
     * @param Request $request
     * @param string $status
     *
     * @return Response
     */
    public function loan(Request $request, $status)
    {
        if(!Auth::user()->can('report.loan')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        if (!in_array($status, array_keys(reportLoanStatuses()))) {
            abort(404);
        }

        $filteredLoans = Loan::where('status', $status);
        $itemCount = $filteredLoans->count();
        $filteredLoans = $filteredLoans->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);

        $pendingLoanCount = Loan::where('status', ReportLoanStatus::PENDING)->count();
        $activeLoanCount = Loan::where('status', ReportLoanStatus::ACTIVE)->count();
        $paidLoanCount = Loan::where('status', ReportLoanStatus::PAID)->count();
        $rejectedLoanCount = Loan::where('status', ReportLoanStatus::REJECTED)->count();

        return view('report/loan', compact(
            'activeLoanCount',
            'itemCount',
            'filteredLoans',
            'offset',
            'paidLoanCount',
            'pendingLoanCount',
            'rejectedLoanCount',
            'status'
        ));
    }

    /**
     * Display monthly or yearly financial statements
     *
     * @param Request $request
     *
     * @return Response
     */
    public function financialStatement(Request $request)
    {
        if(!Auth::user()->can('report.financial')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $reportType = $request->report_type ?? DurationType::YEARLY;
        if (!in_array($reportType, [DurationType::YEARLY, DurationType::MONTHLY])) {
            return back();
        }

        // Calculate loan summary info
        $activeLoans = Loan::whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PAID])->get();
        $activeSchedules = Schedule::whereHas('loan', function ($query) {
            $query->whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PAID]);
        })->get();
        $totalLoanAmount = $activeLoans->sum('loan_amount');
        $totalDepreciation = $activeLoans->sum('depreciation_amount');
        $totalDownPayment = $activeLoans->sum('down_payment_amount');
        $totalInterest = $activeSchedules->sum('interest');
        $totalPaidInterest = $activeSchedules->sum('paid_interest');
        $totalPaidPrincipal = $activeSchedules->sum('paid_principal');

        // Calculate detail info for a month or year
        $isYearlyReport = ($reportType == DurationType::YEARLY);
        $filteredYear = $request->year ?? date('Y');
        $branchTitle = Branch::find($request->branch)->location ?? trans('app.all_branches');
        $filteredSchedules = Schedule::whereHas('loan', function ($query) use ($request) {
            $query->whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PAID]);
            if ($request->branch !== null) {
                $query->where('branch_id', $request->branch);
            }
        })->whereRaw("$filteredYear = YEAR(paid_date)");

        if ($isYearlyReport) {
            $filteredSchedules = $filteredSchedules->get();
            $dataRange = 12;
        } else { // Monthly report
            $filteredSchedules = $filteredSchedules->whereRaw("$request->month = MONTH(paid_date)")->get();
            $dataRange = dateIsoFormat($filteredYear . '-' . $request->month, 't');
        }

        $filteredData = array_map(function () {
                return ['paid_interest' => 0, 'paid_principal' => 0, 'paid_total' => 0];
            }, range(1, $dataRange));
        foreach ($filteredSchedules as $schedule) {
            $dataIndex = (substr($schedule->paid_date, ($isYearlyReport ? 5 : 8), 2) - 1);
            $filteredData[$dataIndex]['paid_interest'] += $schedule->paid_interest;
            $filteredData[$dataIndex]['paid_principal'] += $schedule->paid_principal;
            $filteredData[$dataIndex]['paid_total'] += ($schedule->paid_principal + $schedule->paid_interest);
        }

        $branches = Branch::getAll();
        return view('report/financial-statement', compact(
            'branches',
            'branchTitle',
            'filteredYear',
            'filteredData',
            'reportType',
            'totalDepreciation',
            'totalDownPayment',
            'totalInterest',
            'totalLoanAmount',
            'totalPaidInterest',
            'totalPaidPrincipal'
        ));
    }

    /**
     * Display a listing of client payments.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function clientPayment(Request $request)
    {
        if(!Auth::user()->can('report.payment')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $payments = $this->invoice;
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $payments = $payments->whereBetween('payment_date', [dateIsoFormat($request->start_date), dateIsoFormat($request->end_date)]);
        }

        $itemCount = $payments->count();
        $payments = $payments->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);

        return view('report/client-payment', compact('itemCount', 'offset', 'payments'));
    }

    /**
     * Display receipt of client payment for printing.
     *
     * @param Invoice $invoice
     *
     * @return Response
     */
    public function clientPaymentReceipt(Invoice $invoice)
    {
        return view('partial/receipt', compact('invoice'));
    }

    /**
     * Display client registration report.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function clientRegistration(Request $request)
    {
        if(!Auth::user()->can('report.customer')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $clients = Client::whereHas('loans', function ($query) {
            $query->whereNotIn('status', [LoanStatus::PENDING, LoanStatus::REJECTED]);
        });

        $itemCount = $clients->count();
        $clients = $clients->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);
        $agents = Staff::getAll();

        return view('report/client-registration', compact('agents', 'clients', 'itemCount', 'offset'));
    }

    /**
     * Display loan portfolio report.
     *
     * @param Request $request
     * @param Client $client
     *
     * @return Response
     */
    public function loanPortfolio(Request $request, Client $client)
    {

        $loans = $client->loans()->whereNotIn('status', [LoanStatus::PENDING, LoanStatus::REJECTED])
                ->orderBy('id', 'desc')->get();

        return view('report/loan-portfolio', compact('client', 'loans'));
    }

    /**
     * Display a listing of commission payments.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function commissionPayment(Request $request)
    {
        if(!Auth::user()->can('report.commission-pay')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $commissionPayments = $this->commissionPayment;
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $commissionPayments = $commissionPayments->whereBetween('paid_date', [dateIsoFormat($request->start_date), dateIsoFormat($request->end_date)]);
        }

        $itemCount = $commissionPayments->count();
        $commissionPayments = $commissionPayments->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);

        return view('report/commission-payment', compact('commissionPayments', 'itemCount', 'offset'));
    }

    /**
     * Display a listing of agents.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function agent(Request $request)
    {
        if(!Auth::user()->can('report.agent')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $totalCommission = $this->getAgentCommission(); // Commission amount of all agents
        $paidCommission = CommissionPayment::sum('amount');
        $agents = Staff::with(['loans' => function ($query) {
                $query->where('status', '!=', LoanStatus::REJECTED);
            }]);
        $itemCount = $agents->count();
        $agents = $agents->sortable()->orderBy('name')->paginate(paginationCount());
        $offset = offset($request->page);

        return view('report/agent', compact(
            'agents',
            'itemCount',
            'offset',
            'paidCommission',
            'totalCommission'
        ));
    }

    /**
     * Display agent detail and related info.
     *
     * @param Request $request
     * @param Staff $agent
     *
     * @return Response
     */
    public function agentDetail(Request $request, Staff $agent)
    {
        if(!Auth::user()->can('report.agent')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $loans = Loan::where('staff_id', $agent->id)->where('status', '!=', LoanStatus::REJECTED);
        $itemCount = $loans->count();
        $loans = $loans->paginate(paginationCount());
        $offset = offset($request->page);

        return view('report/agent-detail', compact('agent', 'itemCount', 'loans', 'offset'));
    }

    /**
     * Display each agent's financial statement.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function agentCommission(Request $request)
    {
        if(!Auth::user()->can('report.agent')){
            return back()->with(['message'=>trans('message.no_permission')], 403);
        }

        $agents = Staff::sortable()->orderBy('name')->paginate(paginationCount());

        // Get each agent's commissions
        foreach ($agents as $agent) {
            $agent->total_commission = $this->getAgentCommission($agent->id);
            $agent->paid_commission = CommissionPayment::where('staff_id', $agent->id)->sum('amount');
        }

        $itemCount = $agents->count();
        $offset = offset($request->page);

        return view('report/agent-commission', compact('agents', 'itemCount', 'offset'));
    }
}
