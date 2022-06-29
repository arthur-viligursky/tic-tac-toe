<?php

namespace App\Services;

use App\Models\Game;

class ScoreService
{
    protected GameService $gameService;

    function __construct(
        GameService $gameService,
    ) {
        $this->gameService = $gameService;
    }
    public function updateScore(Game $game): void
    {
        $winnerPiece = $this->gameService->getWinnerPiece($game);
        if ($winnerPiece === '') {
            return;
        }

        $competition = $game->competition;
        $player = $this->gameService->getPlayer($competition, $winnerPiece);
        $player->score++;
        $player->save();
    }
}
