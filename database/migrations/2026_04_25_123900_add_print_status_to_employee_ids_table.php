<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_ids', function (Blueprint $table) {
            $table->string('print_status')
                ->default('in_progress')
                ->after('qr_code_data');
        });

        DB::table('employee_ids')
            ->whereNull('print_status')
            ->update(['print_status' => 'in_progress']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_ids', function (Blueprint $table) {
            $table->dropColumn('print_status');
        });
    }
};
