<?php

namespace App\Support;

/**
 * Utilidades de similitud entre vectores.
 */
final class Similitud
{
    /**
     * Similitud del coseno entre dos vectores (0 a 1, mayor = más parecidos).
     *
     * @param  array<int, float|int>  $a
     * @param  array<int, float|int>  $b
     */
    public static function coseno(array $a, array $b): float
    {
        $n = min(count($a), count($b));
        if ($n === 0) {
            return 0.0;
        }

        $punto = 0.0;
        $normaA = 0.0;
        $normaB = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $punto += $a[$i] * $b[$i];
            $normaA += $a[$i] * $a[$i];
            $normaB += $b[$i] * $b[$i];
        }

        if ($normaA <= 0 || $normaB <= 0) {
            return 0.0;
        }

        return $punto / (sqrt($normaA) * sqrt($normaB));
    }
}
