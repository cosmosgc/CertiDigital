<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateSetting extends Model
{
    protected $table = 'certificate_settings';

    protected $fillable = [
        'frame_color',
        'border_width',
        'font_family',
        'background_image_url',
        'title',
        'signature_max_width',
        'watermark_opacity',
        'custom_css',
    ];

    protected $casts = [
        'watermark_opacity' => 'decimal:2',
    ];

    /**
     * Get the first (and usually only) settings record
     */
    public static function current()
    {
        return self::firstOrCreate([]);
    }
}
