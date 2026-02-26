<?php

namespace App\Concerns;

trait PlayerNoteValidationRules
{
    /**
     * Get the validation rules for player notes.
     *
     * @return array<string, array<int, string>>
     */
    protected function noteRules(): array
    {
        return [
            'content' => ['required', 'string', 'min:3', 'max:1000', 'regex:/\S/'],
        ];
    }
}
