# Employee ID Card Generator

A Laravel/Filament-based system for generating employee ID cards with QR codes.

## Features

- ✅ Employee information management (name, designation, office)
- ✅ Profile picture upload and automatic cropping
- ✅ QR code generation for each employee with encoded data
- ✅ Automated ID card image generation with professional design
- ✅ Filament Admin Panel integration
- ✅ Download generated ID cards

## Installation

The system has been set up with the following packages installed:
- `chillerlan/php-qr-code` - QR code generation
- `intervention/image` - Image manipulation and ID card creation

## Database Setup

The `employee_ids` table has been created with the following columns:

- `id` - Primary key
- `id_number` - Unique employee ID number
- `first_name` - Employee first name
- `middle_initial` - Employee middle initial (optional)
- `last_name` - Employee last name
- `designation` - Job title/position
- `office_name` - Department/office name
- `profile_picture` - Path to uploaded profile picture
- `id_card_image` - Path to generated ID card
- `qr_code_data` - QR code content (JSON)
- `signature` - Path to signature image (optional)
- `created_at` / `updated_at` - Timestamps

## Usage

### Admin Panel
1. Log in to the Filament admin panel
2. Navigate to "Employee IDs" section
3. Click "Create New ID" to add a new employee

### Creating an ID Card

1. **Fill in employee information:**
   - ID Number (e.g., 2024807)
   - First Name
   - Middle Initial (optional)
   - Last Name
   - Designation (e.g., Provincial Governor)
   - Office Name (e.g., Office of the Provincial Governor)

2. **Upload Profile Picture:**
   - Click the file upload field
   - Select a portrait-oriented image
   - The image will be automatically cropped to a square (1:1 ratio)
   - Resized to 200x200 pixels for consistency

3. **Submit the form:**
   - The system will automatically generate an ID card on creation
   - The QR code will contain employee information in JSON format

### Generating/Regenerating ID Cards

1. Go to the employee's detail page
2. Click the "Generate ID Card" button
3. Confirm the action
4. The system will create a new ID card image
5. The image will be automatically downloaded

### Viewing Generated ID Cards

1. Click on an employee in the list
2. The ID card image will be displayed on the detail page
3. You can download the ID card using the download button

## ID Card Design

The generated ID cards feature:
- Professional header with the Davao del Sur provincial design (blue and orange)
- Employee photo (if uploaded)
- Employee name in large text
- Designation in orange highlight
- Office/department name
- Unique ID number
- QR code (bottom right) containing employee data
- 1050x637 pixels (3.5" x 2.125" @ 300dpi)

## QR Code Content

The QR code encodes the following information in JSON format:
```json
{
  "id_number": "2024807",
  "name": "JOHN DOE",
  "designation": "Provincial Governor",
  "office": "Office of the Provincial Governor"
}
```

## File Structure

```
app/
├── Services/
│   ├── QRCodeGenerator.php    - QR code generation
│   └── IDCardGenerator.php    - ID card image creation
├── Models/
│   └── EmployeeId.php         - Employee ID model
└── Filament/
    └── Resources/
        └── EmployeeIds/
            ├── EmployeeIdResource.php
            ├── Schemas/
            │   └── EmployeeIdForm.php
            ├── Tables/
            │   └── EmployeeIdsTable.php
            └── Pages/
                ├── CreateEmployeeId.php
                ├── EditEmployeeId.php
                ├── ListEmployeeIds.php
                └── ViewEmployeeId.php

database/
└── migrations/
    └── create_employee_ids_table.php

resources/
└── views/filament/resources/employee-ids/pages/
    └── view-employee-id.blade.php

storage/
└── public/
    ├── id_cards/          - Generated ID card images
    ├── profile-pictures/  - Uploaded profile photos
    └── qrcodes/          - QR code images
```

## API Services

### QRCodeGenerator Service

```php
use App\Services\QRCodeGenerator;

$generator = new QRCodeGenerator();

// Generate and save QR code
$filename = $generator->generate('data string', 'optional-filename');

// Generate base64 encoded QR code (for embedding in images)
$base64 = $generator->generateBase64('data string');
```

### IDCardGenerator Service

```php
use App\Services\IDCardGenerator;
use App\Models\EmployeeId;

$generator = new IDCardGenerator();
$employee = EmployeeId::find(1);

// Generate ID card
$filename = $generator->generate($employee);
// Returns: 'id_cards/1_abc123xyz.png'
```

## Troubleshooting

### QR Code not appearing
- Ensure the GD PHP extension is installed
- Check file permissions on `storage/app/public` directory

### Profile pictures not showing
- Verify the image file exists in storage
- Check that the file path is correctly stored in the database
- Ensure the storage disk is properly configured

### ID Card generation fails
- Check Laravel logs for specific error messages
- Verify Intervention Image library is properly installed
- Ensure PHP GD extension is enabled

## Requirements

- PHP 8.2+
- Laravel 12.0+
- Filament 5.5+
- GD PHP Extension (for image manipulation)

## Storage Locations

- **Profile Pictures:** `storage/app/public/profile-pictures/`
- **Generated ID Cards:** `storage/app/public/id_cards/`
- **Temporary QR Codes:** `storage/app/public/qrcodes/`

Make sure to run `php artisan storage:link` to create the public symlink if it doesn't exist.

## Security Notes

- File uploads are restricted to image formats only
- All files are stored in the public storage disk
- ID card images can be downloaded by authenticated users
- QR codes contain only basic employee information (no sensitive data)
