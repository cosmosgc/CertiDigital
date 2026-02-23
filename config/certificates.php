<?php

return [
    /* Visual frame color (CSS color) */
    'frame_color' => '#1f2937',

    /* Border width of the certificate frame */
    'border_width' => '8px',

    /* Font family used in printed certificate */
    'font_family' => "'Georgia', 'Times New Roman', serif",

    /*
     * Background image used in certificates.  We now support an uploaded file
     * called `certificate_wm.png` stored in `public/config`.  If the file
     * exists it will be returned by the print controller automatically.  The
     * database column is no longer required and may be left null for
     * backwards‑compatibility.
     */
    'background_image_url' => null,

    /* Default title shown on the certificate */
    'title' => 'Certificate of Completion',

    /* Max width for instructor signature image */
    'signature_max_width' => '220px',

    /* Opacity for watermark / background overlay */
    'watermark_opacity' => 0.06,
];
