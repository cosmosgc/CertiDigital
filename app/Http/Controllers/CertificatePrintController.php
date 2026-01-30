<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificatePrintController extends Controller
{
    public function show(Request $request, Certificate $certificate)
    {
        // Load relations for display
        $certificate->load(['student', 'course', 'instructor']);

        // Default settings from config, allow query param overrides
        $defaults = config('certificates', [
            'frame_color' => '#1f2937',
            'border_width' => '8px',
            'font_family' => "'Georgia', 'Times New Roman', serif",
            'background_image_url' => null,
            'title' => 'Certificate of Completion',
            'signature_max_width' => '220px',
            'watermark_opacity' => 0.06,
        ]);

        $overrides = $request->only(['frame_color', 'border_width', 'font_family', 'background_image_url', 'title', 'signature_max_width', 'watermark_opacity']);

        $settings = array_merge($defaults, array_filter($overrides, function ($v) { return !is_null($v) && $v !== ''; }));

        return view('certificates.print', compact('certificate', 'settings'));
    }
}
