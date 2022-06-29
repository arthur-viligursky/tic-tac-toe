<?php

namespace Tests;

use App\Models\Game;
use Tests\TestCase;

class FeatureTestCase extends TestCase
{
    protected function getFullJsonResponseStructure(): array
    {
        return [
            'board' => ['*' => range(0, Game::BOARD_SIZE - 1)],
            'score' => ['x', 'o'],
            'currentTurn',
            'victory',
        ];
    }
}
