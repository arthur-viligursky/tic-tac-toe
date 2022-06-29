<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Services\ApiResponseService;
use App\Services\GameService;
use App\Services\MoveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController
{

    protected ApiResponseService $apiResponseService;
    protected MoveService $moveService;
    protected GameService $gameService;

    public function __construct(
        ApiResponseService $apiResponseService,
        GameService $gameService,
        MoveService $moveService,
    ) {
        $this->apiResponseService = $apiResponseService;
        $this->gameService = $gameService;
        $this->moveService = $moveService;
    }

    public function defaultAction(Request $request): JsonResponse
    {
        $competition = $this->getCompetition();
        if ($competition === null) {
            $options = $request->all();
            $competition = $this->gameService->startCompetition($options, Auth::user());
        }

        return response()->json($this->apiResponseService->getResponseData($competition));
    }

    protected function getCompetition(): Competition
    {
        return $this->gameService->getCompetition(Auth::user());
    }

    public function makeMoveAction(string $piece, Request $request): JsonResponse
    {
        $competition = $this->getCompetition();
        $game = $this->gameService->getLastGame($competition);
        $coordinates = $request->all();
        $result = $this->moveService->makeMove($piece, $coordinates, $game);
        if ($result !== MoveService::RESULT_OK) {
            abort($result);
        }

        return response()->json($this->apiResponseService->getResponseData($competition));
    }

    public function restartAction(): JsonResponse
    {
        $competition = $this->getCompetition();
        $this->gameService->restartGame($competition);

        return response()->json($this->apiResponseService->getResponseData($competition));
    }
}
