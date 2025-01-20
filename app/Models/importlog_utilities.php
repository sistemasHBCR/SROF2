<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Importlog_Utilities extends Model
{
    use HasFactory;

    protected $table = 'importlog_utilities';

    protected $fillable = [
        'residencia',
        'token',
        'room',
        'owner',
        'ocupacion',
        'kw',
        'agua',
        'gas',
        'total_kw',
        'total_kwfee',
        'total_gas',
        'total_gasfee',
        'total_agua',
        'total_sewer',
        'subtotal',
        'tax',
        'total'
    ];

    
}
