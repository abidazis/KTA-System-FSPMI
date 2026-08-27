<?php

namespace App\Support;

class PrintOrder
{
    /**
     * Reorder cells for the back side of a duplex print job.
     *
     * Layout: 2 cols × N rows per page (e.g. 3 rows for 3 KTA/page)
     * Each row = [FRONT, BACK] pair
     *
     * FRONT order (top-to-bottom):
     *   Row 0: [FRONT-0] [BACK-0]
     *   Row 1: [FRONT-1] [BACK-1]
     *   Row 2: [FRONT-2] [BACK-2]
     *
     * For BACK side printing with duplex:
     *
     * long-edge (default): reverse the row order so that when the
     *   paper is flipped along the top edge, each FRONT lines up with its BACK.
     *   [FRONT-2][BACK-2] → [FRONT-1][BACK-1] → [FRONT-0][BACK-0]
     *
     * short-edge: reverse within each pair so that BACK-0 comes before BACK-1,
     *   simulating a flip along the left edge.
     *   [BACK-0][FRONT-0] → [BACK-1][FRONT-1] → [BACK-2][FRONT-2]
     *
     * @param  array  $cells     Array of KTA data (each a ktaData array).
     * @param  string $mode      'long-edge' or 'short-edge'.
     * @param  int    $cols      Number of columns per row (default 2: FRONT|BACK).
     * @return array
     */
    public static function reorderForDuplex(array $cells, string $mode = 'long-edge', int $cols = 2): array
    {
        if (empty($cells)) {
            return [];
        }

        if ($mode === 'short-edge') {
            // Horizontal flip: reverse within each row
            // [A, B] → [B, A], [C, D] → [D, C], ...
            $rows = array_chunk($cells, $cols);
            $result = [];
            foreach ($rows as $row) {
                $padded = array_pad($row, $cols, null);
                $result[] = array_reverse($padded);
            }
            return array_merge(...$result);
        }

        // long-edge (default): vertical flip — reverse row order
        // [A, B, C, D, E, F] → [F, E, D, C, B, A]
        return array_reverse($cells);
    }
}
