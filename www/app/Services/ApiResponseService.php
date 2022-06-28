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

    public function getResponseData(Competition $competition): array
    {
        $game = $this->gameService->getLastGame($competition);
        $board = $this->gameService->getBoard($game);
        $score = [];
        foreach (GameService::PIECES as $piece) {
            $score[$piece] = $this->gameService->getPlayer($competition, $piece)->score;
        }
        $currentTurn = ($this->gameService->getCurrentTurn($game) ?? 'finished');
        $victory = $this->getVictory($game);

        return compact('board', 'score', 'currentTurn', 'victory');
    }

    protected function getVictory(Game $game) {
        return match ($game->status) {
            Game::STATUS_O_WON => 'o',
            Game::STATUS_X_WON => 'x',
            default => '',
        };
    }
}
