# Troubleshooting & FAQ

## Common Issues & Solutions

### 1. ID Cards Not Generating

**Problem**: When creating or editing an employee, no ID card image is generated.

**Solutions**:

a) **Check Laravel logs**:
```bash
tail -f storage/logs/laravel.log
```

b) **Verify GD extension** is installed:
```bash
php -i | grep -A 10 "GD Support"
```
Should show `GD Support => enabled`

c) **Check storage permissions**:
```bash
# Ensure storage directory is writable
chmod -R 755 storage/
chmod -R 755 storage/app/public/
```

d) **Verify Intervention Image is installed**:
```bash
composer show | grep intervention
```
Should show `intervention/image` in the list

e) **Try manual generation** in Tinker:
```bash
php artisan tinker

>>> $emp = App\Models\EmployeeId::first();
>>> $gen = new App\Services\IDCardGenerator();
>>> $gen->generate($emp);
```

### 2. Profile Pictures Not Showing on ID Card

**Problem**: Profile picture is uploaded but doesn't appear on the generated ID card.

**Solutions**:

a) **Check if file was uploaded**:
```bash
ls storage/app/public/profile-pictures/
```

b) **Verify file storage path** in database:
```bash
php artisan tinker
>>> App\Models\EmployeeId::first()->profile_picture;
# Should show a path like 'profile-pictures/uuid.png'
```

c) **Check file size and format**:
```bash
file storage/app/public/profile-pictures/filename.jpg
# Should show image/jpeg or image/png
```

d) **Permissions on storage**:
```bash
chmod 644 storage/app/public/profile-pictures/*
```

### 3. QR Code Not Appearing

**Problem**: ID card generates but QR code is missing.

**Solutions**:

a) **Check temporary files**:
```bash
ls storage/app/public/qrcodes/
```

b) **Verify chillerlan/php-qr-code**:
```bash
composer show | grep qr-code
```

c) **Test QR generation directly**:
```bash
php artisan tinker

>>> $gen = new App\Services\QRCodeGenerator();
>>> $gen->generate('test data', 'test_qr.png');
# Check if file created in storage/app/public/qrcodes/
```

### 4. Storage Link Not Working

**Problem**: Images show as broken links or 404 errors.

**Solutions**:

a) **Check if symlink exists**:
```bash
ls -la public/storage
```
Should show a link to `storage/app/public`

b) **Recreate the link**:
```bash
php artisan storage:link
```

c) **On Windows with permissions issues**:
```bash
# Remove old link and recreate
rmdir public\storage
php artisan storage:link
```

d) **For production**, use absolute paths in configuration:
```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'path' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

### 5. File Upload Validation Failing

**Problem**: "The file field must be an image" error when uploading.

**Solutions**:

a) **Check file format**:
- Supported: JPG, JPEG, PNG, GIF, WebP
- Not supported: BMP, TIFF, SVG

b) **Verify file mimetype**:
```bash
file your-image.jpg
```

c) **Check file size** - must be under 5MB (configurable in form)

d) **Resize before upload** (recommended):
- Use an image editor to resize to 400×400px
- Save as JPG (better compression than PNG)

### 6. Database Migration Errors

**Problem**: "Table 'employee_ids' doesn't exist" when creating first record.

**Solutions**:

a) **Run migrations**:
```bash
php artisan migrate
```

b) **Check migration status**:
```bash
php artisan migrate:status
```

c) **Refresh database** (development only):
```bash
php artisan migrate:refresh --seed
```

### 7. Filament Admin Panel Access Issues

**Problem**: Can't access admin panel or seeing authentication errors.

**Solutions**:

a) **Check if admin user exists**:
```bash
php artisan tinker
>>> App\Models\User::count();
```

b) **Create admin user if needed**:
```bash
php artisan make:filament-user
# Follow prompts to create admin account
```

c) **Clear cache**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 8. Memory Limit Issues

**Problem**: "Allowed memory size exhausted" when generating large IDs.

**Solutions**:

a) **Increase PHP memory limit** in `php.ini`:
```ini
memory_limit = 256M  ; Or higher if needed
```

b) **For .htaccess** (Apache):
```apache
php_value memory_limit 256M
```

c) **In Laravel** (temporary):
```php
ini_set('memory_limit', '256M');
```

### 9. Slow ID Card Generation

**Problem**: ID card takes a long time to generate.

**Solutions**:

a) **Optimize profile picture**:
- Reduce image size to 100KB or less
- Use JPG instead of PNG
- Resize to 400×400px before upload

b) **Check system resources**:
```bash
# Linux/Mac
top

# Windows
tasklist
```

c) **Generate asynchronously** for bulk operations:
```php
// Queue the generation
dispatch(new GenerateIDCard($employee));
```

d) **Pre-generate QR codes**:
```php
$employee->qr_code_data = json_encode([...]);
$employee->save();
```

### 10. Email Notifications Not Working

**Problem**: Can't receive ID card via email (if implemented).

**Solutions**:

a) **Check mail configuration** in `.env`:
```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

b) **Test email** from Tinker:
```bash
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com'));
```

## Performance Optimization

### Bulk ID Generation

For creating many IDs at once:

```php
// In a command or scheduled task
$employees = EmployeeId::whereNull('id_card_image')->get();

foreach ($employees as $employee) {
    try {
        $generator = new IDCardGenerator();
        $filename = $generator->generate($employee);
        $employee->update(['id_card_image' => $filename]);
        echo "Generated ID for {$employee->full_name}\n";
    } catch (\Exception $e) {
        echo "Failed for {$employee->full_name}: {$e->getMessage()}\n";
    }
}
```

### Database Optimization

```bash
# Create indexes for faster queries
php artisan tinker
>>> DB::statement("ALTER TABLE employee_ids ADD INDEX idx_id_number (id_number)");
>>> DB::statement("ALTER TABLE employee_ids ADD INDEX idx_office (office_name)");
```

## Browser Compatibility

- **Chrome/Edge**: ✅ Full support
- **Firefox**: ✅ Full support
- **Safari**: ✅ Full support
- **IE11**: ❌ Not supported

## Mobile Device Issues

**Problem**: File upload not working on mobile.

**Solutions**:

a) **Check storage permissions**:
```bash
chmod -R 755 storage/
```

b) **Clear browser cache**:
- Chrome: Settings > Clear browsing data
- Safari: Settings > Clear History and Website Data

c) **Try different browser** to isolate issue

## Debugging Mode

Enable detailed error reporting:

```php
// In .env file
APP_DEBUG=true
```

Then check `storage/logs/laravel.log` for detailed stack traces.

## Database Backup

Before major operations:

```bash
# MySQL
mysqldump -u username -p database_name > backup.sql

# SQLite
cp database/database.sqlite database/database.sqlite.backup
```

## Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Reset database
php artisan migrate:reset

# Fresh start with seeds
php artisan migrate:fresh --seed

# Check file permissions
ls -la storage/app/public/

# Verify package installation
composer check-platform-reqs
```

## Getting Help

If issues persist:

1. **Check Laravel docs**: https://laravel.com/docs
2. **Filament docs**: https://filamentphp.com/docs
3. **Check logs**: `storage/logs/laravel.log`
4. **Enable debug mode**: `APP_DEBUG=true` in `.env`

## Report Issues

When reporting problems, include:
- Error message from logs
- PHP version: `php -v`
- Laravel version: `php artisan --version`
- Steps to reproduce
- Screenshot if applicable
