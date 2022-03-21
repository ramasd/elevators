<?php

namespace App\Services;

use App\Enums\Direction;
use App\Enums\Door;
use App\Enums\Floor;
use App\Enums\State;
use App\Models\Elevator;
use App\Models\ExternalRequest;
use App\Models\InternalRequest;
use Illuminate\Support\Carbon;

class ElevatorService
{
    /**
     * @var RequestService
     */
    protected RequestService $requestService;

    /**
     * @var SecureService
     */
    protected SecureService $secureService;

    /**
     * @param RequestService $requestService
     * @param SecureService $secureService
     */
    public function __construct(RequestService $requestService, SecureService $secureService)
    {
        $this->requestService = $requestService;
        $this->secureService = $secureService;
    }

    /**
     * @param State $state
     * @param Elevator $elevator
     * @return void
     */
    function setState(State $state, Elevator $elevator): void
    {
        if ($elevator->getState() !== $state) {
            echo "Change state from " . $elevator->getState()->name;
            $elevator->setState($state);
            echo " to " . $elevator->getState()->name . "<br>";
        }
    }

    /**
     * @param Elevator $elevator
     * @param $request
     * @return void
     */
    function changeDirection(Elevator $elevator, $request): void
    {
        echo "Change direction from " . $elevator->getDirection()->name;
        $elevator->setDirection($request->getDirection());
        echo " to " . $elevator->getDirection()->name . "<br>";
    }


    /**
     * @param Elevator $elevator
     * @return void
     */
    function openDoor(Elevator $elevator): void
    {
        if ($elevator->getState() !== State::Moving) {
            if ($elevator->getDoor() === Door::Closed) {
                $elevator->setDoor(Door::Opened);
                echo "Open the door<br>";
            }
        }
    }

    /**
     * @param Elevator $elevator
     * @return void
     */
    function closeDoor(Elevator $elevator): void
    {
        if ($elevator->getDoor() === Door::Opened) {
            $elevator->setDoor(Door::Closed);
            echo "Close the door<br>";
        }
    }

    /**
     * @param Elevator $elevator
     * @return void
     */
    function move(Elevator $elevator): void
    {
        $elevator->getDirection() === Direction::Up ? $elevator->moveUp() : $elevator->moveDown();

        echo "<br>Go " . $elevator->getDirection()->name . " to " . $elevator->getCurrentFloorValue() . " floor<br>";
    }

    /**
     * @param Elevator $elevator
     * @return void
     */
    function prepareRoute(Elevator $elevator): void
    {
        $elevator->sortListByObjects()->removeDuplicateFloors();
        $elevator->sortListAsc()->prepareRoute();
    }

    /**
     * @param $request
     * @param Elevator $elevator
     * @return void
     */
    public function executeRequest($request, Elevator $elevator): void
    {
        $requestGoal = $this->requestService->getRequestGoal($request);
        $currenFloor = $elevator->getCurrentFloorValue();

        if ($requestGoal === $currenFloor) {
            $this->setState(State::Stopped, $elevator);

            if ($request instanceof ExternalRequest) {
                if ($elevator->getDirection() !== $request->getDirection()) {
                    $this->changeDirection($elevator, $request);
                }
            }

            $this->openDoor($elevator);

            echo "Wait 10 sec<br>"; // sleep(10);

            $numberOfPassengers = rand(0, 12);
            $this->secureService->checkLimitation($numberOfPassengers);

            $this->closeDoor($elevator);
            $elevator->removeFirstRequestFromList();
        } else {
            $directionBefore = $elevator->getDirection();

            $elevator->setDirection($requestGoal > $currenFloor ? Direction::Up : Direction::Down);
            if ($directionBefore !== $elevator->getDirection()) {
                echo "Change direction from " . $directionBefore->name . " to " . $elevator->getDirection()->name . "<br>";
            }

            $this->closeDoor($elevator);
            $this->setState(State::Moving, $elevator);
            $this->move($elevator);
        }
    }

    /**
     * @param Elevator $elevator
     * @return Floor
     */
    public function getWaitingFloor(Elevator $elevator): Floor
    {
        $currentHour = Carbon::now()->format('H');

        if ($currentHour > 8 and $currentHour < 18) {
            if ($elevator->getId() % 2 == 0) {
                $waitingFloor = Floor::Third;
            } else {
                $waitingFloor = Floor::First;
            }
        } else {
            $waitingFloor = Floor::Ground;
        }

        return $waitingFloor;
    }

    /**
     * @param Elevator $elevator
     * @param int $key
     * @return void
     */
    public function displayInfo(Elevator $elevator, int $key): void
    {
        echo "<b>Elevator" . $key + 1 . ":</b>" .
            "<br><b>State</b>: " . $elevator->getState()->name .
            "<br><b>Floor</b>: " . $elevator->getCurrentFloorValue() .
            "<br><b>Direction</b>: " . $elevator->getDirection()->name .
            "<br><b>State</b>: " . $elevator->getState()->name .
            "<br><b>Door</b>: " . $elevator->getDoor()->name .
            "<br><b>List</b>: ";
    }

    /**
     * @param Elevator $elevator
     * @return void
     */
    public function displayRoute(Elevator $elevator): void
    {
        foreach ($elevator->getList() as $request) {
            if ($request instanceof ExternalRequest) echo $request->getSourceFloorValue() . $request->getDirection()->name . " ";
            if ($request instanceof InternalRequest) echo $request->getDestinationFloorValue() . " ";
        }
        echo "<br><br>";
    }
}
