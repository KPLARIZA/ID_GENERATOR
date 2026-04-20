<?php

namespace App\Console\Commands;

use App\Models\EmployeeId;
use App\Services\IDCardGenerator;
use Illuminate\Console\Command;

class CreateTestEmployee extends Command
{
    protected $signature = 'employee:create-test';
    protected $description = 'Create a test employee and generate ID card';

    public function handle()
    {
        $this->info('Creating test employee...');

        try {
            // Use a random ID to avoid duplicates
            $randomId = '2024' . random_int(100, 999);
            
            // Create test employee
            $employee = EmployeeId::create([
                'id_number' => $randomId,
                'first_name' => 'John',
                'middle_initial' => 'D',
                'last_name' => 'Doe',
                'designation' => 'Provincial Governor',
                'office_name' => 'Office of the Provincial Governor',
                'extension' => '101',
            ]);

            $this->info("✓ Employee created: {$employee->full_name}");

            // Generate ID card
            $this->info('Generating ID card with QR code...');
            $generator = new IDCardGenerator();
            $cardPath = $generator->generate($employee);

            // Update employee with generated card path
            $employee->update(['id_card_image' => $cardPath]);

            $this->info("✓ ID Card generated: {$cardPath}");
            $this->info("✓ Access it at: http://localhost:8000/storage/{$cardPath}");
            
            $this->line('');
            $this->info('✅ Complete! Test employee ID card is ready.');
            $this->line('');
            $this->info('Employee Details:');
            $this->table(['Field', 'Value'], [
                ['ID Number', $employee->id_number],
                ['Name', $employee->full_name],
                ['Designation', $employee->designation],
                ['Office', $employee->office_name],
                ['ID Card URL', "http://localhost:8000/storage/{$cardPath}"],
            ]);

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
