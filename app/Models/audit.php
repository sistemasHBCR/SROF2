<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\user;

class audit extends Model
{
    use HasFactory;
    protected $table = "audit";
    protected $fillable = [
        'user_id',
        'action',
        'panel',
        'module',
        'description',
        'datatable',
        'databefore',
        'dataafter',
    ];

 
    public function user()
    {
        return $this->belongsTo(user::class, 'user_id');
    }

}
