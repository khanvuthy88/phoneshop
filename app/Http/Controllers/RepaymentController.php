<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Constants\PaymentScheduleType;
use App\Constants\LoanStatus;
use App\Constants\Message;
use App\Constants\RepayType;
use App\Constants\UserRole;
use App\Models\Schedule;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Staff;
use Auth;

class RepaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:'. UserRole::ADMIN)->except('index');
    }

    /**
     * Display a listing of active loans.
     *
     * @param Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->can('loan.pay')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $agents = [];
        $loans = Loan::with(['schedules' => function ($query) {
                $query->where('paid_status', 0)->orderBy('payment_date');
            }])->where('status', LoanStatus::ACTIVE);

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
        $loans = $loans->sortable()->orderBy('client_code', 'desc')->paginate(paginationCount());
        $offset = offset($request->page);
        
        return view('payment/index', compact('agents', 'itemCount', 'loans', 'offset'));
    }

    /**
     * Display form to repay or pay off loan.
     *
     * @param int $loanId
     * @param int $repayType Repayment or payoff
     * 
     * @return Response
     */
    public function show($loanId, $repayType)
    {
        if(!Auth::user()->can('loan.pay')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $loan = Loan::where('status', LoanStatus::ACTIVE)->find($loanId);
        if (empty($loan) || !in_array($repayType, repayTypes())) {
            return redirect(route('repayment.index'));
        }

        $penaltyAmount = $this->calcPenaltyAmount($loan->id);
        $payoffAmount = null;

        if ($repayType == RepayType::PAYOFF) {
            $title = trans('app.payoff');
            $repayLabel = trans('app.pay_off');
            $payoffAmount = $this->calcPayoffAmount($loan->id);
        } elseif ($repayType == RepayType::ADVANCE_PAY) {
            $title = trans('app.advance_payment');
            $repayLabel = trans('app.advance_pay');
        } else {
            $title = trans('app.payment');
            $repayLabel = trans('app.repay');
        }
        
        return view('payment/form', compact(
            'loan',
            'payoffAmount',
            'penaltyAmount',
            'repayLabel',
            'repayType',
            'title'
        ));
    }

    /**
     * Save loan payment or payoff.
     *
     * @param Request $request
     * @param int $loanId
     * 
     * @return Response
     */
    public function save(Request $request, $loanId)
    {
        if(!Auth::user()->can('loan.pay')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        
        $this->validate($request, [
            'repay_type' => Rule::in([RepayType::REPAY, RepayType::PAYOFF]),
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|gt:0',
            'payment_method' => 'required',
            'penalty_amount' => 'nullable|numeric',
        ]);
        
        if ($request->repay_type == RepayType::ADVANCE_PAY) {
            $this->validate($request, [
                'selected_schedules' => 'required',
            ]);
        }
        
        // Loan with unpaid schedule (s)
        $loan = Loan::with(['schedules' => function($query) use ($request) {
                    $query->where('paid_status', 0);

                    if ($request->repay_type == RepayType::ADVANCE_PAY) {
                        $query->whereIn('id', $request->selected_schedules);
                    }
                    $query->orderBy('payment_date');
                }])
            ->whereHas('schedules')
            ->where('status', LoanStatus::ACTIVE)
            ->find($loanId);

        if (empty($loan->schedules)) {
            return redirect(route('repayment.index'));
        }

        $repayType = $request->repay_type;
        $paymentAmount = $originalPaymentAmount = decimalNumber($request->payment_amount);
        $penaltyAmount = decimalNumber($request->penalty_amount);
        $paymentDate = dateIsoFormat($request->payment_date);
        
        // Payoff, advance payment, or simple payment for 3 schedule types: decline, flat, or equal payment
        if ($repayType == RepayType::PAYOFF) {
            $payoffAmount = $this->calcPayoffAmount($loan->id);
            
            // Compare remaining and inputted amounts
            if ($paymentAmount != $payoffAmount) {
                session()->flash(Message::ERROR_KEY, trans('message.incorrect_payoff_amount'));
                return back()->withInput($request->all());
            }

            $i = 0;
            foreach ($loan->schedules as $schedule) {
                if ($i == 0) {
                    $schedule->paid_interest = $schedule->interest;
                    $schedule->paid_total = $schedule->total;
                } else {
                    $schedule->paid_interest = 0;
                    $schedule->paid_total = $schedule->principal;
                }

                $schedule->paid_principal = $schedule->principal;
                $schedule->paid_date = $paymentDate;
                $schedule->paid_status = 1;
                $schedule->save();
                $i++;
            }

            $loan->status = LoanStatus::PAID;
            $loan->save();
        } elseif ($repayType == RepayType::ADVANCE_PAY) {
            $amountToPay = $loan->schedules->sum('principal');
            if (decimalNumber($paymentAmount) != decimalNumber($amountToPay)) {
                session()->flash(Message::ERROR_KEY, trans('message.selected_schedule_amount_unequal'));
                return back()->withInput($request->all());
            }
            
            foreach ($loan->schedules as $schedule) {
                $schedule->paid_principal = $schedule->principal;
                $schedule->paid_total = $schedule->principal;
                $schedule->paid_date = $paymentDate;
                $schedule->paid_status = 1;
                $schedule->save();
            }

            // Code to change loan status to paid if all schedules are paid
            // This payment type isn't used in this system at this time

        } elseif ($repayType == RepayType::REPAY) {
            $remainingAmount = ($loan->schedules->sum('total') - $loan->schedules->sum('paid_total'));
            if ($paymentAmount > $remainingAmount) {
                session()->flash(Message::ERROR_KEY, trans('message.payment_amount_lt_or_et_remaining_amount', [
                    'amount' => decimalNumber($remainingAmount),
                ]));
                return back()->withInput($request->all());
            }

            foreach ($loan->schedules as $schedule) {
                // Pay interest
                if ($loan->schedule_type != PaymentScheduleType::FLAT_INTEREST &&
                    $paymentAmount > 0 && $schedule->paid_interest < $schedule->interest
                ) {
                    $paidInterestAmount = $schedule->paid_interest;
                    $newInterestAmount = ($paidInterestAmount + $paymentAmount);

                    if ($newInterestAmount >= $schedule->interest) {
                        $schedule->paid_interest = $schedule->interest;
                        $paymentAmount -= ($schedule->interest - $paidInterestAmount);
                    } else {
                        $schedule->paid_interest = decimalNumber($newInterestAmount);
                        $paymentAmount = 0;
                    }
                }

                // Pay principal
                if ($paymentAmount > 0 && $schedule->paid_principal < $schedule->principal) {
                    $paidPrincipalAmount = $schedule->paid_principal;
                    $newPrincipalAmount = $paidPrincipalAmount + $paymentAmount;
                    
                    if ($newPrincipalAmount >= $schedule->principal) {
                        $schedule->paid_principal = $schedule->principal;
                        $paymentAmount -= ($schedule->principal - $paidPrincipalAmount);
                    } else {
                        $schedule->paid_principal = decimalNumber($newPrincipalAmount);
                        $paymentAmount = 0;
                    }
                }

                $schedule->paid_total = ($schedule->paid_principal + $schedule->paid_interest);
                if ($schedule->paid_total == $schedule->total) {
                    $schedule->paid_status = 1;
                }
                
                $schedule->paid_date = $paymentDate;
                $schedule->save();

                if ($paymentAmount <= 0) {
                    break;
                }
            }

            // Change loan status to paid if all schedules are paid
            $unpaidSchedules = Schedule::where('paid_status', 0)->where('loan_id', $loan->id)->get();
            if (count($unpaidSchedules) == 0) {
                $loan->status = LoanStatus::PAID;
                $loan->save();
            }
        }
        
        // Insert payment info into invoices table
        $invoice = new Invoice();
        $invoice->user_id = auth()->user()->id;
        $invoice->loan_id = $loan->id;
        $invoice->client_id = $loan->client->id;
        $invoice->payment_amount = $originalPaymentAmount;
        $invoice->penalty = $penaltyAmount;
        $invoice->total = ($originalPaymentAmount + $penaltyAmount);
        $invoice->payment_method = $request->payment_method;
        $invoice->reference_number = $request->reference_number;
        $invoice->payment_date = $paymentDate;
        $invoice->note = $request->note;
        
        $lastInvoiceNum = Invoice::latest()->first()->invoice_number ?? 0;
        $invoice->invoice_number = 'INV-' . str_pad(substr($lastInvoiceNum, 4) + 1, 6, 0, STR_PAD_LEFT);
        $invoice->save();
        
        session()->flash(Message::SUCCESS_KEY, trans('message.repayment_success'));
        $redirectRoute = ($repayType == RepayType::PAYOFF ? route('repayment.index') :
                        route('repayment.show', [$loan->id, $request->repay_type]));
        return redirect($redirectRoute);
    }

    /**
     * Calculate payoff amount of a loan.
     *
     * @param int $loanId
     *
     * @return float|null
     */
    private function calcPayoffAmount($loanId)
    {
        $nextUnpaidSchedule = Schedule::where('loan_id', $loanId)
            ->where('paid_status', 0)->orderBy('payment_date')->first();
        if (empty($nextUnpaidSchedule)) {
            return null;
        }

        $lastPaidSchedule = Schedule::where('id', ($nextUnpaidSchedule->id - 1))
            ->where('loan_id', $loanId)->where('paid_status', 1)->orderBy('payment_date')->first();
        $balanceAmount = $lastPaidSchedule->outstanding ?? $nextUnpaidSchedule->outstanding;

        if (empty($lastPaidSchedule)) {
            $dueAmount = ($nextUnpaidSchedule->total - $nextUnpaidSchedule->paid_total);
        } else {
            $dueAmount = ($nextUnpaidSchedule->interest - $nextUnpaidSchedule->paid_total);
        }

        $payoffAmount = decimalNumber($balanceAmount + $dueAmount);
        return $payoffAmount;
    }

    /**
     * Calculate penalty amount for overdue loan.
     * For temporary use:
     *      4 to 15 days => 10% of total remaining amount
     *      16 days up => 20% of total remaining amount
     * It should be set in configuration module.
     *
     * @param int $loanId
     *
     * @reutrn float|null
     */
    private function calcPenaltyAmount($loanId)
    {
        return 0;
    }
}
