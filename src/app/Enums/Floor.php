<?php

declare(strict_types=1);

namespace App\Enums;

enum Floor: int
{
    case Ground = 0;
    case First  = 1;
    case Second = 2;
    case Third  = 3;
    case Fourth = 4;

    /**
     * @param string $prop
     * @return Floor|null
     */
    public static function min(string $prop = "value"): ?Floor
    {
        $min = null;

        if (!empty($values = array_column(self::cases(), $prop)) AND is_int($minVal = min($values))) {
            $min = self::tryFrom($minVal);
        }

        return $min;
    }

    /**
     * @param string $prop
     * @return Floor|null
     */
    public static function max(string $prop = "value"): ?Floor
    {
        $max = null;

        if (!empty($values = array_column(self::cases(), $prop)) AND is_int($maxVal = max($values))) {
            $max = self::tryFrom($maxVal);
        }

        return $max;
    }

    /**
     * @return int
     */
    public static function amount(): int
    {
        return count(self::cases());
    }
}
