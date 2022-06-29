<?php

namespace App\Services;

use App\Models\Game;

class GameResultService
{
    protected function checkLine(array $board, array $coordinates): ?string
    {
        $winningPiece = null;
        foreach ($coordinates as $coordinate) {
            $currentPiece = $board[$coordinate[1]][$coordinate[0]];
            if ($currentPiece === '') {
                return null;
            }
            if ($winningPiece === null) {
                $winningPiece = $currentPiece;
            } else if ($currentPiece !== $winningPiece) {
                return null;
            }
        }

        return [
            'x' => Game::STATUS_X_WON,
            'o' => Game::STATUS_O_WON,
        ][$winningPiece];
    }

    protected function getIsBoardFull(array $board): bool
    {
        foreach ($board as $row) {
            foreach ($row as $tile) {
                if ($tile === '') {
                    return false;
                }
            }
        }

        return true;
    }

    public function getGameResult(array $board): string {
        $lines = [
            [[0, 0], [1, 1], [2, 2]],
            [[0, 2], [1, 1], [0, 2]],
        ];
        foreach (range(0, 2) as $coordinate) {
            $lines[] = [[$coordinate, 0], [$coordinate, 1], [$coordinate, 2]];
            $lines[] = [[0, $coordinate], [1, $coordinate], [2, $coordinate]];
        }
        foreach ($lines as $line) {
            $victory = $this->checkLine($board, $line);
            if ($victory !== null) {
                return $victory;
            }
        }
        if ($this->getIsBoardFull($board)) {
            return Game::STATUS_DRAW;
        } else {
            return Game::STATUS_ONGOING;
        }
    }

}
