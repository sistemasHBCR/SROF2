<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class parameters_costs_utilities extends Model
{
    protected $table = 'parameters_costs_utilities';
    protected $fillable = ['volume_code', 'volume', 'cost_formula', 'cost', 'service_id', 'period_id']; 
    use HasFactory;
}
