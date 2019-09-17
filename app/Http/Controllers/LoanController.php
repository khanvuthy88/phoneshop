<?php

namespace App\Http\Controllers;

use App\Constants\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Constants\FormType;
use App\Constants\Frequency;
use App\Constants\LoanStatus;
use App\Constants\PaymentScheduleType;
use App\Constants\Message;
use App\Constants\StockTransaction;
use App\Constants\StockType;
use App\Http\Requests\LoanRequest;
use App\Models\AgentCommission;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\Staff;
use App\Models\ProductWarehouse;
use App\Models\StockHistory;

use DB;
use Auth;

class LoanController extends Controller
{
    public function __construct()
    {
        //$this->middleware('role:' . UserRole::ADMIN)->only('edit');
    }

    /**
     * Display a list of loans;
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->can('loan.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $agents = [];
        $loans = Loan::where('status', '!=', LoanStatus::REJECTED)
            ->with(['schedules' => function($query) {
                $query->where('paid_status', 0)->orderBy('payment_date');
            }]);

        if (isAdmin()) {
            if (!empty($request->branch)) {
                $loans = $loans->where('branch_id', $request->branch);
                $agents = Staff::where('branch_id', $request->branch)->orderBy('name')->get();
            }

            if (!empty($request->agent)) {
                $loans = $loans->where('staff_id', $request->agent);
            }
        } else {
            $staff = auth()->user()->staff;
            $loans = $loans->where('branch_id', $staff->branch->id)->where('staff_id', $staff->id);
        }

        if (!empty($request->search)) {
            $searchText = $request->search;

            $loans = $loans->where(function ($query) use ($searchText) {
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

        $itemCount = $loans->count();
        $loans = $loans->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);

        return view('loan/index', compact('agents', 'itemCount', 'loans', 'offset'));
    }
    
    /**
     * Display form to create loan.
     *
     * @param Loan $loan
     *
     * @return Response
     */
    public function create(Request $request, Loan $loan)
    {
        if(!Auth::user()->can('loan.add')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $loan->account_number = nextLoanAccNum();
        $title = trans('app.create');
        $formType = FormType::CREATE_TYPE;
        $branches = Branch::getAll();
        $clients = Client::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $agents = [];
        if (isAdmin() && old('branch') !== null) { // When form validation has error
            $agents = Staff::where('branch_id', old('branch'))->orderBy('name')->get();
        }

        return view('loan/form', compact(
            'agents',
            'branches',
            'clients',
            'formType',
            'loan',
            'products',
            'title'
        ));
    }

    /**
     * Display form to edit loan.
     *
     * @param Loan $loan
     *
     * @return Response
     */
    public function edit(Loan $loan)
    {
        if(!Auth::user()->can('loan.edit')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        if (isPaidLoan($loan->id)) {
            return back();
        }
        
        $title = trans('app.edit');
        $formType = FormType::EDIT_TYPE;
        $branches = Branch::getAll();
        $clients = Client::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $agents = [];

        if (isAdmin()) {
            $branchId = old('branch') ?? $loan->branch_id;
            $agents = Staff::where('branch_id', $branchId)->orderBy('name')->get();
        }

        return view('loan/form', compact(
            'agents',
            'branches',
            'clients',
            'formType',
            'loan',
            'products',
            'title'
        ));
    }
    
    /**
     * Display loan detail.
     *
     * @param Loan $loan
     *
     * @return Response
     */
    public function show(Loan $loan)
    {
        if(!Auth::user()->can('loan.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        if ($loan->status == LoanStatus::REJECTED) {
            return redirect(route('loan.index'));
        }
        
        $title = trans('app.detail');
        $formType = FormType::SHOW_TYPE;

        return view('loan/form', compact(
            'formType',
            'loan',
            'title'
        ));
    }
    
    /**
     * Save new or existing loan.
     *
     * @param LoanRequest $request
     * @param Loan $loan
     *
     * @return Response
     */
    public function save(LoanRequest $request, Loan $loan)
    {
        if(!Auth::user()->can('loan.edit') && !Auth::user()->can('loan.add')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $request->request->set('wing_code', str_replace(' ', '', $request->wing_code));
        $validationRules = [
            'form_type' => ['required', Rule::in([FormType::CREATE_TYPE, FormType::EDIT_TYPE])],
//            'wing_code' => ['required', 'numeric', 'digits:8', Rule::unique('loans')->ignore($loan->id)],
            'wing_code' => ['required', 'numeric', 'digits:8'],
//            'client_code' => ['required', Rule::unique('loans')->ignore($loan->id)],
            'client_code' => ['required'],
            'client' => 'required|integer',
            'product' => 'required|integer',
            'product_price' => 'nullable|numeric',
            'loan_amount' => 'required|numeric',
            'depreciation_amount' => 'required|numeric',
        ];

        if (isAdmin()) {
            $validationRules = array_merge($validationRules, [
                'branch' => 'required|integer',
                'agent' => 'required|integer',
            ]);
        }
        $this->validate($request, $validationRules);

        // If loan has payment data, not allow to update
        if ($loan->id !== null && isPaidLoan($loan->id)) {
            return back();
        }

        // If the client has an active or pending loan, not allow to create new loan
        $currentLoan = Loan::whereIn('status', [LoanStatus::PENDING, LoanStatus::ACTIVE])
            ->where('client_id', $request->client)->where('id', '!=', $loan->id)->first();
        if (!empty($currentLoan)) {
            session()->flash(Message::ERROR_KEY, trans('message.loan_disallowed_cos_of_client') .
                '<a href="' . route('loan.show', $currentLoan->id) . '">' . $currentLoan->account_number . '</a>');
            return back()->withInput($request->all());
        }

        $loan->client_id = $request->client;
        $loan->product_id = $request->product;
        $loan->product_price = $request->product_price;
        $loan->product_ime = $request->product_ime;

        if (isAdmin()) {
            $loan->branch_id = $request->branch;
            $loan->staff_id = $request->agent;
        } else { // Auto-set branch and agent when staff creates loan
            $staff = auth()->user()->staff;
            $loan->branch_id = $staff->branch->id;
            $loan->staff_id = $staff->id;
        }

        $loan->schedule_type = $request->schedule_type;
        $loan->loan_amount = $request->loan_amount;
        $loan->depreciation_amount = $request->depreciation_amount;
        $loan->down_payment_amount = $request->down_payment_amount;
        $loan->interest_rate = $request->interest_rate;
        $loan->installment = $request->installment;
        $loan->payment_per_month = $request->payment_per_month;
        $loan->loan_start_date = dateIsoFormat($request->loan_start_date);
        $loan->first_payment_date = dateIsoFormat($request->first_payment_date);
        $loan->note = $request->note;

        // Calculate commission amount for agent
        // $startDates = AgentCommission::select('start_date')->where('staff_id', $request->agent)->get();
        $agentCommissions = AgentCommission::where('staff_id', $request->agent)->orderBy('start_date', 'desc')->get();
        if (count($agentCommissions) > 0) {

            $commissionAmount = 0;
        } else {
            $commissionAmount = 0;
        }
        $loan->commission_amount = $commissionAmount;
        
        // Generate loan code and set creator when create new loan
        if ($request->form_type == FormType::CREATE_TYPE) {
            $loan->account_number = nextLoanAccNum();
            $loan->user_id = auth()->user()->id;
            $loan->status = LoanStatus::PENDING;
        }
        $loan->wing_code = $request->wing_code;
        $loan->client_code = $request->client_code;

        DB::beginTransaction();

        if ($loan->save()) {
            // Delete old schedules when edit loan
            if ($request->form_type == FormType::EDIT_TYPE) {
                Schedule::where('loan_id', $loan->id)->delete();
            }
            
            $paymentSchedules = $this->calcPaymentSchedule($request);
            foreach ($paymentSchedules as $paymentSchedule) {
                $schedule = new Schedule();
                $schedule->loan_id = $loan->id;
                $schedule->payment_date = $paymentSchedule['payment_date'];
                $schedule->principal = $paymentSchedule['principal'];
                $schedule->interest = $paymentSchedule['interest'];
                $schedule->total = $paymentSchedule['total'];
                $schedule->outstanding = $paymentSchedule['outstanding'];
                $schedule->save();
            }

            // Deduct stock level of selected product
            // Update or insert product quantity to warehouse
            $productWarehouse = (ProductWarehouse::selectQuery($request->branch, $loan->product_id)->first()
                                ?? new ProductWarehouse());
            $productWarehouse->product_id = $request->product;
            $productWarehouse->warehouse_id = $loan->branch_id;
            $productWarehouse->quantity = $productWarehouse->quantity - 1;
            $productWarehouse->save();

            // Save stock history
            $stockHistory = new StockHistory();
            $stockHistory->transaction = StockTransaction::LOAN;
            $stockHistory->transaction_id = $loan->id;
            $stockHistory->transaction_date = dateIsoFormat($loan->loan_start_date);
            $stockHistory->warehouse_id = $loan->branch_id;
            $stockHistory->product_id = $loan->product_id;
            $stockHistory->stock_type = StockType::STOCK_OUT;
            $stockHistory->quantity = 1;
            $stockHistory->save();
        }
        DB::commit();

        session()->flash(Message::SUCCESS_KEY, trans('message.item_saved_success'));
        return redirect(route('loan.show', $loan->id));
    }

    /**
     * Get loan payment schedule from AJAX request.
     *
     * @param LoanRequest $request
     *
     * @return Response
     */
    public function getPaymentSchedule(LoanRequest $request)
    {
        if (!$request->ajax()) {
            return back();
        }

        $paymentSchedules = $this->calcPaymentSchedule($request, true);
        return response()->json($paymentSchedules);
    }

    /**
     * Calculate loan payment schedule as flat or decline interest.
     * 
     * @param LoanRequest $request
     * @param bool $displayMode
     * 
     * @return array
     */
    private function calcPaymentSchedule(LoanRequest $request, $displayMode = false)
    {
        $loanStartDate = dateIsoFormat($request->loan_start_date);
        // If first payment date is empty, increase it one month from loan start date
        $firstPaymentDate = dateIsoFormat($request->first_payment_date) ?? oneMonthIncrement($loanStartDate);
        $paymentDay = dateIsoFormat(($request->first_payment_date ?? $loanStartDate), 'd');
        $paymentDate = $firstPaymentDate;

        $scheduleType = $request->schedule_type;
        $isEqualSchedule = ($scheduleType == PaymentScheduleType::EQUAL_PAYMENT);
        $isDeclineSchedule = ($scheduleType == PaymentScheduleType::DECLINE_INTEREST);
        $installment = $request->installment;
        $downPaymentAmount = $outstandingAmount = $request->down_payment_amount;
        $principal = ($downPaymentAmount / $installment);

        if ($isEqualSchedule) {
            if ($request->interest_rate > 0) {
                $loanRate = ($request->interest_rate / 12) / 100;
                $totalAmount = pmt($loanRate, $installment, $downPaymentAmount);
            } else {
                $interest = 0;
                $principal = $totalAmount = decimalNumber($principal);
            }
        } elseif ($isDeclineSchedule) {
            $interestRate = $request->interest_rate / 100;
            $interest = $downPaymentAmount * $interestRate;

            // Calculate first interest amount of payment schedule
            $firstPayDuration = date_diff(date_create($loanStartDate), date_create($firstPaymentDate))->format('%a');
            $firstInterest = ($interest / 30) * $firstPayDuration;
        }

        $loopCount = ($scheduleType == PaymentScheduleType::FLAT_INTEREST ? ($installment + 1) : $installment); // For flat interest, plus one installment
        $scheduleData = [];

        for ($i = 1; $i <= $loopCount; $i++) {
            $isFirstLoop = ($i == 1);
            $isForeLastLoop = ($i == ($loopCount - 1));
            $paymentDate = ($isFirstLoop ? $paymentDate : oneMonthIncrement($paymentDate, $paymentDay));

            if ($isEqualSchedule) {
                if ($request->interest_rate > 0) {
                    $interest = round($loanRate * $outstandingAmount);
                    $principal = ($totalAmount - $interest);
                }
                $outstandingAmount = ($i == $loopCount ? 0 : ($outstandingAmount - $principal));
            } elseif ($isDeclineSchedule) {
                $interest = ($isFirstLoop ? $firstInterest : ($outstandingAmount * $interestRate));
                $totalAmount = ($principal + ($isFirstLoop ? $firstInterest : $interest));
                $outstandingAmount = ($isForeLastLoop ? $principal : ($outstandingAmount - $principal));
            } else {
                $interest = 0;
                $totalAmount = $principal;
                $outstandingAmount = ($isForeLastLoop ? 0 : ($outstandingAmount - $principal));
            }

            $scheduleData[] = [
                'payment_date' => ($displayMode ? displayDate($paymentDate) : $paymentDate),
                'principal' => ($isEqualSchedule ? $principal : decimalNumber($principal, $displayMode)),
                'interest' => ($isEqualSchedule ? $interest : decimalNumber($interest, $displayMode)),
                'total' => ($isEqualSchedule ? $totalAmount : decimalNumber($totalAmount, $displayMode)),
                'outstanding' => decimalNumber($outstandingAmount, $displayMode),
            ];
        }

        return $scheduleData;
    }

    /**
     * Change loan status from AJAX request.
     * 
     * @param Loan $loan
     * @param string $status Loan status to be changed to
     * 
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, Loan $loan, $status)
    {
        if(!Auth::user()->can('loan.edit')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        if (!$request->ajax() || !in_array($status, [LoanStatus::ACTIVE, LoanStatus::REJECTED, LoanStatus::PENDING])) {
            abort(404);
        }

        $loan->status = $status;
        $loan->changed_by = auth()->user()->id;

        if ($status == LoanStatus::ACTIVE) {
            $loan->approved_date = date('Y-m-d');
        }
        $loan->save();

        if ($status == LoanStatus::ACTIVE) {
            $message = trans('message.loan_approved');
        } elseif ($status == LoanStatus::REJECTED) {
            $message = trans('message.loan_rejected');
        } else {
            $message = trans('message.loan_reverted');
        }

        session()->flash(Message::SUCCESS_KEY, $message);
    }

    /**
     * Disburse product.
     * 
     * @param Loan $loan
     *
     * @return Response
     */
    public function disburse(Loan $loan)
    {
        if(!Auth::user()->can('loan.print')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        return view('partial/invoice', compact('loan'));
    }

    /**
     * Display loan contract for printing.
     *
     * @param Loan $loan
     *
     * @return Response
     */
    public function printContract(Loan $loan)
    {
        if(!Auth::user()->can('loan.print')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        return view('loan/contract', compact('loan'));
    }

    /**
     * Display payment schedule for printing.
     * 
     * @param Loan $loan
     *
     * @return Response
     */
    public function printPaymentSchedule(Loan $loan)
    {
        if(!Auth::user()->can('loan.print')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        return view('partial/payment-schedule', compact('loan'));
    }

    /**
     * Update note of a loan.
     * 
     * @param Request $request
     * @param Loan $loan
     * 
     * @return Response
     */
    public function updateNote(Request $request, Loan $loan)
    {
        $loan->note = $request->note;
        $loan->save();
        
        session()->flash(Message::SUCCESS_KEY, trans('message.item_saved_success'));
        return back()->withInput($request->all());
    }

    /**
     * Delete loan.
     *
     * @param Request $request
     * @param Loan $loan
     *
     * @return void
     */
    public function destroy(Request $request, Loan $loan)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $loan->delete();
        session()->flash(Message::SUCCESS_KEY, trans('message.item_deleted_success'));
    }
}
