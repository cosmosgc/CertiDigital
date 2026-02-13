<?php

namespace App\Http\Controllers;

use App\Models\CertificateSetting;
use Illuminate\Http\Request;

class CertificateSettingController extends Controller
{
    /**
     * Show the certificate settings form
     */
    public function edit()
    {
        $settings = CertificateSetting::current();
        return view('certificate-settings.edit', compact('settings'));
    }

    /**
     * Update the certificate settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'frame_color' => 'nullable|string|max:7',
            'border_width' => 'nullable|string|max:20',
            'font_family' => 'nullable|string|max:255',
            'background_image_url' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'signature_max_width' => 'nullable|string|max:20',
            'watermark_opacity' => 'nullable|numeric|min:0|max:1',
        ]);

        // Apply default values if fields are empty
        $defaults = [
            'frame_color' => '#1f2937',
            'border_width' => '8px',
            'font_family' => "'Georgia', 'Times New Roman', serif",
            'title' => 'Certificate of Completion',
            'signature_max_width' => '220px',
            'watermark_opacity' => 0.06,
        ];

        foreach ($defaults as $field => $default) {
            if (empty($validated[$field])) {
                $validated[$field] = $default;
            }
        }

        $settings = CertificateSetting::current();
        $settings->update($validated);

        return redirect()->back()->with('success', 'Certificate settings updated successfully.');
    }
}
