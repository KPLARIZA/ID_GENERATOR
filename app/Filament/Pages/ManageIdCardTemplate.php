<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

class ManageIdCardTemplate extends Page
{
    protected static ?string $title = 'ID card DOCX template';

    protected static ?string $navigationLabel = 'ID card template';

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 20;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'docx_template' => Setting::get('id_card_docx_template_path'),
            'preview_template_image' => null,
            'remove_preview_template_image' => false,
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
                Section::make('Word template')
                    ->description('Upload a .docx file with placeholders: ${FIRST_NAME}, ${MIDDLE_INITIAL}, ${LAST_NAME}, ${FULL_NAME}, ${DESIGNATION}, ${OFFICE_NAME}, ${ID_NUMBER}, ${PROFILE_PICTURE}, ${QR_CODE}. If none is uploaded, the file at resources/templates/id-card-template.docx is used.')
                    ->components([
                        FileUpload::make('docx_template')
                            ->label('Template (.docx)')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            ])
                            ->disk('local')
                            ->directory('id-templates')
                            ->visibility('private')
                            ->fetchFileInformation(false)
                            ->downloadable()
                            ->openable(),
                    ]),
                Section::make('Preview template image')
                    ->description('Upload an image version of your exact ID layout for on-screen preview.')
                    ->components([
                        FileUpload::make('preview_template_image')
                            ->label('Template preview image')
                            ->acceptedFileTypes([
                                'image/png',
                                'image/jpeg',
                                'image/webp',
                            ])
                            ->disk('public')
                            ->directory('id-template-previews')
                            ->visibility('public')
                            ->fetchFileInformation(false)
                            ->multiple(false)
                            ->maxFiles(1)
                            ->appendFiles(false)
                            ->deletable()
                            ->downloadable(false)
                            ->openable(false)
                            ->helperText('Upload a new image to replace the current template.'),
                        Toggle::make('remove_preview_template_image')
                            ->label('Remove current template image')
                            ->helperText('Turn on then Save to clear the current template image.'),
                        View::make('filament.pages.partials.current-template-image')
                            ->viewData(fn (): array => [
                                'currentTemplateDataUri' => $this->getCurrentTemplateImageDataUri(),
                            ]),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make($this->getFormActions())
                            ->alignment(Alignment::Start)
                            ->key('form-actions'),
                    ]),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save template')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $path = $data['docx_template'] ?? null;
        $previewImage = $data['preview_template_image'] ?? null;
        $removePreviewImage = (bool) ($data['remove_preview_template_image'] ?? false);

        if (is_array($path)) {
            $path = $path[0] ?? null;
        }
        if (is_array($previewImage)) {
            $previewImage = $previewImage[0] ?? null;
        }

        if ($path) {
            $previous = Setting::get('id_card_docx_template_path');
            if ($previous && $previous !== $path && Storage::disk('local')->exists($previous)) {
                Storage::disk('local')->delete($previous);
            }

            Setting::set('id_card_docx_template_path', $path);
        }

        $previous = Setting::get('id_card_preview_template_image');

        if ($previewImage) {
            if ($previous && $previous !== $previewImage && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }

            Setting::set('id_card_preview_template_image', $previewImage);
        } elseif ($removePreviewImage) {
            if ($previous && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }

            Setting::set('id_card_preview_template_image', null);
        }

        Notification::make()
            ->title('Template saved')
            ->success()
            ->send();
    }

    protected function getCurrentTemplateImageDataUri(): ?string
    {
        $path = Setting::get('id_card_preview_template_image');
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?? 'image/png';

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }
}
