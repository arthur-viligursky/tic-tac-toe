<?php

namespace Tests;

use App\Models\Competition;
use App\Models\User;
use App\Services\GameStartService;

class InCompetitionTestCase extends TestCase
{
    protected function createCompetition(): Competition
    {
        $user = User::factory()->create();
        $gameStartService = resolve(GameStartService::class);

        return $gameStartService->startCompetition([], $user);
    }
}
