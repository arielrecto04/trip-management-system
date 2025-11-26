<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'name',
        'attachable',
        'type',
        'size',
        'extension',
        'url',
        'uploaded_by',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

}
