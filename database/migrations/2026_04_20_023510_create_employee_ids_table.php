<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_ids', function (Blueprint $table) {
            $table->id();
            $table->string('id_number')->unique();
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->string('last_name');
            $table->string('extension')->nullable();
            $table->string('designation');
            $table->string('office_name');
            $table->string('profile_picture')->nullable();
            $table->string('id_card_image')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_ids');
    }
};
