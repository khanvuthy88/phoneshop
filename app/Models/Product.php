<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Models\ProductWarehouse;
class Product extends Model
{
    use Sortable;

    public $sortable = [
        'name',
        'qty',
        'serial_number',
        'price',
        'quantity',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(ExtendedProperty::class, 'category_id');
    }

    /**
     * Get all records.
     *
     * @param array $fields
     *
     * @return mixed
     */
    public static function getAll($fields = ['*'])
    {
        return self::orderBy('name')->get($fields);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'product_id');
    }

    public function Warehouse()
    {
        return $this->hasMany(ProductWarehouse::class);
    }


    public function getQty($product_id, $location)
    {
        if ($location > 0) {
            $qty = 0;
            foreach ($this->Warehouse as $product) {   
                if($product_id == $product->product_id && $product->warehouse_id == $location):
                     $qty+= $product->quantity;
                endif;
            }
            return $qty;
        }else{
            $qty = 0;
            foreach ($this->Warehouse as $product) {
                $qty+=$product->quantity;
            }
            return $qty;
        }
    }
}
