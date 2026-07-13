<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PowerBiController extends Controller
{
    public function dashboard(Request $request)
    {
        $embedUrl = config('services.powerbi.embed_url') ?? env('POWERBI_EMBED_URL', null);

        // si tienes embedUrl público lo pasamos; si no, la vista intentará obtener token vía AJAX
        return response()->view('powerbi.dashboard', ['embedUrl' => $embedUrl])
            ->header('Content-Security-Policy', "frame-ancestors 'self' https://app.powerbi.com")
            ->header('X-Frame-Options', 'ALLOW-FROM https://app.powerbi.com');
    }
}
