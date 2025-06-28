<?php

return [
    'paths' => ['api/*', '*'], // <-- permitimos acceso a cualquier ruta (temporal para desarrollo)
    'allowed_origins' => ['*'],  // <-- permitimos acceso a cualquier ruta (temporal para desarrollo)

    // 'paths' => ['api/*', '*'],   // <-- permitimos acceso a cualquier ruta (temporal para desarrollo)
    // 'paths' => ['/*', '*'],   // <-- permitimos acceso a cualquier ruta (temporal para desarrollo)

    'allowed_methods' => ['*'],

    // 'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
