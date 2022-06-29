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
        $lines = [];
        $diagonalLine1 = [];
        $diagonalLine2 = [];
        foreach (range(0, Game::BOARD_SIZE - 1) as $coordinate1) {
            $horizontalLine = [];
            $verticalLine = [];
            foreach (range(0, Game::BOARD_SIZE - 1) as $coordinate2) {
                $horizontalLine[] = [$coordinate1, $coordinate2];
                $verticalLine[] = [$coordinate2, $coordinate1];
            }
            $lines[] = [$horizontalLine];
            $lines[] = [$verticalLine];
            $diagonalLine1[] = [$coordinate1, $coordinate1];
            $diagonalLine2[] = [$coordinate1, Game::BOARD_SIZE - $coordinate1 - 1];
        }
        $lines[] = $diagonalLine1;
        $lines[] = $diagonalLine2;
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
