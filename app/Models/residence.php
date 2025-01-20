<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class residence extends Model
{
    use HasFactory;

    public $timestamps = true;
    
    protected $fillable = [
        'number',
        'name',
        'active'
    ];


    public function owner()
    {
        return $this->belongsToMany(owner::class);
    }
}
