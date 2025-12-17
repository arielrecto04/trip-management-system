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
        $trips = $this->tripRepo->all(['driver', 'vehicle', 'dispatcher', 'warehouse']);

        return $trips;
    }

    public function getTrip(int $id)
    {
        $trip = $this->tripRepo->find(
            $id,
            ['driver', 'vehicle', 'dispatcher', 'warehouse'],
        );

        return $trip;
    }

    public function getCreateFormData()
    {
        return [
            'drivers' => $this->driverRepo->all(),
            'vehicles' => $this->vehicleRepo->getActiveVehicles(),
            'warehouses' => $this->warehouseRepo->all(),
            'dispatchers' => $this->userRepo->getUserByRoleSlug('dispatcher'),
        ];
    }

    public function createTrip(array $data)
    {
        $planned = Carbon::parse($data['planned_start_time']);

        $data['status'] = $planned->isFuture()
            ? 'pending'
            : 'in progress';

        $data['planned_start_time'] = $planned;

        return $this->tripRepo->create($data);
    }

    public function editTrip(int $id, array $data)
    {
        $trip = $this->tripRepo->find($id);

        if (!empty($data['planned_start_time'])) {
            $planned = Carbon::parse($data['planned_start_time']);

            $data['planned_start_time'] = $planned;

            if (!in_array($trip->status, ['completed', 'cancelled'])) {
                $data['status'] = $planned->isFuture() ? 'pending' : 'in progress';
            }
        }

        return $this->tripRepo->update($id, $data);
    }

    public function deleteTrip(int $id)
    {
        $trip = $this->tripRepo->delete($id);

        return $trip;
    }
}