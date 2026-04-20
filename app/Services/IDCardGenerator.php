<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use App\Models\EmployeeId;

class IDCardGenerator
{
    protected ImageManager $imageManager;
    protected QRCodeGenerator $qrCodeGenerator;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->qrCodeGenerator = new QRCodeGenerator();
    }

    /**
     * Generate ID card image
     */
    public function generate(EmployeeId $employeeId): string
    {
        // Set up canvas dimensions (standard ID card is 3.5" x 2.125" at 300dpi)
        $width = 1050;
        $height = 637;

        // Create base image with white background
        $image = $this->imageManager->create($width, $height)
            ->fill('ffffff');

        // Add simple design
        $this->addDesign($image, $width, $height);

        // Add employee information text
        $this->addEmployeeInfo($image, $employeeId, $width, $height);

        // Add QR code if possible
        $this->addQRCode($image, $employeeId, $width, $height);

        // Save ID card image
        $filename = 'id_cards/' . $employeeId->id . '_' . uniqid() . '.png';
        Storage::disk('public')->put($filename, $image->toPng());

        return $filename;
    }

    /**
     * Add simple design elements
     */
    protected function addDesign($image, $width, $height): void
    {
        // This is a simplified design - just add colored background areas using fill
        // Intervention Image 3.x doesn't have the same drawing API as 2.x
    }

    /**
     * Add employee information text to the card
     */
    protected function addEmployeeInfo($image, EmployeeId $employeeId, $width, $height): void
    {
        $startX = 50;
        $startY = 80;
        $lineHeight = 50;

        // ID Number
        $image->text(
            'ID: ' . $employeeId->id_number,
            $startX,
            $startY,
            function ($text) {
                $text->size(16);
                $text->color('#003d7a');
            }
        );

        // Full Name
        $image->text(
            strtoupper($employeeId->full_name),
            $startX,
            $startY + $lineHeight,
            function ($text) {
                $text->size(18);
                $text->color('#003d7a');
            }
        );

        // Designation
        $image->text(
            'Designation: ' . $employeeId->designation,
            $startX,
            $startY + ($lineHeight * 2),
            function ($text) {
                $text->size(12);
                $text->color('#666');
            }
        );

        // Office
        $image->text(
            'Office: ' . $employeeId->office_name,
            $startX,
            $startY + ($lineHeight * 3),
            function ($text) {
                $text->size(11);
                $text->color('#666');
            }
        );
    }

    /**
     * Add QR code to the ID card
     */
    protected function addQRCode($image, EmployeeId $employeeId, $width, $height): void
    {
        try {
            // Prepare QR code data
            $qrData = json_encode([
                'id_number' => $employeeId->id_number,
                'name' => $employeeId->full_name,
                'designation' => $employeeId->designation,
                'office' => $employeeId->office_name,
            ]);

            // Generate QR code file
            $qrFilename = 'temp_qr_' . uniqid() . '.png';
            $this->qrCodeGenerator->generate($qrData, $qrFilename);
            
            $qrPath = Storage::disk('public')->path($qrFilename);
            if (file_exists($qrPath)) {
                $qrImage = $this->imageManager->read($qrPath);
                
                // Resize QR code
                $qrSize = 120;
                $qrImage = $qrImage->scale(width: $qrSize, height: $qrSize);

                // Add QR code to bottom right
                $image->place(
                    $qrImage,
                    'bottom-right',
                    20,
                    20
                );
                
                // Clean up temporary file
                @unlink($qrPath);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('QR Code generation failed: ' . $e->getMessage());
        }
    }
}

