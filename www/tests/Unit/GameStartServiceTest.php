<?php

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\User;
use App\Services\GameService;
use App\Services\GameStartService;
use Tests\TestCase;

class GameStartServiceTest extends TestCase
{
    public function test_competition_start_with_default_options()
    {
        $user = User::factory()->create();
        $gameStartService = resolve(GameStartService::class);
        $competition = $gameStartService->startCompetition([], $user);
        $this->assertInstanceOf(Competition::class, $competition);
    }

    public function test_competition_start_with_play_as()
    {
        foreach (GameStartService::PLAY_AS_OPTIONS as $playAs) {
            $user = User::factory()->create();
            $gameStartService = resolve(GameStartService::class);
            $competition = $gameStartService->startCompetition(compact('playAs'), $user);
            $this->assertInstanceOf(Competition::class, $competition);
        }
    }

    public function test_game_restart()
    {
        $user = User::factory()->create();
        $gameService = resolve(GameService::class);
        $gameStartService = resolve(GameStartService::class);
        $competition = $gameStartService->startCompetition([], $user);
        $oldGame = $gameService->getLastGame($competition);
        $gameStartService->restartGame($competition);
        $newGame = $gameService->getLastGame($competition);
        $this->assertNotEquals($oldGame->id, $newGame->id);
    }
}
