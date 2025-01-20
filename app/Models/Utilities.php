<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilities extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'residencia',
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
