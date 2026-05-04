<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_id_print_status_histories', function (Blueprint $table) {
            $table->string('event_type')->default('status_updated')->after('employee_id_id');
            $table->string('field_name')->nullable()->after('event_type');
            $table->text('old_value')->nullable()->after('new_status');
            $table->text('new_value')->nullable()->after('old_value');
        });

        DB::table('employee_id_print_status_histories')
            ->whereNull('event_type')
            ->update([
                'event_type' => 'status_updated',
                'field_name' => 'print_status',
            ]);

        DB::table('employee_id_print_status_histories')
            ->whereNull('old_value')
            ->update([
                'old_value' => DB::raw('old_status'),
                'new_value' => DB::raw('new_status'),
            ]);
    }

    public function down(): void
    {
        Schema::table('employee_id_print_status_histories', function (Blueprint $table) {
            $table->dropColumn(['event_type', 'field_name', 'old_value', 'new_value']);
        });
    }
};
