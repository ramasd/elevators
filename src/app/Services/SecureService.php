<?php

namespace App\Services;

use App\Models\Secure;

class SecureService
{
    /**
     * @param int $numberOfPassengers
     * @return void
     */
    public function checkLimitation(int $numberOfPassengers): void
    {
        while (Secure::tooManyPassengers($numberOfPassengers)) {
            echo "TOO MANY PASSENGERS! The lift can only accommodate up to 10 passengers. There are currently $numberOfPassengers passengers<br>";
            $numberOfPassengers--;
            echo "One passenger leaves the elevator<br>";
        }

        echo "The number of passengers is ALLOWED.<br>";
    }
}
