<?php

namespace App\Support;

class PrintOrder
{
    /**
     * Reorder cells for the back side of a duplex print job.
     *
     * FRONT (10 KTA per page, 2 columns × 5 rows, left-to-right top-to-bottom):
     *   [1] [2]
     *   [3] [4]
     *   [5] [6]
     *   [7] [8]
     *   [9] [10]
     *
     * When the paper is flipped on the LONG edge (top↔bottom, vertical flip):
     *   [10] [9]      ← top of back, which is bottom of front when flipped
     *   [8]  [7]
     *   [6]  [5]
     *   [4]  [3]
     *   [2]  [1]
     *
     * When the paper is flipped on the SHORT edge (left↔right, horizontal flip):
     *   [2] [1]
     *   [4] [3]
     *   [6] [5]
     *   [8] [7]
     *   [10] [9]
     *
     * Both reorderings keep the same top-to-bottom row order; only the
     * within-row direction changes.
     *
     * @param  array  $cells      Array of KTA data (length ≤ 10).
     * @param  string $duplexMode 'long-edge' or 'short-edge'.
     * @return array
     */
    public static function reorderForDuplex(array $cells, string $duplexMode = 'long-edge'): array
    {
        // Pad to full 10 slots with null so positions are predictable
        $padded = array_pad($cells, 10, null);

        if ($duplexMode === 'short-edge') {
            // Reverse each row (within-row horizontal flip)
            $rows = array_chunk($padded, 2);
            foreach ($rows as &$row) {
                $row = array_reverse($row);
            }
            return array_merge(...$rows);
        }

        // Default: long-edge (full reverse — both row order AND within-row order)
        // Because flipping on long edge mirrors vertically AND horizontally
        return array_reverse($padded);
    }
}