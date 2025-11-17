<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfDelivery extends Model
{
    protected $fillable = [
        'trip_stop_id',
        'recipient_name',
        'collected_cod_amount',
        'ePod_timestamp',
    ];

    public function stop()
    {
        return $this->belongsTo(TripStop::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
