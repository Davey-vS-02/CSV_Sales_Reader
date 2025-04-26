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
        Schema::create('csv_processing_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('csv_file_path');
            $table->integer('valid_row_count');
            $table->integer('invalid_row_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csv_processing_jobs');
    }
};
