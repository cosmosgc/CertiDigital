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
        Schema::create('certificate_settings', function (Blueprint $table) {
            $table->id();
            $table->string('frame_color')->default('#1f2937');
            $table->string('border_width')->default('8px');
            $table->string('font_family')->default("'Georgia', 'Times New Roman', serif");
            $table->string('background_image_url')->nullable();
            $table->string('title')->default('Certificate of Completion');
            $table->string('signature_max_width')->default('220px');
            $table->decimal('watermark_opacity', 3, 2)->default(0.06);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_settings');
    }
};
