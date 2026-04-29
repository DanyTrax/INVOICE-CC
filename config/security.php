<?php

/**
 * Cabeceras HTTP de endurecimiento. La CSP por defecto cubre los CDN usados en
 * login, legales, admin (Tailwind, jsDelivr, cdnjs, fuentes) y estilos/scripts en línea
 * que aún existen en las plantillas.
 *
 * Para desactivar solo la CSP en un entorno: SECURITY_CSP=
 */
$cspDefault = implode(' ', [
    "default-src 'self'",
    "base-uri 'self'",
    "form-action 'self'",
    "frame-ancestors 'none'",
    "object-src 'none'",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net",
    "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
    "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com",
    "img-src 'self' data: https: blob:",
    "connect-src 'self' https://cdn.jsdelivr.net https://www.googleapis.com",
    "frame-src 'self' https:",
    "worker-src 'self' blob:",
]);

return [

    'csp' => env('SECURITY_CSP', $cspDefault),

    'hsts' => filter_var(env('SECURITY_HSTS', false), FILTER_VALIDATE_BOOLEAN),

];
