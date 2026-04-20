# ID Card Design Customization Guide

Based on the Davao del Sur Provincial ID template you provided, here's how to customize the ID card design further.

## Current Design

The system generates ID cards with:
- Dimensions: 1050 × 637 pixels (3.5" × 2.125" @ 300dpi)
- Blue header with orange accents
- Professional layout suitable for government IDs

## Matching Your Template

The template shows:
- **Top Section**: 
  - Provincial seal/logo (left)
  - "REPUBLIC OF THE PHILIPPINES" header
  - "PROVINCE OF DAVAO DEL SUR"
  - "MATTI, DIGOS CITY"

- **Center Section**:
  - Large circle background design
  - Employee name in bold
  - Designation below name
  - Office name

- **Bottom Section**:
  - Signature area
  - ID number in large text
  - QR code (bottom right)

## Customization Steps

### 1. Add Logo/Seal

To add the provincial seal image:

```php
// In app/Services/IDCardGenerator.php, addSeal() method

protected function addSeal($image): void
{
    try {
        $sealPath = storage_path('app/seal.png');
        if (file_exists($sealPath)) {
            $seal = $this->imageManager->read($sealPath);
            // Resize to fit
            $seal = $seal->scaleDown(60, 60);
            // Place on image
            $image->place($seal, 'left', 20, 20);
        }
    } catch (\Exception $e) {
        \Log::error('Seal placement failed: ' . $e->getMessage());
    }
}
```

**To use:**
1. Place your seal image at `storage/app/seal.png`
2. The code will automatically incorporate it

### 2. Add Header Text

Add provincial information header:

```php
// Add to addEmployeeInfo() method

protected function addEmployeeInfo($image, EmployeeId $employeeId, $width, $height): void
{
    // Add header text
    $image->text(
        'REPUBLIC OF THE PHILIPPINES',
        intval($width / 2),
        10,
        function ($text) {
            $text->size(9);
            $text->color('#ffffff');
            $text->align('center');
        }
    );

    $image->text(
        'PROVINCE OF DAVAO DEL SUR',
        intval($width / 2),
        20,
        function ($text) {
            $text->size(11);
            $text->weight(700);
            $text->color('#ff9933');
            $text->align('center');
        }
    );

    $image->text(
        'MATTI, DIGOS CITY',
        intval($width / 2),
        32,
        function ($text) {
            $text->size(9);
            $text->color('#ffffff');
            $text->align('center');
        }
    );

    // ... rest of employee info ...
}
```

### 3. Add Signature Area

Add signature field to form:

```php
// app/Filament/Resources/EmployeeIds/Schemas/EmployeeIdForm.php

Section::make('Authorized Representative Signature')
    ->description('Upload signature of authorizing officer')
    ->schema([
        FileUpload::make('signature')
            ->label('Signature')
            ->image()
            ->directory('signatures')
            ->columnSpan(1),
    ])
```

Display signature on ID card:

```php
// Add to IDCardGenerator.generate() method

if ($employeeId->signature && Storage::disk('public')->exists($employeeId->signature)) {
    $this->addSignature($image, $employeeId->signature, $width, $height);
}

// Add this method
protected function addSignature($image, $signaturePath, $width, $height): void
{
    try {
        $signature = $this->imageManager->read(Storage::disk('public')->get($signaturePath));
        $signature = $signature->scaleDown(100, 40);
        $image->place($signature, 'bottom-left', 30, $height - 50);
    } catch (\Exception $e) {
        \Log::error('Signature addition failed: ' . $e->getMessage());
    }
}
```

### 4. Enhance Background Design

Create more sophisticated wave patterns:

```php
// Replace addOrangeWaves() with enhanced version

protected function addOrangeWaves($image, $width, $height): void
{
    // Top right circle
    $image->drawEllipse(
        intval($width * 0.75),
        intval($height * 0.15),
        200, 200,
        function ($draw) {
            $draw->background('rgba(255, 153, 51, 0.1)');
            $draw->border('#ff9933', 2);
        }
    );

    // Bottom left accent
    $image->drawRectangle(
        0, intval($height * 0.8),
        intval($width * 0.3), $height,
        function ($draw) {
            $draw->background('rgba(0, 61, 122, 0.05)');
        }
    );
}
```

### 5. Color Scheme Options

Predefined color schemes for different departments:

```php
// app/Services/IDCardGenerator.php

public function generate(EmployeeId $employeeId, string $theme = 'default'): string
{
    $colors = $this->getThemeColors($theme);
    
    // Use in design:
    $image->drawRectangle(
        0, 0, $width, intval($height * 0.25),
        function ($draw) use ($colors) {
            $draw->background($colors['primary']);
        }
    );
}

private function getThemeColors(string $theme): array
{
    $themes = [
        'default' => [
            'primary' => '#003d7a',
            'accent' => '#ff9933',
        ],
        'green' => [
            'primary' => '#1b5e20',
            'accent' => '#43a047',
        ],
        'red' => [
            'primary' => '#b71c1c',
            'accent' => '#e53935',
        ],
    ];
    
    return $themes[$theme] ?? $themes['default'];
}
```

## Advanced Customization

### Custom Font Support

Download fonts and place in `storage/fonts/`:

```php
// Use custom fonts in text rendering

$image->text(
    $text,
    $x, $y,
    function ($text) {
        $text->fontPath(storage_path('fonts/Arial.ttf'));
        $text->size(12);
    }
);
```

Recommended fonts:
- `Arial.ttf` - Professional sans-serif
- `TimesNewRoman.ttf` - Formal serif

### Add Watermark

```php
protected function addWatermark($image, $width, $height): void
{
    $image->text(
        'DAVAO DEL SUR',
        intval($width / 2),
        intval($height / 2),
        function ($text) {
            $text->size(60);
            $text->color('rgba(200, 200, 200, 0.1)');
            $text->align('center');
            $text->angle(45);
        }
    );
}
```

### Barcode Support

Add barcode in addition to QR code:

```bash
composer require picqer/php-barcode-generator
```

```php
use Picqer\Barcode\BarcodeGeneratorPNG;

protected function addBarcode($image, EmployeeId $employeeId, $width, $height): void
{
    $generator = new BarcodeGeneratorPNG();
    $barcode = $generator->getBarcode($employeeId->id_number, BarcodeGeneratorPNG::TYPE_CODE_128);
    
    $barcodeImage = $this->imageManager->read($barcode);
    $barcodeImage = $barcodeImage->scaleDown(150, 40);
    $image->place($barcodeImage, 'bottom', intval(($width - 150) / 2), intval($height - 60));
}
```

## Testing Customizations

1. Modify the generator code
2. Edit an existing employee record
3. Click "Generate ID Card" to create with new design
4. The old version is saved; new version replaces it

## Backup Original Design

Before major changes, create a backup:

```bash
cp app/Services/IDCardGenerator.php app/Services/IDCardGenerator.php.backup
```

## Performance Tips

- Pre-generate QR codes if you have many employees
- Cache seal/logo images
- Consider async generation for bulk operations
- Optimize image sizes before upload (max 2MB recommended)

## For Full Template Match

If you need exact matching with your template:

1. **Get dimensions** of template elements
2. **Extract colors** using color picker tool
3. **Position elements** relative to card dimensions
4. **Test print quality** at actual size

```php
// Print quality settings
$image = $this->imageManager->create($width, $height)
    ->fill('ffffff')
    ->quality(95);  // High quality for printing
```

For professional print shops, use 300 DPI images and save as PDF instead of PNG.

---

**Need more customization?** Check the Intervention Image documentation:
- https://image.intervention.io/
- https://www.php-qr-code.com/
