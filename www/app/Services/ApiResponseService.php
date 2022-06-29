<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Game;

class ApiResponseService
{
    protected GameService $gameService;

    function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function getCurrentTurnResponseData(Competition $competition): array
    {
        $game = $this->gameService->getLastGame($competition);
        $currentTurn = ($this->gameService->getCurrentTurn($game) ?? 'finished');

        return compact('currentTurn');
    }

    public function getFullResponseData(Competition $competition): array
    {
        $game = $this->gameService->getLastGame($competition);
        $board = $this->gameService->getBoard($game);
        $score = [];
        foreach (GameService::PIECES as $piece) {
            $score[$piece] = $this->gameService->getPlayer($competition, $piece)->score;
        }
        $currentTurn = ($this->gameService->getCurrentTurn($game) ?? 'finished');
        $victory = $this->gameService->getWinnerPiece($game);

        return compact('board', 'score', 'currentTurn', 'victory');
    }
}
