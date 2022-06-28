<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tile extends Model
{
    protected $guarded = ['id'];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function move(): BelongsTo
    {
        return $this->belongsTo(Move::class);
    }
}
