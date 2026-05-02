<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class EmployeeId extends Model
{
    public const PRINT_STATUS_IN_PROGRESS = 'in_progress';

    public const PRINT_STATUS_DONE_PRINTING = 'done_printing';

    public const PRINT_STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'id_number',
        'first_name',
        'middle_initial',
        'last_name',
        'extension',
        'designation',
        'office_name',
        'profile_picture',
        'id_card_image',
        'qr_code_data',
        'signature',
        'print_status',
    ];

    protected static function booted(): void
    {
        static::updated(function (self $employeeId): void {
            if (! $employeeId->wasChanged('print_status')) {
                return;
            }

            EmployeeIdPrintStatusHistory::query()->create([
                'employee_id_id' => $employeeId->id,
                'old_status' => (string) $employeeId->getOriginal('print_status'),
                'new_status' => (string) $employeeId->print_status,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);
        });
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->middle_initial}. {$this->last_name}";
    }

    public function printStatusHistories(): HasMany
    {
        return $this->hasMany(EmployeeIdPrintStatusHistory::class, 'employee_id_id');
    }

    /**
     * @return array<string, string>
     */
    public static function getPrintStatusOptions(): array
    {
        return [
            self::PRINT_STATUS_IN_PROGRESS => 'In Progress',
            self::PRINT_STATUS_DONE_PRINTING => 'Done Printing',
            self::PRINT_STATUS_CANCELLED => 'Cancelled',
        ];
    }

    public static function getPrintStatusLabel(string $status): string
    {
        return self::getPrintStatusOptions()[$status]
            ?? (string) str($status)->replace('_', ' ')->title();
    }
}
