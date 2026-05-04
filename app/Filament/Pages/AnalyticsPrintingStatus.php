<?php

namespace App\Filament\Pages;

use App\Models\EmployeeId;
use App\Models\EmployeeIdPrintStatusHistory;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Carbon;

class AnalyticsPrintingStatus extends Page
{
    protected static string $routePath = '/analytics-printing-status';

    protected static ?string $title = 'Analytics Printing Status';

    protected static ?string $navigationLabel = 'Analytics Printing Status';

    protected static ?string $navigationParentItem = 'Analytics';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-printer';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.analytics-printing-status';

    public static function getRoutePath(Panel $panel): string
    {
        return static::$routePath;
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'Monthly tracker for created, updated, and deleted IDs with full history';
    }

    /**
     * @return array{created: int, updated: int, deleted: int}
     */
    public function getMonthlyActivitySummary(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $created = EmployeeId::query()
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $updated = EmployeeIdPrintStatusHistory::query()
            ->whereBetween('changed_at', [$start, $end])
            ->where('event_type', EmployeeId::HISTORY_EVENT_UPDATED)
            ->distinct('employee_id_id')
            ->count('employee_id_id');

        $deleted = EmployeeIdPrintStatusHistory::query()
            ->whereBetween('changed_at', [$start, $end])
            ->where('event_type', EmployeeId::HISTORY_EVENT_DELETED)
            ->count();

        return [
            'created' => $created,
            'updated' => $updated,
            'deleted' => $deleted,
        ];
    }

    /**
     * @return array<int, array{month: string, total: int}>
     */
    public function getMonthlyCreatedIds(int $months = 12): array
    {
        return EmployeeId::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->where('created_at', '>=', now()->subMonths($months - 1)->startOfMonth())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->map(fn (EmployeeId $row): array => [
                'month' => (string) Carbon::createFromFormat('Y-m', (string) $row->ym)->format('M Y'),
                'total' => (int) $row->total,
            ])
            ->all();
    }

    public function getCreatedThisMonthCount(): int
    {
        return $this->getMonthlyActivitySummary()['created'];
    }

    /**
     * @return array<int, array{id_number: string, employee_name: string, event: string, details: string, changed_by: string, changed_at: string}>
     */
    public function getHistoryTracker(int $limit = 40): array
    {
        return EmployeeIdPrintStatusHistory::query()
            ->with(['employeeId:id,id_number,first_name,middle_initial,last_name', 'changedBy:id,name'])
            ->whereIn('event_type', [
                EmployeeId::HISTORY_EVENT_CREATED,
                EmployeeId::HISTORY_EVENT_UPDATED,
                EmployeeId::HISTORY_EVENT_DELETED,
            ])
            ->orderByDesc('changed_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (EmployeeIdPrintStatusHistory $history): array {
                $employee = $history->employeeId;
                $idNumber = (string) ($employee?->id_number ?? 'N/A');
                $employeeName = trim(collect([
                    $employee?->first_name,
                    $employee?->middle_initial ? rtrim((string) $employee->middle_initial, '.') . '.' : null,
                    $employee?->last_name,
                ])->filter()->implode(' '));

                return [
                    'id_number' => $idNumber,
                    'employee_name' => $employeeName !== '' ? $employeeName : 'Unknown Employee',
                    'event' => $this->formatEventLabel((string) $history->event_type),
                    'details' => $this->formatHistoryDetails($history),
                    'changed_by' => (string) ($history->changedBy?->name ?? 'System'),
                    'changed_at' => (string) optional($history->changed_at)?->format('M d, Y h:i A'),
                ];
            })
            ->all();
    }

    protected function formatEventLabel(string $eventType): string
    {
        return match ($eventType) {
            EmployeeId::HISTORY_EVENT_CREATED => 'Created',
            EmployeeId::HISTORY_EVENT_STATUS_UPDATED => 'Print Status Updated',
            EmployeeId::HISTORY_EVENT_DELETED => 'Deleted',
            default => 'Information Updated',
        };
    }

    protected function formatHistoryDetails(EmployeeIdPrintStatusHistory $history): string
    {
        $eventType = (string) $history->event_type;

        if ($eventType === EmployeeId::HISTORY_EVENT_CREATED) {
            return 'Created new employee ID record';
        }

        if ($eventType === EmployeeId::HISTORY_EVENT_DELETED) {
            return 'Deleted employee ID record';
        }

        $field = (string) $history->field_name;
        $oldValue = (string) ($history->old_value ?? '-');
        $newValue = (string) ($history->new_value ?? '-');

        if ($field === 'print_status') {
            return sprintf(
                'Print status changed from "%s" to "%s"',
                EmployeeId::getPrintStatusLabel($oldValue),
                EmployeeId::getPrintStatusLabel($newValue),
            );
        }

        $fieldLabel = (string) str($field)->replace('_', ' ')->title();

        return sprintf('%s changed from "%s" to "%s"', $fieldLabel, $oldValue, $newValue);
    }
}
