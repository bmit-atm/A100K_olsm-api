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

    // destroy a log from the database
    public function destroy($id)
    {
        try{
            $log = Log::find($id);
            if ($log) {
                $log->delete();
                return response()->json(['success' => 'Log deleted'], 200);
            } else {
                return response()->json(['error' => 'Log not found'], 404);
            }
        }catch(\Exception $e){
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}
