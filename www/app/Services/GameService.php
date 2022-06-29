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
    public const PIECES = ['x', 'o'];

    public function deleteCompetition(Competition $competition): void
    {
        $competition->delete();
    }

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
            ->orderBy('id', 'desc')
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
}
