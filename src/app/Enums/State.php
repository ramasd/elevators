<?php

declare(strict_types=1);

namespace App\Enums;

enum State
{
    case Moving;
    case Stopped;
    case Idle;
}
