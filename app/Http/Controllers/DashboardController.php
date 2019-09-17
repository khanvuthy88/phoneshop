<?php

namespace App\Http\Controllers;

use App\Constants\LoanStatus;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Auth;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $view_data = false;
        if(Auth::user()->can('dashboard')){
            $view_data = true;

            $clientCount = Client::count();
            $activeLoanCount = Loan::where('status', '!=', LoanStatus::REJECTED)->count();
            $rejectedLoanCount = Loan::where('status', LoanStatus::REJECTED)->count();
            $overdueLoanCount = Loan::where('status', LoanStatus::ACTIVE)
                ->whereHas('schedules', function ($query) {
                    $query->where('paid_status', 0)->whereRaw('payment_date < CURDATE()');
                })->count();

            // Calculate monthly principals and interests of current year for graph chart
            $schedulesPerYear = Schedule::whereHas('loan', function ($query) {
                $query->whereIn('status', [LoanStatus::ACTIVE, LoanStatus::PAID]);
            })->whereRaw(date('Y') . ' = YEAR(paid_date)')->get();

            $paidInterests = $paidPrincipals = array_map(function () { return 0; }, range(1, 12));
            foreach ($schedulesPerYear as $schedule) {
                $monthArrIndex = (substr($schedule->paid_date, 5, 2) - 1);
                $paidInterests[$monthArrIndex] += $schedule->paid_interest;
                $paidPrincipals[$monthArrIndex] += $schedule->paid_principal;
            }

            $totalPaidAmount = (array_sum($paidPrincipals) + array_sum($paidInterests));
            $paidPrincipals = array_map(function ($value) { return decimalNumber($value); }, $paidPrincipals);
            $paidInterests = array_map(function ($value) { return decimalNumber($value); }, $paidInterests);

            return view('dashboard', compact(
                'activeLoanCount',
                'clientCount',
                'overdueLoanCount',
                'paidInterests',
                'paidPrincipals',
                'rejectedLoanCount',
                'totalPaidAmount',
                'view_data'
            ));
        }
        
        return view('dashboard', compact('view_data'));
    }
}
