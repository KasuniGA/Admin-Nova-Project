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
        Schema::table('tracked_prices', function (Blueprint $table) {
            // Add composite index for product_id and tracked_at for better query performance
            $table->index(['product_id', 'tracked_at'], 'tracked_prices_product_date_index');
            
            // Add index for recent price queries
            $table->index('tracked_at', 'tracked_prices_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracked_prices', function (Blueprint $table) {
            $table->dropIndex('tracked_prices_product_date_index');
            $table->dropIndex('tracked_prices_date_index');
        });
    }
};
