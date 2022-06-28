<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    public const STATUS_DRAW = 'draw';
    public const STATUS_O_WON = 'o won';
    public const STATUS_ONGOING = 'ongoing';
    public const STATUS_X_WON = 'x won';

    protected $guarded = ['id'];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function moves(): HasMany
    {
        return $this->hasMany(Move::class);
    }
}
