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
        Schema::create('invalid_sales', function (Blueprint $table) {
            $table->id();
            $table->string('store')->nullable();
            $table->string('store_code')->nullable();
            $table->string('date')->nullable();
            $table->string('order_num')->nullable();
            $table->string('receipt_num')->nullable();
            $table->string('receipt_count')->nullable();
            $table->string('balance_outstanding')->nullable();
            $table->string('delivery_date')->nullable();
            $table->string('commission_earned')->nullable();
            $table->string('product_code')->nullable();
            $table->string('head_base_included')->nullable();
            $table->string('head_base_frame_included')->nullable();
            $table->string('completion_status')->nullable();
            $table->string('product_color')->nullable();
            $table->string('direct')->nullable();
            $table->string('photo')->nullable();
            $table->string('wall_included')->nullable();
            $table->string('comments')->nullable();
            $table->string('error_column');
            $table->longText('error_message'); //This will be used to display name of column causing error and error description.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invalid_sales');
    }
};
