<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Loan extends Model
{
    use Sortable;

    public $sortable = [
        'product_price',
        'product_ime',
        'account_number',
        'account_number_append',
        'loan_amount',
        'commission_amount',
        'depreciation_amount',
        'down_payment_amount',
        'extra_fee',
        'interest_rate',
        'installment',
        'frequency',
        'loan_start_date',
        'payment_per_month',
        'first_payment_date',
        'second_payment_date',
        'wing_code',
        'client_code',
        'note',
        'approved_date',
        'disbursed_date',
        'status',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
    
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'loan_id');
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'loan_id');
    }
    
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get user who approved or rejected loan.
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
