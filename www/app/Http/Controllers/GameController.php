<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController
{
    public function defaultAction(
        ApiResponseService $apiResponseService,
        GameService $gameService,
        Request $request
    ): JsonResponse {
        $user = Auth::user();
        $competition = $gameService->getCompetition($user);
        if ($competition === null) {
            $options = $request->all();
            $competition = $gameService->startCompetition($options, $user);
        }

        return response()->json($apiResponseService->getResponseData($competition));
    }
}
