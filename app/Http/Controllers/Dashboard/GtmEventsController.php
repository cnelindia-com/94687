<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Analytics\GoogleTagManager;
use Illuminate\Http\JsonResponse;

class GtmEventsController extends Controller
{
    public function index(): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['events' => []]);
        }

        return response()->json([
            'events' => GoogleTagManager::pull((int) auth()->id()),
        ]);
    }
}
