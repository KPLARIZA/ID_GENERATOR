# Quick Start Guide - Employee ID Generator

## Setup Complete! ✅

Your Employee ID Card Generator is ready to use. Here's how to get started:

### 1. Start the Development Server

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev
```

The admin panel will be available at: `http://localhost:8000/admin`

### 2. Access the Admin Panel

1. Log in with your admin credentials
2. Look for "Employee IDs" in the sidebar navigation
3. You're ready to create ID cards!

### 3. Creating Your First ID Card

1. Click "Create New ID"
2. Fill in the form:
   - **ID Number**: Unique identifier (e.g., 2024807)
   - **First Name**: Employee's first name
   - **Middle Initial**: Optional middle initial
   - **Last Name**: Employee's last name
   - **Designation**: Job title (e.g., Provincial Governor)
   - **Office Name**: Department name

3. Upload a profile picture:
   - Click the file upload area
   - Select a portrait-oriented image
   - The system will automatically crop it to square (1:1)

4. Click "Create"
   - The system will automatically generate an ID card
   - The ID card image is saved to storage

### 4. Viewing the Generated ID Card

1. Click on the employee record in the list
2. You'll see:
   - Employee information
   - Profile picture preview
   - Generated ID card image
   - Download button

### 5. Regenerating an ID Card

1. Go to the employee's edit page
2. Click the "Generate ID Card" button
3. Confirm the action
4. A new ID card will be generated and downloaded automatically

## What Gets Generated?

### ID Card Features:
- 📸 Professional design with provincial branding
- 🎨 Blue and orange color scheme (Davao del Sur style)
- 👤 Employee profile picture (if uploaded)
- 🆔 Unique ID number
- 💼 Designation/Position
- 🏢 Office/Department
- 📱 QR Code containing employee data

### QR Code Contains:
```json
{
  "id_number": "2024807",
  "name": "JOHN DOE",
  "designation": "Provincial Governor",
  "office": "Office of the Provincial Governor"
}
```

Scan with any QR code reader to extract employee information!

## File Storage

Your files are automatically saved to:

- **Profile Pictures**: `storage/app/public/profile-pictures/`
- **ID Cards**: `storage/app/public/id_cards/`
- **QR Codes**: `storage/app/public/qrcodes/`

All files are accessible via: `http://localhost:8000/storage/...`

## Customization Tips

### Changing ID Card Design
Edit `app/Services/IDCardGenerator.php`:

```php
// Colors (hex format)
#003d7a  // Dark blue
#ff9933  // Orange

// Dimensions
$width = 1050;   // Pixels
$height = 637;   // Pixels

// Text positions and sizes in addEmployeeInfo() method
```

### Modifying QR Code Content
Update the JSON data in `IDCardGenerator.php`:

```php
$qrData = json_encode([
    'id_number' => $employeeId->id_number,
    'name' => $employeeId->full_name,
    'designation' => $employeeId->designation,
    'office' => $employeeId->office_name,
    // Add more fields as needed
]);
```

## Troubleshooting

### ID Cards not generating?
- Check `storage/logs/laravel.log` for errors
- Ensure GD extension is enabled: `php -m | grep -i gd`
- Verify storage link exists: `ls -la public/storage`

### Profile pictures not showing?
- Check file was uploaded: `ls storage/app/public/profile-pictures/`
- Verify permissions: `chmod -R 755 storage/`

### Storage link not working?
```bash
# Recreate the link
php artisan storage:link
```

## Database Access

View employee records directly:

```php
php artisan tinker

>>> App\Models\EmployeeId::all();
>>> $employee = App\Models\EmployeeId::first();
>>> $employee->full_name;
```

## Next Steps

1. ✅ Create a few test employees
2. ✅ Download generated ID cards
3. ✅ Try scanning QR codes with your phone
4. ✅ Customize the design to your needs
5. ✅ Set up proper user authentication for admin panel

## Support

For detailed documentation, see: `ID_GENERATOR_README.md`

---

**Happy ID card generating! 🎉**
