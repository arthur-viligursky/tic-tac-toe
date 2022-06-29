<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Services\ApiResponseService;
use App\Services\GameService;
use App\Services\GameStartService;
use App\Services\MoveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController
{
    protected ApiResponseService $apiResponseService;
    protected GameService $gameService;
    protected GameStartService $gameStartService;
    protected MoveService $moveService;

    public function __construct(
        ApiResponseService $apiResponseService,
        GameService $gameService,
        GameStartService $gameStartService,
        MoveService $moveService,
    ) {
        $this->apiResponseService = $apiResponseService;
        $this->gameService = $gameService;
        $this->gameStartService = $gameStartService;
        $this->moveService = $moveService;
    }

    public function deleteAction(Request $request): JsonResponse
    {
        $oldCompetition = $this->getCompetition();
        $this->gameService->deleteCompetition($oldCompetition);
        $newCompetition = $this->startCompetition($request);

        return response()->json($this->apiResponseService->getCurrentTurnResponseData($newCompetition));
    }

    public function defaultAction(Request $request): JsonResponse
    {
        $competition = $this->getCompetition();
        if ($competition === null) {
            $competition = $this->startCompetition($request);
        }

        return response()->json($this->apiResponseService->getFullResponseData($competition));
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

        return response()->json($this->apiResponseService->getFullResponseData($competition));
    }

    public function restartAction(): JsonResponse
    {
        $competition = $this->getCompetition();
        $this->gameStartService->restartGame($competition);

        return response()->json($this->apiResponseService->getFullResponseData($competition));
    }

    protected function startCompetition(Request $request): Competition
    {
        $options = $request->all();

        return $this->gameStartService->startCompetition($options, Auth::user());
    }
}
