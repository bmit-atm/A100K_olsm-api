<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Log as Log;
class SignatureController extends Controller
{
    
    public function generate(Request $request)
    {
        try {
           // Gruppe aus dem Request abrufen
            $gruppe = $request->input('gruppe');

            $user = $request->input('user');

            if(is_array($gruppe)) {
                $gruppe = implode(',', $gruppe);
            }
            $name = $request->input('name');

            // Benutzername aus dem Request abrufen
            $signatureLink = $request->input('url');

            // Hochgeladene Datei verarbeiten
            if ($request->hasFile('file')) {
                $image = $request->file('file');
                $imageContent = file_get_contents($image->getPathname()); // Inhalt aus der Datei lesen

                // Überprüfen, ob der Inhalt gültig ist
                if ($imageContent === false) {
                    return response()->json(['error' => 'Failed to read file content'], 500);
                }

                // MIME-Typ der Datei abrufen
                $mimeType = $image->getMimeType();
                file_put_contents('image_content.log', $imageContent);
                // Inhalt in Base64 kodieren
                $imageBase64 = base64_encode($imageContent);
                file_put_contents('mime_type.log', $mimeType);
                
                // HTML-Datei für die Outlook-Signatur generieren
                $htmlContent = "<html><body>";
                $htmlContent .= "<h1>Outlook-Signatur für die Gruppe: {$gruppe}</h1>";
                $htmlContent .= "<h2>Name: {$name}</h2>";
                $htmlContent .= "<a target='_blank' href='{$signatureLink}'>";
                // Base64-kodierten Inhalt einfügen
                $htmlContent .= "<img src='data:{$mimeType};base64,{$imageBase64}' />";
                $htmlContent .= "</a>";
                $htmlContent .= "</body></html>";

                try{
                    // Log erstellen
                    $log = new Log;
                    $log->gruppe = $gruppe;
                    $log->name = $name;
                    $log->img = $imageBase64; // Speichere den Base64-kodierten Inhalt
                    $log->user = $user;
                    $log->save();
                } catch(\Exception $e){
                    \Log::error('error: ' . 'Das funktioniert nicht');
                    return response()->json(['error' => 'das funktioniert nicht'], 500);
                }

                // Datei als Antwort senden
                return response($htmlContent)
                    ->header('Content-Type', 'text/html');
            } else {
                return response()->json(['error' => 'No file uploaded'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
}
