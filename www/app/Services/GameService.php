<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\Game;
use App\Models\Player;
use App\Models\Tile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GameService
{
    public const PLAY_AS_OPTION_NONE = 'none';
    public const PLAY_AS_OPTION_X = 'x';
    public const PLAY_AS_OPTION_O = 'o';
    public const PLAY_AS_OPTION_BOTH = 'both';
    public const PLAY_AS_OPTIONS = [self::PLAY_AS_OPTION_NONE, self::PLAY_AS_OPTION_X, self::PLAY_AS_OPTION_O, self::PLAY_AS_OPTION_BOTH];
    public const PIECES = ['x', 'o'];

    public function getBoard(Game $game): array
    {
        $result = [];
        $tiles = Tile
            ::whereBelongsTo($game)
            ->get()
        ;

        foreach ($tiles as $tile) {
            if (!array_key_exists($tile->y, $result)) {
                $result[$tile->y] = [];
            }
            $result[$tile->y][$tile->x] = ($tile->move?->player->piece ?? '');
        }

        return $result;
    }

    public function getCompetition(User $user): ?Competition
    {
        return Competition::whereBelongsTo($user)->first();
    }

    public function getCurrentTurn(Game $game): ?string
    {
        if ($game->status === Game::STATUS_ONGOING) {
            return static::PIECES[$this->getMovesCount($game) % 2];
        } else {
            return null;
        }
    }

    public function getLastGame(Competition $competition): Game
    {
        return Game
            ::query()
            ->whereBelongsTo($competition)
            ->orderBy('created_at', 'desc')
            ->first()
        ;
    }

    public function getMovesCount(Game $game): int
    {
        $game->loadCount('moves');

        return $game->moves_count;
    }

    public function getPlayer(Competition $competition, string $piece): Player
    {
        return Player
            ::whereBelongsTo($competition)
            ->where('piece', $piece)
            ->first()
        ;
    }

    public function getTile(Game $game, array $coordinates): Tile
    {
        return Tile
            ::whereBelongsTo($game)
            ->where('x', $coordinates['x'])
            ->where('y', $coordinates['y'])
            ->first()
        ;
    }

    public function getWinnerPiece(Game $game) {
        return match ($game->status) {
            Game::STATUS_O_WON => 'o',
            Game::STATUS_X_WON => 'x',
            default => '',
        };
    }

    public function restartGame(Competition $competition): void
    {
        DB::transaction(function() use ($competition) {
            $lastGame = $this->getLastGame($competition);
            $this->updateScore($lastGame);
            $this->startGame($competition);
        });
    }

    public function startCompetition(array $options, User $user): Competition
    {
        $competition = new Competition();
        $options += [
            'aiStrength' => AiService::DEFAULT_STRENGTH,
            'playAs' => 'both',
        ];

        DB::transaction(function() use ($competition, $options, $user) {
            $competition->user()->associate($user);
            $competition->save();
            foreach (static::PIECES as $piece) {
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

    protected function updateScore(Game $game): void
    {
        $winnerPiece = $this->getWinnerPiece($game);
        if ($winnerPiece === '') {
            return;
        }

        $competition = $game->competition;
        $player = $this->getPlayer($competition, $winnerPiece);
        $player->score++;
        $player->save();
    }
}
