<?php

namespace App\Http\Requests;

use App\Rules\PlayAsRule;
use App\Services\AiService;
use Illuminate\Foundation\Http\FormRequest;

class NewCompetitionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'aiStrength' => ['nullable', 'integer', 'min:0', 'max:'.AiService::MAX_STRENGTH],
            'playAs' => ['nullable', new PlayAsRule()],
        ];
    }
}
