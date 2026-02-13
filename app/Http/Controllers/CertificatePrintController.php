<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateSetting;
use Illuminate\Http\Request;

class CertificatePrintController extends Controller
{
    public function show(Request $request, Certificate $certificate)
    {
        // Load relations for display
        $certificate->load(['student', 'course', 'instructor']);

        // Get settings from database
        $dbSettings = CertificateSetting::current();
        $defaults = [
            'frame_color' => $dbSettings->frame_color,
            'border_width' => $dbSettings->border_width,
            'font_family' => $dbSettings->font_family,
            'background_image_url' => $dbSettings->background_image_url,
            'title' => $dbSettings->title,
            'signature_max_width' => $dbSettings->signature_max_width,
            'watermark_opacity' => $dbSettings->watermark_opacity,
        ];

        $overrides = $request->only(['frame_color', 'border_width', 'font_family', 'background_image_url', 'title', 'signature_max_width', 'watermark_opacity']);

        $settings = array_merge($defaults, array_filter($overrides, function ($v) { return !is_null($v) && $v !== ''; }));

        return view('certificates.print', compact('certificate', 'settings'));
    }
}
