<?php

namespace App\Models;

use App\Constants\LoanStatus;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class Invoice extends Model
{
    use Sortable;

    public $sortable = [
        'invoice_number',
        'payment_amount',
        'penalty',
        'total',
        'payment_method',
        'reference_number',
        'payment_date',
        'note',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
    
    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
