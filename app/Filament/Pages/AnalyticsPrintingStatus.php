<?php

namespace App\Filament\Pages;

use App\Models\EmployeeId;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Panel;

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
        return 'Tracking IDs in progress and done printing';
    }

    /**
     * @return array{in_progress: int, done_printing: int, cancelled: int, total: int}
     */
    public function getPrintStatusSummary(): array
    {
        $counts = EmployeeId::query()
            ->selectRaw('print_status, COUNT(*) as total')
            ->groupBy('print_status')
            ->pluck('total', 'print_status');

        $inProgress = (int) ($counts[EmployeeId::PRINT_STATUS_IN_PROGRESS] ?? 0);
        $donePrinting = (int) ($counts[EmployeeId::PRINT_STATUS_DONE_PRINTING] ?? 0);
        $cancelled = (int) ($counts[EmployeeId::PRINT_STATUS_CANCELLED] ?? 0);

        return [
            'in_progress' => $inProgress,
            'done_printing' => $donePrinting,
            'cancelled' => $cancelled,
            'total' => $inProgress + $donePrinting + $cancelled,
        ];
    }

    /**
     * @return array<int, array{id_number: string, full_name: string, office_name: string, print_status: string, updated_at: string}>
     */
    public function getRecentPrintTracker(int $limit = 20): array
    {
        return EmployeeId::query()
            ->select(['id_number', 'first_name', 'middle_initial', 'last_name', 'office_name', 'print_status', 'updated_at'])
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (EmployeeId $record): array => [
                'id_number' => (string) $record->id_number,
                'full_name' => trim(collect([
                    $record->first_name,
                    $record->middle_initial ? rtrim((string) $record->middle_initial, '.') . '.' : null,
                    $record->last_name,
                ])->filter()->implode(' ')),
                'office_name' => (string) $record->office_name,
                'print_status' => (string) $record->print_status,
                'updated_at' => (string) optional($record->updated_at)?->format('M d, Y h:i A'),
            ])
            ->all();
    }
}
