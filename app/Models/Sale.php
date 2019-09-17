<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Constants\SaleType;

class Sale extends Model
{
    use Sortable;

    public $sortable = [
        'sale_date',
        'sale_code',
        'reference_no',
        'total_quantity',
        'total_price',
        'discount_type',
        'total_discount',
        'shipping_amount',
        'shipping_note',
        'total_tax',
        'grand_total',
        'paid_amount',
        'total_commission',
        'payment_installment',
        'payment_start_date',
        'payment_end_date',
        'payment_period',
        'note',
        'staff_note',
        'type',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'sale_id');
    }

    public static function posType()
    {
        return self::where('type', SaleType::POS);
    }

    public static function saleType()
    {
        return self::where('type', SaleType::SALE);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Branch::class, 'warehouse_id');
    }
}
