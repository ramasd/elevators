<?php

namespace App\Services;

use App\Models\ExternalRequest;
use App\Models\InternalRequest;

class RequestService
{
    /**
     * @param $request
     * @return int
     */
    function getRequestGoal($request): int
    {
        if ($request instanceof ExternalRequest) {
            $requestGoal = $request->getSourceFloorValue();
        } elseif ($request instanceof InternalRequest) {
            $requestGoal = $request->getDestinationFloorValue();
        } else {
            die('Bad Request!');
        }

        return $requestGoal;
    }
}
