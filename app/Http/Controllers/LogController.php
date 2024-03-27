<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log as Log;
class LogController extends Controller
{
    

    // Get all logs from the database
    public function index()
    {
        try{
            $logs = Log::orderBy('created_at', 'desc')->get();
            return response()->json($logs, 200);
        }catch(\Exception $e){
            return response()->json(['error' => 'das funktioniert nicht'], 500);
        }
    }
}
