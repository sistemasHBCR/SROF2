<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class parameters_tc_utilities extends Model
{
    protected $table = 'parameters_tc_utilities';
    protected $fillable = ['tc', 'tax', 'period_id']; 
    use HasFactory;
}
