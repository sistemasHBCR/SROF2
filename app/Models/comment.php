<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comment extends Model
{
    use HasFactory;
    protected $table = "comments";
    protected $fillable = [
        'user_id',
        'comment'
    ];

    public function cell()
    {
        return $this->belongsToMany(cell::class, 'cell_comment', 'comment_id', 'cell_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
