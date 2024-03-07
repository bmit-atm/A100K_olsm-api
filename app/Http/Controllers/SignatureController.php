<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class SignatureController extends Controller
{
    
    public function generate(Request $request)
    {
        try {
            // Benutzername aus dem Request abrufen
            $username = $request->input('username');

            // Hochgeladenes Bild verarbeiten
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $imagePath = $image->store('logos', 'public');

                // Lokaler Pfad des Bildes
                $localImagePath = storage_path('app/public/' . $imagePath);

                // HTML-Datei für die Outlook-Signatur generieren
                $htmlContent = "<html><body>";
                $htmlContent .= "<h1>Outlook-Signatur für $username</h1>";
                $htmlContent .= "<p>Benutzername: $username</p>";
                $htmlContent .= "<img src='" . $localImagePath . "' alt='Logo'>";
                $htmlContent .= "</body></html>";

                // Datei als Antwort senden
                return response($htmlContent)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'attachment; filename="signature.htm"');
            } else {
                return response()->json(['error' => 'No file uploaded'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
