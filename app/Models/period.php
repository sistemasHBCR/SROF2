<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class period extends Model
{
    use HasFactory;

    protected $fillable = [
        'start',
        'end',
        'concept',
        'status_id',
        'parametersutilities',
        'templateutilities',
        'token'
    ];

    public function status()
    {
        return $this->belongsTo(status::class);
    }

}
