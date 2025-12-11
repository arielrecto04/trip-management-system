<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'external_order_id',
        'customer_name',
        'recipient_phone',
        'pickup_address',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'weight_kg',
        'volume_m3',
        'required_time_window_start',
        'required_time_window_end',
        'collected_cod_amount',
        'status',
    ];

    public function tripStops()
    {
        return $this->hasMany(TripStop::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'origin_warehouse_id');
    }
}
