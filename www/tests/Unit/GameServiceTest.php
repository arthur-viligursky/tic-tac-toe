<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Player;
use App\Models\Tile;
use App\Services\GameService;
use App\Services\MoveService;
use Tests\InGameTestCase;

class GameServiceTest extends InGameTestCase
{
    public function test_get_competition()
    {
        $gameService = resolve(GameService::class);
        $competition = $this->createCompetition();
        $user = $competition->user;
        $retreivedCompetition = $gameService->getCompetition($user);
        $this->assertEquals($competition->id, $retreivedCompetition->id);
    }

    public function test_get_last_game()
    {
        $gameService = resolve(GameService::class);
        $competition = $this->createCompetition();
        $game = $gameService->getLastGame($competition);
        $this->assertInstanceOf(Game::class, $game);
        $this->assertEquals($competition->id, $game->competition->id);
    }

    public function test_get_board()
    {
        $gameService = resolve(GameService::class);
        $game = $this->createGame();
        $board = $gameService->getBoard($game);
        $this->assertEquals(Game::BOARD_SIZE, count($board));
        foreach ($board as $row) {
            $this->assertEquals(Game::BOARD_SIZE, count($row));
            foreach ($row as $piece) {
                $this->assertTrue(in_array($piece, ['x', 'o', '']));
            }
        }
    }

    public function test_get_player()
    {
        $gameService = resolve(GameService::class);
        $competition = $this->createCompetition();
        $players = [];
        foreach (['x', 'o'] as $piece) {
            $player = $gameService->getPlayer($competition, $piece);
            $this->assertInstanceOf(Player::class, $player);
            $players[$piece] = $player;
        }
        $this->assertNotEquals($players['x']->id, $players['o']->id);
    }

    public function test_get_tile()
    {
        $game = $this->createGame();
        $gameService = resolve(GameService::class);
        $tilesIds = [];
        foreach (range(0, Game::BOARD_SIZE - 1) as $y) {
            foreach (range(0, Game::BOARD_SIZE - 1) as $x) {
                $tile = $gameService->getTile($game, compact('x', 'y'));
                $this->assertInstanceOf(Tile::class, $tile);
                $tilesIds[$tile->id] = true;
            }
        }
        // all the tiles must be different
        $this->assertEquals(pow(Game::BOARD_SIZE, 2), count($tilesIds));
    }

    public function test_get_moves_count()
    {
        $gameService = resolve(GameService::class);
        $moveService = resolve(MoveService::class);
        $game1 = $this->createGame();
        $game2 = $this->createGame();
        self::assertEquals(0, $gameService->getMovesCount($game1));
        $moveService->makeMove('x', ['x' => 0, 'y' => 0], $game1);
        self::assertEquals(1, $gameService->getMovesCount($game1));
        $moveService->makeMove('o', ['x' => 0, 'y' => 1], $game1);
        self::assertEquals(2, $gameService->getMovesCount($game1));
        // moved from different game must not be counted
        self::assertEquals(0, $gameService->getMovesCount($game2));
    }

    public function test_get_current_turn()
    {
        $gameService = resolve(GameService::class);
        $moveService = resolve(MoveService::class);
        $game = $this->createGame();
        $this->assertEquals('x', $gameService->getCurrentTurn($game));
        $moveService->makeMove('x', ['x' => 0, 'y' => 0], $game);
        self::assertEquals('o', $gameService->getCurrentTurn($game));
        $moveService->makeMove('o', ['x' => 0, 'y' => 1], $game);
        self::assertEquals('x', $gameService->getCurrentTurn($game));
        $game->status = Game::STATUS_DRAW;
        self::assertEquals(null, $gameService->getCurrentTurn($game));
    }

    public function test_delete_competition()
    {
        $gameService = resolve(GameService::class);
        $competition = $this->createCompetition();
        $user = $competition->user;
        $gameService->deleteCompetition($competition);
        $retreivedCompetition = $gameService->getCompetition($user);
        $this->assertEquals(null, $retreivedCompetition);
    }
}
