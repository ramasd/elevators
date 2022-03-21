<?php

namespace App\Http\Controllers;

use App\Enums\Direction;
use App\Enums\Floor;
use App\Enums\State;
use App\Models\Dispatcher;
use App\Models\Elevator;
use App\Models\ExternalRequest;
use App\Models\InternalRequest;
use App\Services\ElevatorService;
use App\Services\RequestService;
use App\Services\SecureService;

class ElevatorController extends Controller
{
    /**
     * @var ElevatorService
     */
    protected ElevatorService $elevatorService;

    /**
     * @var RequestService
     */
    protected RequestService $requestService;

    /**
     * @var SecureService
     */
    protected SecureService $secureService;

    /**
     * @param ElevatorService $elevatorService
     * @param RequestService $requestService
     * @param SecureService $secureService
     */
    public function __construct(ElevatorService $elevatorService, RequestService $requestService, SecureService $secureService)
    {
        $this->elevatorService = $elevatorService;
        $this->requestService = $requestService;
        $this->secureService = $secureService;
    }

    /**
     * @return void
     */
    public function index(): void
    {
        $dispatcher = new Dispatcher();
        $elevator1 = new Elevator();
        $elevator2 = new Elevator();
        $elevators = [$elevator1, $elevator2];

        // Scenario
        $elevator2->setCurrentFloor(Floor::Second);
        $elevator2->setDirection(Direction::Up);

        $dispatcher->addExternalRequestToElevatorList(new ExternalRequest(Direction::Down, Floor::Third), $elevators);
        $dispatcher->addInternalRequestToList(new InternalRequest(Floor::Fourth, $elevator2));
        $dispatcher->addInternalRequestToList(new InternalRequest(Floor::First, $elevator1));
        $dispatcher->addExternalRequestToElevatorList(new ExternalRequest(Direction::Up, Floor::First), $elevators);
        $dispatcher->addInternalRequestToList(new InternalRequest(Floor::Third, $elevator1));
        $dispatcher->addInternalRequestToList(new InternalRequest(Floor::First, $elevator2));
        $dispatcher->addExternalRequestToElevatorList(new ExternalRequest(Direction::Up, Floor::Ground), $elevators);

        foreach ($elevators as $key => $elevator) {

            $this->elevatorService->displayInfo($elevator, $key);
            $this->elevatorService->prepareRoute($elevator);
            $this->elevatorService->displayRoute($elevator);

            while ($list = $elevator->getList()) {
                $request = reset($list);
                $directionBefore = $elevator->getDirection();
                $this->elevatorService->executeRequest($request, $elevator);

                if (!count($elevator->getList())) {
                    $this->elevatorService->setState(State::Idle, $elevator);
                    echo "<br>";
                }

                if ($directionBefore !== $elevator->getDirection() or !count($elevator->getList())) {
                    $dispatcher->checkBufferList($elevators);
                    $this->elevatorService->prepareRoute($elevator);
                }

                if (!count($elevator->getList())) {
                    $waitingFloor = $this->elevatorService->getWaitingFloor($elevator);

                    if ($waitingFloor !== $elevator->getCurrentFloor()) {
                        $dispatcher->addInternalRequestToList(new InternalRequest($waitingFloor, $elevator));
                        new InternalRequest($waitingFloor, $elevator);
                    }
                }
            }

            echo "<hr>";
        }
    }
}
