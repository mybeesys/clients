<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Frame ancestors (tenant hosts allowed to embed /subscribe)
    |--------------------------------------------------------------------------
    |
    | Space-separated CSP frame-ancestors sources. Defaults cover MyBee
    | tenant subdomains. Override via SUBSCRIBE_FRAME_ANCESTORS.
    |
    */
    'frame_ancestors' => env(
        'SUBSCRIBE_FRAME_ANCESTORS',
        'https://*.mybeesystem.net https://*.mybeesystem.com https://*.mybeesys.com'
    ),

];
