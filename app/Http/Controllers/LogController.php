<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(){
        $recentLogs = Log::latest()->take(10)->get();

        if($recentLogs->count() > 0){
            return response()->json([
                "data" => $recentLogs
            ], 200);
        } else {
            return response()->json([
                "message" => "Logs not found"
            ], 404);
        }
    }
}
