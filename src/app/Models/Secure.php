<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secure extends Model
{
    /**
     * @param int $numberOfPassengers
     * @return bool
     */
    public static function tooManyPassengers(int $numberOfPassengers): bool
    {
        return $numberOfPassengers > 10;
    }
}
