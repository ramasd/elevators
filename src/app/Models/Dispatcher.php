<?php

namespace App\Models;

use App\Enums\Direction;
use App\Enums\Floor;
use App\Enums\State;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;

class Dispatcher extends Model
{
    /**
     * @var array
     */
    private array $bufferList = [];

    /**
     * @param ExternalRequest $request
     * @return $this
     */
    public function addRequestToBufferList(ExternalRequest $request): static
    {
        $this->bufferList[] = $request;

        return $this;
    }

    /**
     * @param ExternalRequest $request
     * @param array $elevators
     * @return bool
     */
    public function addExternalRequestToElevatorList(ExternalRequest $request, array $elevators): bool
    {
        // do not attach a request if the same has already been added
        foreach ($elevators as $elevator) {
            if (in_array($request, $elevator->getList())) return true;
        }

        if ($elevator = $this->getTheMostAppropriateElevator($request, $elevators)) {
            $elevator->addRequestToList($request)->sortListAsc();
            return true;
        }

        $this->addRequestToBufferList($request);

        return false;
    }

    /**
     * @param ExternalRequest $externalRequest
     * @param array $elevators
     * @return Elevator|null
     */
    #[Pure] public function getTheMostAppropriateElevator(ExternalRequest $externalRequest, array $elevators): ?Elevator
    {
        $mostEligibleElevator = null;
        $minEstimatedTime = Floor::max()->value * 5 + Floor::amount() * 10 + 1;
        $sourceFloor = $externalRequest->getSourceFloorValue();

        foreach ($elevators as $elevator) {
            if ($elevator->isEligible($externalRequest)) {
                $diff = $elevator->getCurrentFloorValue() - $sourceFloor;
                $list = $elevator->sortListAsc()->getList();
                // The estimated number of stops to request source floor
                $stops = 0;

                foreach ($list as $request) {
                    $requestGoal = $request instanceof ExternalRequest ? $request->getSourceFloorValue() : $request->getDestinationFloorValue();

                    if ($elevator->getState() === State::Idle) {
                        if ($externalRequest->getDirection() === Direction::Up and $requestGoal < $sourceFloor or
                            $externalRequest->getDirection() === Direction::Down and $requestGoal > $sourceFloor) $stops++;
                    } else {
                        if ($diff >= 0 and $requestGoal <= $elevator->getCurrentFloorValue() and $requestGoal > $sourceFloor or
                            $diff <= 0 and $requestGoal >= $elevator->getCurrentFloorValue() and $requestGoal < $sourceFloor) $stops++;
                    }
                }

                $estimatedTime = abs($diff) * 5 + $stops * 10;

                if ($estimatedTime <= $minEstimatedTime) {
                    $minEstimatedTime = $estimatedTime;
                    $mostEligibleElevator = $elevator;
                }
            }
        }

        return $mostEligibleElevator;
    }

    /**
     * @param $elevators
     * @return void
     */
    public function checkBufferList($elevators): void
    {
        foreach ($this->bufferList as $request) {

            $this->addExternalRequestToElevatorList($request, $elevators) ?
                array_shift($this->bufferList) : array_pop($this->bufferList);
        }
    }

    /**
     * @param InternalRequest $request
     * @return void
     */
    public function addInternalRequestToList(InternalRequest $request): void
    {
        $elevator = $request->getElevator();
        $elevator->addRequestToList($request);
    }
}
