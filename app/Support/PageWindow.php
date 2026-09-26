<?php

namespace App\Support;

/**
 * Builds the list of page links for pagination, e.g. 1 2 3 4 5 6 7 … 20.
 * Null entries mark an ellipsis.
 */
class PageWindow
{
    /**
     * @return array<int, int|null>
     */
    public static function make(int $current, int $last, int $visible = 7): array
    {
        if ($last <= $visible + 2) {
            return range(1, max(1, $last));
        }

        $half = intdiv($visible - 3, 2);

        // Near the start: 1 2 3 4 5 6 7 … last
        if ($current <= $visible - $half - 1) {
            return [...range(1, $visible), null, $last];
        }

        // Near the end: 1 … last-6 … last
        if ($current >= $last - $visible + $half + 2) {
            return [1, null, ...range($last - $visible + 1, $last)];
        }

        // Middle: 1 … c-2 c-1 c c+1 c+2 … last
        return [1, null, ...range($current - $half, $current + $half), null, $last];
    }
}
