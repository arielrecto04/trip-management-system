<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'house_number',
        'street',
        'region_code',
        'region_name',
        'province_code',
        'province_name',
        'city_code',
        'city_name',
        'barangay_code',
        'barangay_name',
        'zip_code',
        'latitude',
        'longitude',
        'contact_person',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'origin_warehouse_id');
    }

    public function contactPerson()
    {
        return $this->belongsTo(User::class, 'contact_person');
    }
    
}
