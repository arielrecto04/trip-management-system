<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liquidation extends Model
{
    protected $fillable = [
        'driver_id',
        'trip_id',
        'auditor_id',
        'initial_advance',
        'total_approved_expenses',
        'total_collected_cod',
        'net_cash_due_from_driver',
        'date_audited',
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

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}
