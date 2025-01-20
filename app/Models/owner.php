<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class owner extends Model
{
    use HasFactory;

    public $timestamps = true;
    
    protected $fillable = ['name', 'email', 'active']; 


    public function residences()
    {
        return $this->belongsToMany(residence::class);
    }
}
