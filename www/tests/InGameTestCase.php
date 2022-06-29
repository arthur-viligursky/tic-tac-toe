<?php

namespace Tests;

use App\Models\Game;
use App\Services\GameService;

class InGameTestCase extends InCompetitionTestCase
{
    protected function createGame(): Game
    {
        $gameService = resolve(GameService::class);

        return $gameService->getLastGame($this->createCompetition());
    }
}
