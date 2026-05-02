<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class EmployeeId extends Model
{
    public const HISTORY_EVENT_CREATED = 'created';

    public const HISTORY_EVENT_UPDATED = 'updated';

    public const HISTORY_EVENT_STATUS_UPDATED = 'status_updated';

    public const HISTORY_EVENT_DELETED = 'deleted';

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
        static::created(function (self $employeeId): void {
            EmployeeIdPrintStatusHistory::query()->create([
                'employee_id_id' => $employeeId->id,
                'event_type' => self::HISTORY_EVENT_CREATED,
                'field_name' => null,
                'old_status' => null,
                'new_status' => (string) $employeeId->print_status,
                'old_value' => null,
                'new_value' => (string) $employeeId->id_number,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);
        });

        static::updated(function (self $employeeId): void {
            $changes = collect($employeeId->getChanges())
                ->except(['updated_at'])
                ->keys();

            if ($changes->isEmpty()) {
                return;
            }

            foreach ($changes as $field) {
                $oldValue = $employeeId->getOriginal($field);
                $newValue = $employeeId->getAttribute($field);
                $isStatusChange = $field === 'print_status';

                EmployeeIdPrintStatusHistory::query()->create([
                    'employee_id_id' => $employeeId->id,
                    'event_type' => $isStatusChange ? self::HISTORY_EVENT_STATUS_UPDATED : self::HISTORY_EVENT_UPDATED,
                    'field_name' => $field,
                    'old_status' => $isStatusChange ? self::normalizeHistoryValue($oldValue) : null,
                    'new_status' => $isStatusChange
                        ? self::normalizeHistoryValue($newValue)
                        : (string) $employeeId->print_status,
                    'old_value' => self::normalizeHistoryValue($oldValue),
                    'new_value' => self::normalizeHistoryValue($newValue),
                    'changed_by' => Auth::id(),
                    'changed_at' => now(),
                ]);
            }
        });

        static::deleting(function (self $employeeId): void {
            EmployeeIdPrintStatusHistory::query()->create([
                'employee_id_id' => $employeeId->id,
                'event_type' => self::HISTORY_EVENT_DELETED,
                'field_name' => null,
                'old_status' => (string) $employeeId->print_status,
                'new_status' => (string) $employeeId->print_status,
                'old_value' => (string) $employeeId->id_number,
                'new_value' => null,
                'changed_by' => Auth::id(),
                'changed_at' => now(),
            ]);
        });
    }

    protected static function normalizeHistoryValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        $encodedValue = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encodedValue === false ? null : $encodedValue;
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
