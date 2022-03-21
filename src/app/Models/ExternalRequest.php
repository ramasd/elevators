<?php

namespace App\Models;

use App\Enums\Direction;
use App\Enums\Floor;
use Illuminate\Database\Eloquent\Model;

class ExternalRequest extends Model
{
    /**
     * @var Direction
     */
    private Direction $direction;

    /**
     * @var Floor
     */
    private Floor $sourceFloor;

    /**
     * @param Direction $direction
     * @param Floor $floor
     */
    public function __construct(Direction $direction, Floor $floor)
    {
        parent::__construct();
        $this->direction = $direction;
        $this->sourceFloor = $floor;
    }

    /**
     * @return Direction
     */
    public function getDirection() :Direction
    {
        return $this->direction;
    }

    /**
     * @param Direction $direction
     * @return $this
     */
    public function setDirection(Direction $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return int
     */
    public function getSourceFloorValue(): int
    {
        return $this->sourceFloor->value;
    }
}
