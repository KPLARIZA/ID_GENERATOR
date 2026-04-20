<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class QRCodeGenerator
{
    /**
     * Generate QR code for employee data
     */
    public function generate(string $data, string $filename = null): string
    {
        $options = new QROptions([
            'version'      => QRCode::VERSION_AUTO,
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_L,
            'scale'        => 5,
            'imageBase64'  => false,
        ]);

        $qrCode = new QRCode($options);
        $qrImage = $qrCode->render($data);

        if (!$filename) {
            $filename = 'qrcodes/' . uniqid('qr_') . '.png';
        }

        // Save QR code to storage
        Storage::disk('public')->put($filename, $qrImage);

        return $filename;
    }

    /**
     * Generate QR code as base64 string for embedding in images
     */
    public function generateBase64(string $data): string
    {
        $options = new QROptions([
            'version'      => QRCode::VERSION_AUTO,
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_L,
            'scale'        => 5,
            'imageBase64'  => true,
        ]);

        $qrCode = new QRCode($options);
        return $qrCode->render($data);
    }
}
