<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE reading_plans
            MODIFY status ENUM(
                'planning',
                'reading',
                'completed',
                'expired'
            ) NOT NULL DEFAULT 'planning'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE reading_plans
            MODIFY status ENUM(
                'planning',
                'reading',
                'completed'
            ) NOT NULL DEFAULT 'planning'
        ");
    }
};
