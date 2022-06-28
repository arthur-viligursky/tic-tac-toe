<?php
namespace App\Rules;

use App\Services\GameService;
use Illuminate\Contracts\Validation\Rule;

class PlayAsRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ($attribute !== 'playAs') {
            throw new \LogicException(static::class.' misuse');
        }

        return in_array($value, GameService::PLAY_AS_OPTIONS);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Wrong playAs';
    }
}
