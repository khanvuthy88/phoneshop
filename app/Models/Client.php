<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Client extends Model
{
    use Sortable;

    public $sortable = [
        'name',
        'gender',
        'id_card_number',
        'first_phone',
        'second_phone',
        'sponsor_name',
        'sponsor_phone',
        'sponsor_phone_2',
    ];

    public function commune()
    {
        return $this->belongsTo(Address::class, 'commune_id');
    }

    public function district()
    {
        return $this->belongsTo(Address::class, 'district_id');
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
        return $this->hasMany(Loan::class, 'client_id');
    }

    public function province()
    {
        return $this->belongsTo(Address::class, 'province_id');
    }

    public function sponsorCommune()
    {
        return $this->belongsTo(Address::class, 'sponsor_commune_id');
    }

    public function sponsorDistrict()
    {
        return $this->belongsTo(Address::class, 'sponsor_district_id');
    }

    public function sponsorProvince()
    {
        return $this->belongsTo(Address::class, 'sponsor_province_id');
    }

    public function sponsorVillage()
    {
        return $this->belongsTo(Address::class, 'sponsor_village_id');
    }

    public function village()
    {
        return $this->belongsTo(Address::class, 'village_id');
    }
}
