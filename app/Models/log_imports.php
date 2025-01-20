<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_imports extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'filepath',
        'template',
        'status_id',
        'period_id',
        'updated_by',
        'aproved_by',
        'updated_date',
        'aproved_date',
        'created_at',
        'updated_at'
    ];
}
