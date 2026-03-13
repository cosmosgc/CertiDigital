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
        Schema::table('certificate_settings', function (Blueprint $table) {
            if (Schema::hasColumn('certificate_settings', 'background_image_url')) {
                $table->dropColumn('background_image_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('certificate_settings', 'background_image_url')) {
                $table->string('background_image_url')->nullable();
            }
        });
    }
};
