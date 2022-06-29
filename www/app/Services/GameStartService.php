<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameStartService
{
    public const PLAY_AS_OPTION_NONE = 'none';
    public const PLAY_AS_OPTION_X = 'x';
    public const PLAY_AS_OPTION_O = 'o';
    public const PLAY_AS_OPTION_BOTH = 'both';
    public const PLAY_AS_OPTIONS = [self::PLAY_AS_OPTION_NONE, self::PLAY_AS_OPTION_X, self::PLAY_AS_OPTION_O, self::PLAY_AS_OPTION_BOTH];

    protected GameService $gameService;
    protected MoveService $moveService;
    protected ScoreService $scoreService;

    function __construct(
        GameService $gameService,
        MoveService $moveService,
        ScoreService $scoreService,
    ) {
        $this->gameService = $gameService;
        $this->moveService = $moveService;
        $this->scoreService = $scoreService;
    }

    public function restartGame(Competition $competition): void
    {
        DB::transaction(function() use ($competition) {
            $lastGame = $this->gameService->getLastGame($competition);
            $this->scoreService->updateScore($lastGame);
            $this->startGame($competition);
        });
    }

    public function startCompetition(array $options, User $user): Competition
    {
        $competition = new Competition();
        $options += [
            'aiStrength' => AiService::DEFAULT_STRENGTH,
            'playAs' => static::PLAY_AS_OPTION_BOTH,
        ];

        DB::transaction(function() use ($competition, $options, $user) {
            $competition->user()->associate($user);
            $competition->save();
            foreach (GameService::PIECES as $piece) {
                $player = new Player([
                    'is_ai' => ($options['playAs'] !== $piece && $options['playAs'] !== 'both'),
                    'piece' => $piece,
                    'score' => 0,
                ]);
                $player->competition()->associate($competition);
                $player->save();
            }
            $this->startGame($competition);
        });

        return $competition;
    }

    protected function startGame(Competition $competition): Game
    {
        $game = new Game([
            'status' => Game::STATUS_ONGOING,
        ]);

        DB::transaction(function() use ($competition, $game) {
            $game->competition()->associate($competition);
            $game->save();
            foreach (range(0, 2) as $y) {
                foreach (range(0, 2) as $x) {
                    $tile = new Tile(compact('x', 'y'));
                    $tile->game()->associate($game);
                    $tile->save();
                }
            }
        });

        return $game;
    }
}
