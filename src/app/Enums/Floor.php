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
}
