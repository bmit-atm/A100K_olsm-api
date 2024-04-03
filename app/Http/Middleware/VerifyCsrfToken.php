<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login', // Beispiel: URI, für den CSRF-Schutz deaktiviert werden soll
        'logout', // Weitere URIs, für die CSRF-Schutz deaktiviert werden soll, falls erforderlich
    ];
}
