<?php

namespace App\Filament\Pages;

use App\Models\EmployeeId;
use App\Models\Setting;
use App\Services\QRCodeGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class IdCreationDashboard extends Page
{
    protected static string $routePath = '/';

    protected Width | string | null $maxContentWidth = Width::Full;

    protected static ?int $navigationSort = -2;

    public static function getRoutePath(Panel $panel): string
    {
        return static::$routePath;
    }

    protected static ?string $title = null;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedHome;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];
    public ?string $templateBackgroundDataUri = null;
    public ?int $templateBackgroundWidth = null;
    public ?int $templateBackgroundHeight = null;
    protected ?string $lastQrPayload = null;
    protected ?string $lastQrDataUri = null;

    public function getTitle(): string | \Illuminate\Contracts\Support\Htmlable
    {
        return 'Dashboard';
    }

    public function getSubheading(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'Create a new employee identification card';
    }

    public function mount(): void
    {
        $this->templateBackgroundDataUri = $this->getTemplatePreviewBackgroundDataUri();
        [$this->templateBackgroundWidth, $this->templateBackgroundHeight] = $this->getTemplatePreviewDimensions();

        $this->form->fill([
            'first_name' => '',
            'middle_initial' => '',
            'last_name' => '',
            'extension' => '',
            'designation' => '',
            'office_name' => '',
            'id_number' => '',
            'profile_picture' => null,
            'mirror_print' => false,
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(1);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'lg' => 2])
                    ->schema([
                        Section::make('Input fields')
                            ->description('Enter employee details. Fields marked * are required.')
                            ->components([
                                TextInput::make('first_name')
                                    ->label('First name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(),
                                TextInput::make('middle_initial')
                                    ->label('Middle initial')
                                    ->maxLength(1)
                                    ->live(),
                                TextInput::make('last_name')
                                    ->label('Last name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(),
                                TextInput::make('extension')
                                    ->label('Extension')
                                    ->placeholder('Optional')
                                    ->maxLength(50)
                                    ->live(),
                                TextInput::make('designation')
                                    ->label('Designation')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(),
                                TextInput::make('office_name')
                                    ->label('Office name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(),
                                TextInput::make('id_number')
                                    ->label('ID no.')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(EmployeeId::class, 'id_number')
                                    ->live(),
                                FileUpload::make('profile_picture')
                                    ->label('Profile picture')
                                    ->acceptedFileTypes([
                                        'image/png',
                                        'image/jpeg',
                                        'image/webp',
                                    ])
                                    ->directory('profile-pictures')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->maxSize(5120)
                                    ->fetchFileInformation(false)
                                    ->downloadable(false)
                                    ->openable(false)
                                    ->live(),
                                Toggle::make('mirror_print')
                                    ->label('Mirror print')
                                    ->helperText('Use this when printing for transfer/media that needs reversed output.')
                                    ->inline(false)
                                    ->live(),
                            ])
                            ->columnSpan(1),

                        Section::make('ID template')
                            ->description('Preview updates as you type.')
                            ->components([
                                View::make('filament.pages.partials.id-card-preview')
                                    ->viewData(fn (): array => [
                                        'preview' => $this->data ?? [],
                                        'qrDataUri' => $this->getQrPreviewDataUri(),
                                        'profileUrl' => $this->getProfilePreviewUrl(),
                                        'templateBackgroundUrl' => $this->templateBackgroundDataUri,
                                        'templateBackgroundWidth' => $this->templateBackgroundWidth,
                                        'templateBackgroundHeight' => $this->templateBackgroundHeight,
                                        'mirrorPrint' => (bool) ($this->data['mirror_print'] ?? false),
                                    ]),
                            ])
                            ->footerActions([
                                Action::make('print')
                                    ->label('Print')
                                    ->icon(Heroicon::OutlinedPrinter)
                                    ->color('gray')
                                    ->action(fn () => $this->js('window.print()')),
                                Action::make('download_image')
                                    ->label('Download Image')
                                    ->icon(Heroicon::OutlinedArrowDownTray)
                                    ->color('primary')
                                    ->action(fn () => $this->dispatch('download-preview-id-card', mirror: false)),
                                Action::make('download_image_mirror')
                                    ->label('Download Mirror Image')
                                    ->icon(Heroicon::OutlinedArrowDownTray)
                                    ->color('gray')
                                    ->action(fn () => $this->dispatch('download-preview-id-card', mirror: true)),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('id-creation-form'),
                Section::make('Dashboard tracker: print status')
                    ->description('Track IDs that are still in progress versus done printing.')
                    ->components([
                        View::make('filament.pages.partials.print-status-tracker')
                            ->viewData(fn (): array => [
                                'trackerRows' => $this->getRecentPrintTrackerRows(),
                                'statusSummary' => $this->getPrintStatusSummary(),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array{in_progress: int, done_printing: int, cancelled: int}
     */
    public function getPrintStatusSummary(): array
    {
        $counts = EmployeeId::query()
            ->selectRaw('print_status, COUNT(*) as total')
            ->groupBy('print_status')
            ->pluck('total', 'print_status');

        return [
            'in_progress' => (int) ($counts[EmployeeId::PRINT_STATUS_IN_PROGRESS] ?? 0),
            'done_printing' => (int) ($counts[EmployeeId::PRINT_STATUS_DONE_PRINTING] ?? 0),
            'cancelled' => (int) ($counts[EmployeeId::PRINT_STATUS_CANCELLED] ?? 0),
        ];
    }

    /**
     * @return array<int, array{id_number: string, full_name: string, office_name: string, print_status: string, updated_at: string}>
     */
    public function getRecentPrintTrackerRows(int $limit = 10): array
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

    protected function getProfilePreviewUrl(): ?string
    {
        $raw = $this->data['profile_picture'] ?? null;
        $candidates = $this->flattenProfilePictureCandidates($raw);

        foreach ($candidates as $candidate) {
            $resolved = $this->resolveProfileCandidateToUrl($candidate);

            if ($resolved) {
                return $resolved;
            }
        }

        return null;
    }

    protected function getQrPreviewDataUri(): ?string
    {
        $d = $this->data ?? [];
        $payload = $this->buildQrPayloadFromData($d);
        if ($payload === '') {
            return null;
        }

        if ($this->lastQrPayload === $payload && $this->lastQrDataUri !== null) {
            return $this->lastQrDataUri;
        }

        $raw = app(QRCodeGenerator::class)->generateBase64($payload);

        $this->lastQrPayload = $payload;
        $this->lastQrDataUri = str_starts_with($raw, 'data:')
            ? $raw
            : 'data:image/png;base64,' . $raw;

        return $this->lastQrDataUri;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function buildQrPayloadFromData(array $data): string
    {
        $first = trim((string) ($data['first_name'] ?? ''));
        $middle = trim((string) ($data['middle_initial'] ?? ''));
        $last = trim((string) ($data['last_name'] ?? ''));
        $extension = trim((string) ($data['extension'] ?? ''));
        $designation = trim((string) ($data['designation'] ?? ''));
        $office = trim((string) ($data['office_name'] ?? ''));

        $middleInitial = $middle !== '' ? strtoupper(mb_substr(rtrim($middle, '.'), 0, 1)) . '.' : '';
        $fullName = trim(collect([$first, $middleInitial, $last, $extension])
            ->filter(fn (string $part): bool => $part !== '')
            ->implode(' '));

        return collect([$fullName, $designation, $office])
            ->filter(fn (string $part): bool => $part !== '')
            ->implode("\n");
    }

    protected function getTemplatePreviewBackgroundDataUri(): ?string
    {
        $path = Setting::get('id_card_preview_template_image');
        if (! $path) {
            return null;
        }

        $path = str_replace('\\', '/', (string) $path);

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?? 'image/png';

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    protected function getTemplatePreviewDimensions(): array
    {
        $path = Setting::get('id_card_preview_template_image');
        if (! $path) {
            return [null, null];
        }

        $path = str_replace('\\', '/', (string) $path);

        if (! Storage::disk('public')->exists($path)) {
            return [null, null];
        }

        $absolutePath = Storage::disk('public')->path($path);
        $size = @getimagesize($absolutePath);
        if (! is_array($size) || ! isset($size[0], $size[1])) {
            return [null, null];
        }

        return [(int) $size[0], (int) $size[1]];
    }

    protected function getDiskImageAsDataUri(string $disk, string $path): ?string
    {
        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $contents = Storage::disk($disk)->get($path);
        $mimeType = Storage::disk($disk)->mimeType($path) ?? 'image/png';

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    /**
     * @return array<int, mixed>
     */
    protected function flattenProfilePictureCandidates(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (! is_array($value)) {
            return [$value];
        }

        $flattened = [];

        array_walk_recursive($value, function (mixed $item) use (&$flattened): void {
            if ($item !== null && $item !== '') {
                $flattened[] = $item;
            }
        });

        return $flattened;
    }

    protected function resolveProfileCandidateToUrl(mixed $candidate): ?string
    {
        if ($candidate instanceof TemporaryUploadedFile) {
            try {
                return $candidate->temporaryUrl();
            } catch (\Throwable) {
                try {
                    $realPath = $candidate->getRealPath() ?: $candidate->getPathname();
                    if ($realPath && is_file($realPath)) {
                        $mime = mime_content_type($realPath) ?: 'image/png';
                        $contents = file_get_contents($realPath);

                        if ($contents !== false) {
                            return 'data:' . $mime . ';base64,' . base64_encode($contents);
                        }
                    }
                } catch (\Throwable) {
                    return null;
                }
            }
        }

        if (! is_string($candidate)) {
            return null;
        }

        if (str_starts_with($candidate, 'data:image/')) {
            return $candidate;
        }

        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            return $candidate;
        }

        if (str_starts_with($candidate, 'livewire-file:')) {
            try {
                $temp = TemporaryUploadedFile::createFromLivewire($candidate);

                return $temp->temporaryUrl();
            } catch (\Throwable) {
                return null;
            }
        }

        if (Storage::disk('public')->exists($candidate)) {
            return $this->getDiskImageAsDataUri('public', $candidate);
        }

        if (Storage::disk('local')->exists($candidate)) {
            return $this->getDiskImageAsDataUri('local', $candidate);
        }

        return null;
    }

}
