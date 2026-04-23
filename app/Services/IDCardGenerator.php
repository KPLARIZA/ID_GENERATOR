<?php

namespace App\Services;

use App\Models\EmployeeId;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class IDCardGenerator
{
    protected QRCodeGenerator $qrCodeGenerator;

    public function __construct()
    {
        $this->qrCodeGenerator = new QRCodeGenerator();
    }

    /**
     * Generate ID card document from a DOCX template.
     */
    public function generate(EmployeeId $employeeId): string
    {
        $templatePath = $this->resolveTemplatePath();

        $template = new TemplateProcessor($templatePath);
        $qrAbsolutePath = $this->generateQrForEmployee($employeeId);
        $profileAbsolutePath = $this->resolveProfilePicturePath($employeeId);

        $template->setValue('FIRST_NAME', $employeeId->first_name ?? '');
        $template->setValue('MIDDLE_INITIAL', strtoupper((string) ($employeeId->middle_initial ?? '')));
        $template->setValue('LAST_NAME', $employeeId->last_name ?? '');
        $template->setValue('DESIGNATION', $employeeId->designation ?? '');
        $template->setValue('OFFICE_NAME', $employeeId->office_name ?? '');
        $template->setValue('ID_NUMBER', $employeeId->id_number ?? '');
        $template->setValue('FULL_NAME', trim((string) $employeeId->full_name));

        if ($profileAbsolutePath !== null) {
            $template->setImageValue('PROFILE_PICTURE', [
                'path' => $profileAbsolutePath,
                'ratio' => true,
                'width' => 170,
                'height' => 170,
            ]);
        } else {
            $template->setValue('PROFILE_PICTURE', '');
        }

        $template->setImageValue('QR_CODE', [
            'path' => $qrAbsolutePath,
            'ratio' => true,
            'width' => 120,
            'height' => 120,
        ]);

        $filename = 'id_cards/' . $employeeId->id . '_' . uniqid('', true) . '.docx';
        $outputPath = Storage::disk('public')->path($filename);
        $outputDirectory = dirname($outputPath);
        if (! is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }
        $template->saveAs($outputPath);

        return $filename;
    }

    protected function resolveProfilePicturePath(EmployeeId $employeeId): ?string
    {
        if (! $employeeId->profile_picture) {
            return null;
        }

        $path = Storage::disk('public')->path($employeeId->profile_picture);

        return file_exists($path) ? $path : null;
    }

    protected function generateQrForEmployee(EmployeeId $employeeId): string
    {
        $qrPayload = collect([
            trim((string) $employeeId->full_name),
            trim((string) ($employeeId->designation ?? '')),
            trim((string) ($employeeId->office_name ?? '')),
        ])
            ->filter(fn (string $value): bool => $value !== '')
            ->implode("\n");

        $qrFilename = 'qrcodes/qr_' . $employeeId->id . '_' . uniqid('', true) . '.png';
        $storedPath = $this->qrCodeGenerator->generate($qrPayload, $qrFilename);

        return Storage::disk('public')->path($storedPath);
    }

    protected function resolveTemplatePath(): string
    {
        $stored = Setting::get('id_card_docx_template_path');
        if ($stored) {
            $full = Storage::disk('local')->path($stored);
            if (file_exists($full)) {
                return $full;
            }
        }

        $fallback = resource_path('templates/id-card-template.docx');
        if (! file_exists($fallback)) {
            throw new \RuntimeException(
                'DOCX template not found. Upload one in Admin → Settings → ID card template, or add resources/templates/id-card-template.docx'
            );
        }

        return $fallback;
    }
}

