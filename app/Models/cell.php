<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cell extends Model
{
    use HasFactory;
    protected $table = "cells";
    protected $fillable = [
        'row',
        'name_column',
        'name_table',
    ];

    public function comments()
    {
        return $this->belongsToMany(comment::class, 'cell_comment', 'cell_id', 'comment_id');
    }
}
