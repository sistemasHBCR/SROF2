<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class parameters_res_utilities extends Model
{
    protected $table = 'parameters_res_utilities';
    protected $fillable = ['residence_id', 'service_id', 'period_id','rate_id', 'fixedrate_value', 'parameters_cost_id', 'fixedrate_isconditional', 'consumptionlower_is','flaterate_consumption_islower']; 
    use HasFactory;
     /**
     * Relación con la tabla parameters_costs_utilities
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function residence()
    {
        return $this->belongsTo(residence::class, 'residence_id');
    }
    public function cost()
    {
        return $this->belongsTo(parameters_costs_utilities::class, 'parameters_cost_id');
    }
}
