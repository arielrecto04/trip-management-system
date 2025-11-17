<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripStop extends Model
{
    protected $fillable = [
        'trip_id',
        'order_id',
        'sequence_no',
        'stop_name',
        'is_pickup_stop',
        'is_delivery_stop',
        'planned_latitude',
        'planned_longitude',
        'planned_arrival_time',
        'actual_arrival_time',
        'actual_arrival_latitude',
        'actual_arrival_longitude',
        'is_completed',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function proofOfDelivery()
    {
        return $this->hasOne(ProofOfDelivery::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
