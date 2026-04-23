<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificateSetting;
use Illuminate\Http\Request;

class CertificatePrintController extends Controller
{
    /**
     * Display the certificate print page with settings.
     */
    public function show(Request $request, Certificate $certificate)
    {
        // Load relations for display
        $certificate->load(['student', 'course', 'instructor']);

        // Get settings from database (most values still stored here)
        $dbSettings = CertificateSetting::current();
        // determine background image path - look for fixed upload file
        if (file_exists(public_path('config/certificate_wm.png'))) {
            $bgUrl = asset('config/certificate_wm.png');
        } else {
            // fallback to whatever may be stored in the DB (older installations)
            $bgUrl = $dbSettings->background_image_url;
        }

        $defaults = [
            'frame_color' => $dbSettings->frame_color,
            'border_width' => $dbSettings->border_width,
            'font_family' => $dbSettings->font_family,
            'background_image_url' => $bgUrl,
            'title' => $dbSettings->title,
            'signature_max_width' => $dbSettings->signature_max_width,
            'watermark_opacity' => $dbSettings->watermark_opacity,
            'custom_css' => $dbSettings->custom_css,
        ];

        // overrides from query parameters, background image no longer supported here
        $overrides = $request->only(['frame_color', 'border_width', 'font_family', 'title', 'signature_max_width', 'watermark_opacity', 'custom_css']);

        $settings = array_merge($defaults, array_filter($overrides, function ($v) { return !is_null($v) && $v !== ''; }));

        return view('certificates.print', compact('certificate', 'settings'));
    }
}
