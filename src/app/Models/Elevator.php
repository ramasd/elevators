<?php

namespace App\Models;

use App\Enums\Direction;
use App\Enums\Door;
use App\Enums\Floor;
use App\Enums\State;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;

class Elevator extends Model
{
    /**
     * @var Floor
     */
    private Floor $currentFloor = Floor::Ground;

    /**
     * @var Direction
     */
    private Direction $direction = Direction::Down;

    /**
     * @var State
     */
    private State $state = State::Idle;

    /**
     * @var Door
     */
    private Door $door = Door::Closed;

    /**
     * @var array
     */
    private array $list = [];

    /**
     * @return Floor
     */
    public function getCurrentFloor(): Floor
    {
        return $this->currentFloor;
    }

    /**
     * @return int
     */
    public function getCurrentFloorValue(): int
    {
        return $this->currentFloor->value;
    }

    /**
     * @param Floor $floor
     * @return $this
     */
    public function setCurrentFloor(Floor $floor): static
    {
        $this->currentFloor = $floor;

        return $this;
    }

    /**
     * @return Direction
     */
    public function getDirection(): Direction
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
     * @return State
     */
    public function getState(): State
    {
        return $this->state;
    }

    /**
     * @param State $state
     * @return $this
     */
    public function setState(State $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Door
     */
    public function getDoor(): Door
    {
        return $this->door;
    }

    /**
     * @param Door $door
     * @return $this
     */
    public function setDoor(Door $door): static
    {
        $this->door = $door;

        return $this;
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * @param $request
     * @return $this
     */
    public function addRequestToList($request): static
    {
        $this->list[] = $request;

        return $this;
    }

    /**
     * @param $request
     * @return $this
     */
    public function addRequestToBeginningOfList($request): static
    {
        array_unshift($this->list, $request);

        return $this;
    }


    /**
     * @return $this
     */
    public function removeFirstRequestFromList(): static
    {
        array_shift($this->list);

        return $this;
    }


    /**
     * @param int $key
     * @return $this
     */
    public function removeRequestFromListByKey(int $key): static
    {
        array_splice($this->list, $key, 1);

        return $this;
    }

    /**
     * @return ExternalRequest|null
     */
    public function getExternalRequestFromList(): ?ExternalRequest
    {
        foreach ($this->list as $request) {
            if ($request instanceof ExternalRequest) {
                return $request;
            }
        }

        return null;
    }

    /**
     * @return $this
     */
    public function sortListAsc(): static
    {
        usort($this->list, array("self", "sort"));

        return $this;
    }

    /**
     * @param $request1
     * @param $request2
     * @return int
     */
    #[Pure] private static function sort($request1, $request2): int
    {
        $floor1 = $request1 instanceof ExternalRequest ? $request1->getSourceFloorValue() : $request1->getDestinationFloorValue();
        $floor2 = $request2 instanceof ExternalRequest ? $request2->getSourceFloorValue() : $request2->getDestinationFloorValue();

        return $floor1 == $floor2 ? 0 : ($floor1 > $floor2 ? 1 : -1);
    }

    /**
     * @return $this
     */
    public function sortListByObjects(): static
    {
        foreach ($this->list as $key => $request) {
            if ($request instanceof ExternalRequest) {
                $this->removeRequestFromListByKey($key);
                $this->addRequestToBeginningOfList($request);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeDuplicateFloors(): static
    {
        $floors = array_map(function ($request) {
            if ($request instanceof ExternalRequest) {
                $goalFloor = $request->getSourceFloorValue();
            } else {
                $goalFloor = $request->getDestinationFloorValue();
            }
            return $goalFloor;
        }, $this->list);

        $unique_requests = array_unique($floors);

        $this->list = array_values(array_intersect_key($this->list, $unique_requests));

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return spl_object_id($this);
    }

    /**
     * @return $this
     */
    public function moveUp(): static
    {
        if (Floor::tryFrom($this->currentFloor->value)->value < Floor::max()->value) {
            $this->currentFloor = Floor::tryFrom($this->currentFloor->value + 1);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function moveDown(): static
    {
        if (Floor::tryFrom($this->currentFloor->value)->value > Floor::min()->value) {
            $this->currentFloor = Floor::tryFrom($this->currentFloor->value - 1);
        }

        return $this;
    }

    /**
     * @param ExternalRequest $externalRequest
     * @return bool
     */
    #[Pure] public function isEligible(ExternalRequest $externalRequest): bool
    {
        $isEligible = true;
        $requestDirection = $externalRequest->getDirection();
        $requestSourceFloor = $externalRequest->getSourceFloorValue();

        foreach ($this->list as $request) {
            if ($request instanceof ExternalRequest and $request->getDirection() !== $requestDirection) {
                $isEligible = false;
            }
        }

        if ($this->state !== State::Idle) {
            if ($this->direction !== $requestDirection or
                $this->direction === Direction::Up and $this->currentFloor > $requestSourceFloor or
                $this->direction === Direction::Down and $this->currentFloor < $requestSourceFloor) {
                $isEligible = false;
            }
        }

        return $isEligible;
    }

    /**
     * @return $this
     */
    public function prepareRoute(): static
    {
        if ($this->state === State::Idle and
            $this->getExternalRequestFromList() and
            $this->getExternalRequestFromList()->getDirection() === Direction::Down) {
            $this->list = array_reverse($this->list);
        } else {
            if ($this->direction === Direction::Up) {
                $this->list = array_reverse($this->list);
            }

            foreach ($this->list as $key => $request) {
                $goalFloor = $request instanceof ExternalRequest ? $request->getSourceFloorValue() : $request->getDestinationFloorValue();
                if ($this->direction === Direction::Up and $goalFloor < $this->currentFloor->value or
                    $this->direction === Direction::Down and $goalFloor <= $this->currentFloor->value) {
                    unset($this->list[$key]);
                    array_unshift($this->list, $request);;
                }
            }

            if ($this->direction === Direction::Up) {
                $this->list = array_reverse($this->list);
            }
        }

        return $this;
    }
}
