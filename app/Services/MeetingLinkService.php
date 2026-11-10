<?php

namespace App\Services;

use Illuminate\Support\Str;

class MeetingLinkService
{
    /**
     * Generate or sanitize a meeting link.
     */
    public static function generate(?string $customUrl = null, string $platform = 'jitsi', ?string $title = null): string
    {
        $customUrl = trim((string) $customUrl);

        if ($customUrl !== '' && filter_var($customUrl, FILTER_VALIDATE_URL)) {
            return $customUrl;
        }

        $uniqueCode = 'icpg-' . Str::lower(Str::random(4)) . '-' . Str::lower(Str::random(4)) . '-' . Str::lower(Str::random(3));

        return match ($platform) {
            'google_meet' => "https://meet.google.com/{$uniqueCode}",
            'zoom' => "https://zoom.us/j/" . rand(100000000, 999999999),
            default => "https://meet.jit.si/ICPG-Meeting-{$uniqueCode}",
        };
    }
}
