<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use App\Constants\ExtendedProperty as EPropertyType;

class ExtendedProperty extends Model
{
    use Sortable;

    public $sortable = ['value'];

    public static function allBrands()
    {
        return self::brand()->orderBy('value')->get();
    }

    public static function allPositions()
    {
        return self::position()->orderBy('value')->get();
    }

    public static function allProductCategories()
    {
        return self::productCategory()->orderBy('value')->get();
    }

    public static function brand()
    {
        return self::where('property_name', EPropertyType::BRAND);
    }

    public static function position()
    {
        return self::where('property_name', EPropertyType::POSITION);
    }

    public static function productCategory()
    {
        return self::where('property_name', EPropertyType::PRODUCT_CATEGORY);
    }

}
