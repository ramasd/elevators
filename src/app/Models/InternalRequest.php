<?php

namespace App\Models;

use App\Enums\Floor;
use Illuminate\Database\Eloquent\Model;

class InternalRequest extends Model
{
    /**
     * @var Elevator
     */
    private Elevator $elevator;

    /**
     * @var Floor
     */
    private Floor $destinationFloor;

    /**
     * @param Floor $destinationFloor
     * @param Elevator $elevator
     */
    public function __construct(Floor $destinationFloor, Elevator $elevator)
    {
        parent::__construct();
        $this->destinationFloor = $destinationFloor;
        $this->elevator = $elevator;
    }

    /**
     * @return int
     */
    public function getDestinationFloorValue(): int
    {
        return $this->destinationFloor->value;
    }

    /**
     * @return Elevator
     */
    public function getElevator(): Elevator
    {
        return $this->elevator;
    }
}
