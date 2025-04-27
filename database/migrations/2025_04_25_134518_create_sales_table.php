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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('store');
            $table->string('store_code');
            $table->date('date');
            $table->integer('order_num');
            $table->integer('receipt_num');
            $table->integer('receipt_count');
            $table->integer('balance_outstanding');
            $table->date('delivery_date')->nullable();
            $table->enum('commission_earned', ['YES', 'NO', 'CANCELED'])->default('NO');
            $table->string('product_code');
            $table->enum('head_base_included', ['N/A', 'NO ENTRY', 'X', 'CANCELED'])->default('N/A');
            $table->enum('head_base_frame_included', ['N/A', 'NO ENTRY', 'X', 'CANCELED'])->default('N/A');
            $table->enum('completion_status', ['N/A', 'NO ENTRY', 'X', 'CANCELED'])->default('N/A');
            $table->string('product_color');
            $table->enum('direct', ['NO', 'NO ENTRY', 'YES', 'CANCELED'])->default('NO');
            $table->enum('photo', ['G', 'P', 'NO ENTRY', 'NO', 'CANCELED'])->default('NO');
            $table->enum('wall_included', ['YES', 'NO', 'NO ENTRY', 'CANCELED'])->default('NO');
            $table->longText('comments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
