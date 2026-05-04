<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeIdPrintStatusHistory extends Model
{
    protected $fillable = [
        'employee_id_id',
        'event_type',
        'field_name',
        'old_status',
        'new_status',
        'old_value',
        'new_value',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function employeeId(): BelongsTo
    {
        return $this->belongsTo(EmployeeId::class, 'employee_id_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
