<?php

namespace App\Services;

use App\Repositories\Interfaces\DriverRepositoryInterface;
use App\Repositories\Interfaces\TripRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\VehicleRepositoryInterface;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use Carbon\Carbon;

class TripServices {

    public function __construct(
        protected TripRepositoryInterface $tripRepo,
        protected VehicleRepositoryInterface $vehicleRepo,
        protected WarehouseRepositoryInterface $warehouseRepo,
        protected UserRepositoryInterface $userRepo,
        protected DriverRepositoryInterface $driverRepo,
    )
    {}

    public function getAllTrips()
    {
        $trips = $this->tripRepo->all(['driver', 'vehicle', 'dispatcher']);

        return $trips;
    }

    public function getCreateFormData()
    {
        return [
            'drivers' => $this->driverRepo->all(),
            'vehicles' => $this->vehicleRepo->all(),
            'warehouses' => $this->warehouseRepo->all(),
            'dispatchers' => $this->userRepo->getUserByRoleSlug('dispatcher'),
        ];
    }

    public function createTrip(array $data)
    {
        $planned = Carbon::parse($data['planned_start_time']);

        $data['status'] = $planned->isFuture() ? 'pending' : 'in progress';

        $data['planned_start_time'] = $planned->clone()->setTimezone('UTC');

        return $this->tripRepo->create($data);
    }
}