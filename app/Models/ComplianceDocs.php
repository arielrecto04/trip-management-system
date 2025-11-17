<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceDocs extends Model
{
    protected $fillable = [
        'compliable_id',
        'compliable_type',
        'doc_type',
        'doc_number',
        'expiration_date',
        'file_path',
    ];

    public function compliable()
    {
        return $this->morphTo();
    }
}
