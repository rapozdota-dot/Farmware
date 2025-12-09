<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     * Add indexes for better query performance
     */
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            // Index for location queries (used in FeatureEstimator)
            $table->index('location', 'idx_records_location');
            
            // Index for season queries (used frequently)
            $table->index('season', 'idx_records_season');
            
            // Composite index for location + season (most common query pattern)
            $table->index(['location', 'season'], 'idx_records_location_season');
            
            // Index for yield queries (used in filtering)
            $table->index('yield_t_ha', 'idx_records_yield');
            
            // Composite index for common WHERE clauses
            $table->index(['season', 'yield_t_ha'], 'idx_records_season_yield');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropIndex('idx_records_location');
            $table->dropIndex('idx_records_season');
            $table->dropIndex('idx_records_location_season');
            $table->dropIndex('idx_records_yield');
            $table->dropIndex('idx_records_season_yield');
        });
    }
};
