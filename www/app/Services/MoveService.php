<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Move;
use Illuminate\Support\Facades\DB;

class MoveService
{
    public const RESULT_CANNOT_MAKE_MOVE = 406;
    public const RESULT_CONFLICT = 409;
    public const RESULT_OK = 200;
    public const VICTORY_STATUSES = [
        'x' => Game::STATUS_X_WON,
        'o' => Game::STATUS_O_WON,
    ];

    protected AiService $aiService;
    protected GameService $gameService;
    protected GameResultService $gameResultService;

    function __construct(
        AiService $aiService,
        GameService $gameService,
        GameResultService $gameResultService,
    ) {
        $this->aiService = $aiService;
        $this->gameService = $gameService;
        $this->gameResultService = $gameResultService;
    }

    protected function makeAiMove(string $piece, Game $game): void {
        $board = $this->gameService->getBoard($game);
        $coordinates = $this->aiService->makeMove($board, $piece);
        $this->makeMove($piece, $coordinates, $game);
    }

    protected function makeAiMoveIfNeeded(Game $game): void
    {
        $currentTurn = $this->gameService->getCurrentTurn($game);
        $player = $this->gameService->getPlayer($game->competition, $currentTurn);
        if ($player->is_ai) {
            $this->makeAiMove($currentTurn, $game);
        }
    }

    public function makeMove(string $piece, array $coordinates, Game $game): int
    {
        $currentTurn = $this->gameService->getCurrentTurn($game);
        if ($currentTurn !== $piece) {
            return static::RESULT_CANNOT_MAKE_MOVE;
        }
        $tile = $this->gameService->getTile($game, $coordinates);
        if ($tile->move !== null) {
            return static::RESULT_CONFLICT;
        }
        $move = new Move([
            'index' => ($this->gameService->getMovesCount($game) + 1),
        ]);
        $player = $this->gameService->getPlayer($game->competition, $piece);
        DB::transaction(function() use ($game, $move, $player, $tile) {
            $move->game()->associate($game);
            $move->player()->associate($player);
            $move->save();
            $tile->move()->associate($move);
            $tile->save();
            $this->updateGame($game);
        });

        return static::RESULT_OK;
    }

    protected function updateGame(Game $game): void
    {
        $board = $this->gameService->getBoard($game);
        $game->status = $this->gameResultService->getGameResult($board);
        if ($game->status === Game::STATUS_ONGOING) {
            $this->makeAiMoveIfNeeded($game);
        } else if ($game->status !== Game::STATUS_DRAW) {
            $this->updateScore($game);
        }
        $game->save();
    }

    protected function updateScore(Game $game): void
    {
        $competition = $game->competition;
        $winningPiece = array_flip(static::VICTORY_STATUSES)[$game->status];
        $player = $this->gameService->getPlayer($competition, $winningPiece);
        $player->score++;
        $player->save();
    }
}
