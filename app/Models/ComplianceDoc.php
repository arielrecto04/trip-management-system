<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceDoc extends Model
{
    protected $fillable = [
        'compliable_id',
        'compliable_type',
        'doc_type',
        'doc_number',
        'expiration_date',
    ];

    public function compliable()
    {
        return $this->morphTo();
    }

    public function attachments(){
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
