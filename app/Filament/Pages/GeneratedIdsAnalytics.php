<?php

namespace App\Filament\Pages;

use App\Models\EmployeeId;
use App\Models\EmployeeIdPrintStatusHistory;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Panel;

class GeneratedIdsAnalytics extends Page
{
    protected static string $routePath = '/analytics';

    protected static ?string $title = 'Generated IDs Analytics';

    protected static ?string $navigationLabel = 'Analytics';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.generated-ids-analytics';

    public static function getRoutePath(Panel $panel): string
    {
        return static::$routePath;
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'Monthly overview of generated employee IDs';
    }

    public function getTotalGeneratedCount(): int
    {
        return EmployeeId::query()->count();
    }

    public function getGeneratedTodayCount(): int
    {
        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');

        return EmployeeId::query()
            ->whereDate('created_at', $today)
            ->count();
    }

    public function getGeneratedThisMonthCount(): int
    {
        $now = new \DateTimeImmutable('now');

        return EmployeeId::query()
            ->whereYear('created_at', (int) $now->format('Y'))
            ->whereMonth('created_at', (int) $now->format('m'))
            ->count();
    }

    /**
     * @return array<int, array{label: string, total: int}>
     */
    public function getMonthlyBreakdown(int $months = 12): array
    {
        $months = max(1, $months);
        $currentMonth = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $start = $currentMonth->modify('-' . ($months - 1) . ' months');
        $end = $currentMonth;

        $rawCounts = EmployeeId::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total")
            ->whereBetween('created_at', [
                $start->format('Y-m-01 00:00:00'),
                $end->modify('last day of this month')->format('Y-m-d 23:59:59'),
            ])
            ->groupBy('period')
            ->pluck('total', 'period');

        $breakdown = [];

        for ($month = $start; $month <= $end; $month = $month->modify('+1 month')) {
            $periodKey = $month->format('Y-m');

            $breakdown[] = [
                'label' => $month->format('M Y'),
                'total' => (int) ($rawCounts[$periodKey] ?? 0),
            ];
        }

        return $breakdown;
    }

    /**
     * @return array<int, array{office: string, total: int}>
     */
    public function getTopOffices(int $limit = 5): array
    {
        return EmployeeId::query()
            ->selectRaw('office_name, COUNT(*) as total')
            ->whereNotNull('office_name')
            ->where('office_name', '!=', '')
            ->groupBy('office_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn (EmployeeId $record): array => [
                'office' => (string) $record->office_name,
                'total' => (int) $record->total,
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, printed: int, in_progress: int, cancelled: int, total: int}>
     */
    public function getMonthlyPrintStatusBreakdown(int $months = 12): array
    {
        $months = max(1, $months);
        $currentMonth = (new \DateTimeImmutable('first day of this month'))->setTime(0, 0);
        $start = $currentMonth->modify('-' . ($months - 1) . ' months');
        $end = $currentMonth;

        // Use employee records as the chart source so past months always have data,
        // even when print status history logs are sparse.
        $rawCounts = EmployeeId::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, print_status as status, COUNT(*) as total")
            ->whereBetween('created_at', [
                $start->format('Y-m-01 00:00:00'),
                $end->modify('last day of this month')->format('Y-m-d 23:59:59'),
            ])
            ->groupBy('period', 'status')
            ->get()
            ->groupBy('period')
            ->map(fn ($rows) => $rows->pluck('total', 'status'));

        $breakdown = [];

        for ($month = $start; $month <= $end; $month = $month->modify('+1 month')) {
            $periodKey = $month->format('Y-m');
            $counts = $rawCounts->get($periodKey);

            $printed = (int) ($counts[EmployeeId::PRINT_STATUS_DONE_PRINTING] ?? 0);
            $inProgress = (int) ($counts[EmployeeId::PRINT_STATUS_IN_PROGRESS] ?? 0);
            $cancelled = (int) ($counts[EmployeeId::PRINT_STATUS_CANCELLED] ?? 0);

            $breakdown[] = [
                'label' => $month->format('M Y'),
                'printed' => $printed,
                'in_progress' => $inProgress,
                'cancelled' => $cancelled,
                'total' => $printed + $inProgress + $cancelled,
            ];
        }

        return $breakdown;
    }

    /**
     * @return array<int, array{title: string, description: string, time: string}>
     */
    public function getPrintStatusHistoryActivities(int $limit = 10): array
    {
        return EmployeeIdPrintStatusHistory::query()
            ->with(['employeeId:id,id_number,first_name,last_name', 'changedBy:id,name'])
            ->orderByDesc('changed_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (EmployeeIdPrintStatusHistory $history): array {
                $employeeIdNumber = (string) optional($history->employeeId)->id_number;
                $actorName = (string) (optional($history->changedBy)->name ?: 'System');

                return [
                    'title' => 'Print Status Updated',
                    'description' => sprintf(
                        'ID %s changed from "%s" to "%s" by %s',
                        $employeeIdNumber !== '' ? $employeeIdNumber : 'N/A',
                        $this->formatPrintStatusLabel((string) $history->old_status),
                        $this->formatPrintStatusLabel((string) $history->new_status),
                        $actorName,
                    ),
                    'time' => (string) optional($history->changed_at)->format('M d, Y h:i A'),
                ];
            })
            ->all();
    }

    protected function formatPrintStatusLabel(string $status): string
    {
        return match ($status) {
            EmployeeId::PRINT_STATUS_DONE_PRINTING => 'Done Printing',
            EmployeeId::PRINT_STATUS_IN_PROGRESS => 'In Progress',
            EmployeeId::PRINT_STATUS_CANCELLED => 'Cancelled',
            '' => 'Unknown',
            default => (string) str($status)->replace('_', ' ')->title(),
        };
    }
}
