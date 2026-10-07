<?php

namespace App\Support;

class SurveyState
{
    public const BASELINE = 'baseline';

    public const STATES = ['baseline', 'midline', 'endline'];

    public static function current(): string
    {
        $state = null;

        try {
            if (app()->bound('session') && request()->hasSession()) {
                $state = session('survey_state');
            }
        } catch (\Throwable $e) {
            // no session available (console, tests)
        }

        return in_array($state, self::STATES, true) ? $state : self::BASELINE;
    }

    public static function set(string $state): void
    {
        if (in_array($state, self::STATES, true)) {
            session(['survey_state' => $state]);
        }
    }

    /** The state whose measurements are copied into the given one. */
    public static function previous(string $state): ?string
    {
        return match ($state) {
            'midline' => 'baseline',
            'endline' => 'midline',
            default => null,
        };
    }
}
