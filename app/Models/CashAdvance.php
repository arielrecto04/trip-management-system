<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvance extends Model
{
    protected $fillable = [
        'driver_id',
        'trip_id',
        'amount_issued',
        'issue_date',
        'issuer_id',
        'status',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issuer_id');
    }
}
