<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_id_print_status_histories', function (Blueprint $table): void {
            $table->dropForeign(['employee_id_id']);
        });

        DB::statement('ALTER TABLE employee_id_print_status_histories MODIFY employee_id_id BIGINT UNSIGNED NULL');

        Schema::table('employee_id_print_status_histories', function (Blueprint $table): void {
            $table->foreign('employee_id_id')
                ->references('id')
                ->on('employee_ids')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_id_print_status_histories', function (Blueprint $table): void {
            $table->dropForeign(['employee_id_id']);
        });

        DB::statement('ALTER TABLE employee_id_print_status_histories MODIFY employee_id_id BIGINT UNSIGNED NOT NULL');

        Schema::table('employee_id_print_status_histories', function (Blueprint $table): void {
            $table->foreign('employee_id_id')
                ->references('id')
                ->on('employee_ids')
                ->cascadeOnDelete();
        });
    }
};
