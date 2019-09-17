<?php

use App\Constants\BranchType;
use App\Constants\DurationType;
use App\Constants\ExtendedProperty as Property;
use App\Constants\Frequency;
use App\Constants\Gender;
use App\Constants\LoanStatus;
use App\Constants\MaritalStatus;
use App\Constants\PaymentMethod;
use App\Constants\PaymentScheduleType;
use App\Constants\PurchaseStatus;
use App\Constants\RepayType;
use App\Constants\ReportLoanStatus;
use App\Constants\SaleStatus;
use App\Constants\StockTransferStatus;
use App\Constants\StockType;
use App\Constants\UserRole;
use App\Models\Branch;
use App\Models\ExtendedProperty;
use App\Models\Invoice;
use App\Models\Loan;
use App\Models\Staff;

if (!function_exists('activeMenu')) {
    /**
     * Check if a menu is active.
     *
     * @param string|array $values
     * @param int $index URI segment index
     *
     * @return string
     */
    function activeMenu($values, $index = 1)
    {
        if (!is_array($values)) {
            return (request()->segment($index) == $values ? 'active' : '');
        }

        foreach ($values as $value) {
            if (request()->segment($index) == $value) {
                return 'active';
            }
        }

        return '';
    }
}

if (!function_exists('activeTreeviewMenu')) {
    /**
     * Check if a treeview menu is active.
     *
     * @param string|array $values
     * @param int $index URI segment index
     *
     * @return string
     */
    function activeTreeviewMenu($values, $index = 1)
    {
        if (!is_array($values)) {
            return (request()->segment($index) == $values ? 'is-expanded' : '');
        }

        foreach ($values as $value) {
            if (request()->segment($index) == $value) {
                return 'is-expanded';
            }
        }

        return '';
    }
}

if (!function_exists('allBranches')) {
    /**
     * GEt all branches from database.
     *
     * @return mixed
     */
    function allBranches()
    {
        return Branch::orderBy('name')->get();
    }
}

if (!function_exists('branchTypes')) {
    /**
     * Get all branch types or title of a type.
     *
     * @param string $key
     *
     * @return array|string
     */
    function branchTypes($key = null)
    {
        $branchTypes = [
            BranchType::SHOP => trans('app.shop'),
            BranchType::WAREHOUSE => trans('app.warehouse'),
        ];

        if ($key === null) {
            return $branchTypes;
        }

        return $branchTypes[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('brands')) {
    /**
     * Get all brands or title of a brand from extended_property table.
     *
     * @param int $id
     *
     * @return array|string|null
     */
    function brands($id = null)
    {
        $brandQuery = ExtendedProperty::brand();
        if ($id === null) {
            return $brandQuery->orderBy('value')->get();
        }

        $brandTitle = $brandQuery->where('id', $id)->first()->value ?? trans('app.n/a');
        return $brandTitle;
    }
}

if (!function_exists('dateIsoFormat')) {
    /**
     * Convert and return date as ISO format 'yyyy-mm-dd'.
     * 
     * @param string $date Valid date value
     * @param string Format
     * 
     * @return string|null
     */
    function dateIsoFormat($date, $format = 'Y-m-d')
    {
        return ($date === null ? null : date($format, strtotime($date)));
    }
}

if (!function_exists('decimalNumber')) {
    /**
     * Return decimal format of a number.
     * 
     * @param float|int $value
     * @param bool $displayMode Display thousand separator if true
     * 
     * @return string
     */
    function decimalNumber($value, $displayMode = false)
    {
        return number_format($value, 2, '.', ($displayMode ? ',' : ''));
    }
}

if (!function_exists('displayDate')) {
    /**
     * Return date value for display in a specific format.
     * 
     * @param string $date Valid date value
     * @param bool $numericMonth
     * 
     * @return string
     */
    function displayDate($date, $numericMonth = true)
    {
        $format = ($numericMonth ? 'd-m-Y' : 'd-M-Y');
        return ($date === null ? null : date($format, strtotime($date)));
    }
}

if (!function_exists('durationTypes')) {
    /**
     * Get duration types or title of a duration type.
     *
     * @param string|null $key
     *
     * @return array|string
     */
    function durationTypes($key = null)
    {
        $durationTypes = [
            DurationType::YEARLY => trans('app.yearly'),
            DurationType::MONTHLY => trans('app.monthly'),
        ];

        if ($key === null) {
            return $durationTypes;
        }

        return $durationTypes[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('genders')) {
    /**
     * Get all genders or title of a gender.
     *
     * @param string $key
     *
     * @return array|string
     */
    function genders($key = null)
    {
        $genders = [
            Gender::MALE => trans('app.male'),
            Gender::FEMALE => trans('app.female'),
        ];

        if ($key === null) {
            return $genders;
        }

        return $genders[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Check if the login has role as admin.
     * FOR TEMPORARY USE.
     */
    function isAdmin()
    {
        return auth()->check() && auth()->user()->hasRole(env('ADMIN_ROLE'));
    }
}

if (!function_exists('isPaidLoan')) {
    /**
     * Check if a loan has payment data.
     *
     * @param int $loanId
     *
     * @return bool True if loan has payment
     */
    function isPaidLoan($loanId)
    {
        $payments = Invoice::where('loan_id', $loanId)->count();
        return ($payments > 0);
    }
}

if (!function_exists('khmerDate')) {
    /**
     * Return date value in Khmer for display.
     * 
     * @param string $date Valid date value
     * 
     * @return string
     */
    function khmerDate($date)
    {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return trans('app.n/a');
        }
        
        $khmerDate = date('d-', $timestamp) . khmerMonths(date('m', $timestamp)) . date('-Y', $timestamp);
        return $khmerDate;
    }
}

if (!function_exists('khmerDay')) {
    /**
     * Return day value in Khmer letter.
     *
     * @param string $date Valid date value
     *
     * @return string
     */
    function khmerDay($date)
    {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return trans('app.n/a');
        }

        $khmerDays = ['អាទិត្យ', 'ច័ន្ទ', 'អង្គារ', 'ពុធ', 'ព្រហស្បតិ៏', 'សុក្រ', 'សៅរ៍'];
        $khmerDay = $khmerDays[date('w', $timestamp)];
        return $khmerDay;
    }
}

if (!function_exists('khmerMonths')) {
    /**
     * Return all months or title of a month in Khmer.
     * 
     * @param int $key Value from 1 to 12
     * 
     * @return array|string
     */
    function khmerMonths($key = null)
    {
        $khmerMonths = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ជិកា', 'ធ្នូ'];

        if ($key === null) {
            return $khmerMonths;
        }

        return $khmerMonths[$key - 1] ?? trans('app.n/a');
    }
}

if (!function_exists('loanStatuses')) {
    /**
     * Get all loan statuses or title of a status.
     *
     * @param string $key
     *
     * @return array|string
     */
    function loanStatuses($key = null)
    {
        $loanStatuses = [
            LoanStatus::PENDING => trans('app.pending'),
            LoanStatus::ACTIVE => trans('app.progressing'),
            LoanStatus::PAID => trans('app.paid'),
            LoanStatus::REJECTED => trans('app.rejected'),
        ];

        if ($key === null) {
            return $loanStatuses;
        }

        return $loanStatuses[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('maritalStatuses')) {
    /**
     * Get all marital statuses or title of a status.
     * 
     * @param string $key
     * 
     * @return array|string
     */
    function maritalStatuses($key = null)
    {
        $maritalStatuses = [
            MaritalStatus::SINGLE => trans('app.single'),
            MaritalStatus::MARRIED => trans('app.married'),
        ];

        if ($key === null) {
            return $maritalStatuses;
        }

        return $maritalStatuses[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('nextLoanAccNum')) {
    /**
     * Generate loan account number that is auto-increment.
     *
     * @return string Format as "N-000001"
     */
    function nextLoanAccNum()
    {
        $lastAccNum = Loan::latest()->first()->account_number ?? 0;
        $accountNumber = 'N-' . str_pad(substr($lastAccNum, 2) + 1, 6, 0, STR_PAD_LEFT);
        return $accountNumber;
    }
}

if (!function_exists('oneMonthIncrement')) {
    /**
     * Increase one month to the given date but keep the same day.
     * If day of the new date is incorrect, change it to last day of the month.
     *
     * @param string $date
     * @param string|int $day Day to return for new date
     *
     * @return string Date with Y-m-d format
     */
    function oneMonthIncrement($date, $day = null)
    {
        if ($date === null || !validateDate($date)) {
            return $date;
        }

        $newMonth = date('Y-m', strtotime(dateIsoFormat($date, 'Y-m') . " +1 month"));
        $newDate = $newMonth . '-' . ($day === null ? dateIsoFormat($date, 'd') : $day);

        if (!validateDate($newDate)) {
            $lastDayOfMonth = dateIsoFormat($newMonth, 't');
            $newDate = $newMonth . '-' . $lastDayOfMonth;
        }

        return $newDate;
    }
}

if (!function_exists('offset')) {
    /**
     * Return starting number for displaying items on a page.
     * 
     * @param int $currentPage
     * 
     * @return int
     */
    function offset($currentPage)
    {
        $currentPage = $currentPage ?? 1;
        $offset = (paginationCount() * $currentPage) - (paginationCount() - 1);
        
        return $offset;
    }
}

if (!function_exists('paymentMethods')) {
    /**
     * Get all payment methods or title of a payment method.
     * 
     * @param int|string $key
     * 
     * @return array|string
     */
    function paymentMethods($key = null)
    {
        $paymentMethods = [
            PaymentMethod::DIRECT_PAYMENT => trans('app.direct_payment'),
            PaymentMethod::BANK => trans('app.bank'),
            PaymentMethod::WING => trans('app.wing'),
            PaymentMethod::TRUE_MONEY => trans('app.truemoney'),
            PaymentMethod::E_MONEY => trans('app.emoney'),
        ];

        if ($key === null) {
            return $paymentMethods;
        }

        return $paymentMethods[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('paymentScheduleTypes')) {
    /**
     * Get all payment schedule types or title of a type.
     *
     * @param string|null $type
     *
     * @return array|string
     */
    function paymentScheduleTypes($type = null)
    {
        $paymentScheduleTypes = [
            PaymentScheduleType::EQUAL_PAYMENT => trans('app.equal_payment'),
            PaymentScheduleType::FLAT_INTEREST => trans('app.flat_interest'),
            PaymentScheduleType::DECLINE_INTEREST => trans('app.decline_interest'),
        ];

        if ($type === null) {
            return $paymentScheduleTypes;
        }

        return $paymentScheduleTypes[$type] ?? trans('app.n/a');
    }
}

if (!function_exists('paginationCount')) {
    /**
     * Return number of items for display on a page.
     * 
     * @return int
     */
    function paginationCount()
    {
        return 15;
    }
}

if (!function_exists('pmt')) {
    /**
     * Calculate total payment amount of equal payment schedule.
     *
     * @param float|int $loanRate Calculated loan rate as percent
     * @param int $installment
     * @param float|int $loanAmount Total loan amount
     *
     * @return float Total payment amount
     */
    function pmt($loanRate, $installment, $loanAmount)
    {
        $powVal = pow((1 + $loanRate), $installment);
        $paymentAmount = $loanRate * -$loanAmount * $powVal / (1 - $powVal);
        return round($paymentAmount);
    }
}

if (!function_exists('positions')) {
    /**
     * Get all positions or title of a position from extended_property table.
     *
     * @param int $id
     *
     * @return array|string|null
     */
    function positions($id = null)
    {
        $positionQuery = ExtendedProperty::where('property_name', Property::POSITION);
        if ($id === null) {
            return $positionQuery->orderBy('value')->get();
        }

        $positionTitle = $positionQuery->where('id', $id)->first()->value ?? trans('app.n/a');
        return $positionTitle;
    }
}

if (!function_exists('purchaseStatuses')) {
    /**
     * Get all purchase statuses or title of a status.
     *
     * @param string|null $key
     *
     * @return array|string
     */
    function purchaseStatuses($key = null)
    {
        $purchaseStatuses = [
            PurchaseStatus::RECEIVED => trans('app.received'),
//            PurchaseStatus::ORDERED => trans('app.ordered'),
        ];

        if ($key === null) {
            return $purchaseStatuses;
        }

        return $purchaseStatuses[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('repayTypes')) {
    /**
     * Get all repayment types.
     * 
     * @return array
     */
    function repayTypes()
    {
        return [
            RepayType::REPAY,
            RepayType::PAYOFF,
            // RepayType::ADVANCE_PAY,
        ];
    }
}
    
if (!function_exists('reportLoanStatuses')) {
    /**
     * Return all report loan statuses or a title of a status.
     *
     * @param string $key
     *
     * @return array
     */
    function reportLoanStatuses($key = null)
    {
        $statuses = [
            ReportLoanStatus::PENDING => trans('app.pending'),
            ReportLoanStatus::ACTIVE => trans('app.progressing'),
            ReportLoanStatus::PAID => trans('app.paid'),
            ReportLoanStatus::REJECTED => trans('app.rejected'),
        ];

        if ($key === null) {
            return $statuses;
        }

        return $statuses[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('resourceRouteMethods')) {
    /**
     * Get only used methods for resource route.
     *
     * @param bool $showMethod Include show method if true
     *
     * @return array
     */
    function resourceRouteMethods($showMethod = true)
    {
        return ['index', 'create', 'edit', 'destroy', ($showMethod ? 'show' : null)];
    }
}

if (!function_exists('selectedOption')) {
    /**
     * Set 'selected' attribute to HTML select option depending on conditions.
     *
     * @param string|number $optionValue
     * @param string|number $oldValue
     * @param string|number|null $fieldValue
     *
     * @return string
     */
    function selectedOption($optionValue, $oldValue, $fieldValue = null)
    {
        return (isset($oldValue)
            ? ($oldValue == $optionValue ? 'selected' : '')
            : ($fieldValue == $optionValue ? 'selected': '')
        );
    }
}

if (!function_exists('setResourceRouteNames')) {
    /**
     * Set resource route names for index, create, edit, show, and destroy methods.
     *
     * @param string $name
     *
     * @param array
     */
    function setResourceRouteNames($name)
    {
        return [
            'index' => $name . '.index',
            'create' => $name . '.create',
            'edit' => $name . '.edit',
            'show' => $name . '.show',
            'destroy' => $name . '.destroy',
        ];
    }
}

if (!function_exists('saleStatuses')) {
    /**
     * Get all sale statuses or title of a status.
     *
     * @param string $key
     *
     * @return array|string
     */
    function saleStatuses($key = null)
    {
        $saleStatuses = [
            SaleStatus::DRAFT => trans('app.draft'),
            SaleStatus::PENDING => trans('app.pending'),
            //SaleStatus::ACTIVE => trans('app.progressing'),
            SaleStatus::DONE => trans('app.completed'),
            //SaleStatus::PAID => trans('app.paid'),
        ];

        if ($key === null) {
            return $saleStatuses;
        }

        return $saleStatuses[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('stockTransferStatuses')) {
    /**
     * Get all transfer statuses or title of a status.
     *
     * @param string|null $key
     *
     * @return array|string
     */
    function stockTransferStatuses($key = null)
    {
        $transferStatuses = [
            StockTransferStatus::COMPLETED => trans('app.completed'),
//            StockTransferStatus::SENT => trans('app.sent'),
        ];

        if ($key === null) {
            return $transferStatuses;
        }

        return $transferStatuses[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('stockTypes')) {
    /**
     * Get all stock types or title of a type.
     *
     * @param string|null $key
     *
     * @return array|string
     */
    function stockTypes($key = null)
    {
        $stockTypes = [
            StockType::STOCK_IN => trans('app.stock-in'),
            StockType::STOCK_OUT => trans('app.stock-out'),
        ];

        if ($key === null) {
            return $stockTypes;
        }

        return $stockTypes[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('userRoles')) {
    /**
     * Get all user roles or title of a role.
     *
     * @param int $key
     *
     * @return array|string
     */
    function userRoles($key = null)
    {
        $roles = [
            UserRole::STAFF => 'Staff',
//            UserRole::REPORTER => 'Reporter',
        ];

        if ($key === null) {
            return $roles;
        }

        return $roles[$key] ?? trans('app.n/a');
    }
}

if (!function_exists('validateDate')) {
    /**
     * Check if the given date is valid.
     * 
     * @param string $date Date value
     * @param string $format Date format to compare
     * 
     * @return bool
     */
    function validateDate($date, $format = 'Y-m-d')
    {
        return date($format, strtotime($date)) == $date;
    }
}
