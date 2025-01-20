<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class imports extends Model
{
    use HasFactory;

    protected $table = 'imports';

    protected $fillable = [
        'period_id',
        'importlog_tb',
        'import_token',
        'version',
        'status_id',
        'uploaded_by',
        'maintenance_action',
        'maintenance_description',
        'maintenance_aproved_by',
        'maintenance_aproved_date',
        'finance_action',
        'finance_description',
        'finance_aproved_by',
        'finance_aproved_date',
    ];

    public function period()
    {
        return $this->belongsTo(period::class, 'period_id');
    }

    public function status()
    {
        return $this->belongsTo(status::class, 'status_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(user::class, 'uploaded_by');
    }

    public function maintenanceApprovedBy()
    {
        return $this->belongsTo(user::class, 'maintenance_aproved_by');
    }

    public function financeApprovedBy()
    {
        return $this->belongsTo(user::class, 'finance_aproved_by');
    }
    
}
