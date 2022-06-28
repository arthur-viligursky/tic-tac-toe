<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\NewCompetitionRequest;

class GameController
{
    public function defaultAction(
        ApiResponseService $apiResponseService,
        GameService $gameService,
        NewCompetitionRequest $request
    ): JsonResponse {
        $user = Auth::user();
        $competition = $gameService->getCompetition($user);
        if ($competition === null) {
            $options = $request->validated();
            $competition = $gameService->startCompetition($options, $user);
        }

        return response()->json($apiResponseService->getResponseData($competition));
    }
}
