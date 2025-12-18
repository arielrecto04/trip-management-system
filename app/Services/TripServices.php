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

    public function getTripRelatedData()
    {
        return [
            'warehouses' => $this->warehouseRepo->all(),
            'drivers'    => $this->driverRepo->all(),
            'vehicles'   => $this->vehicleRepo->getActiveVehicles(),
            'dispatchers'=> $this->userRepo->getUserByRoleSlug('dispatcher'),
        ];
    }

    public function createTrip(array $data)
    {
        $planned = Carbon::parse($data['planned_start_time']);

        $data['status'] = $planned->isFuture()
            ? 'pending'
            : 'in progress';

        $data['planned_start_time'] = $planned;

        $trip = $this->tripRepo->create($data);

        if($data['status'] === 'in progress' && isset($data['vehicle_id'])) {
            $vehicle = $this->vehicleRepo->find($data['vehicle_id']);
            if($vehicle) {
                $vehicle->update(['current_trip_id' => $trip->id]);
            };
        }

        return $trip;
    }

    public function editTrip(int $id, array $data)
    {        
        $trip = $this->tripRepo->find($id);

        if(!empty($data['planned_start_time'])) {
            $data['planned_start_time'] = Carbon::parse($data['planned_start_time']);

            if($data['planned_start_time']->isFuture()) {
                $data['status'] = 'pending';
                $trip->vehicle->update(['current_trip_id' => null]);
            }
        } else {
            $data['status'] = 'in progress';
            $trip->vehicle?->update(['current_trip_id' => $id]);
        }

        return $this->tripRepo->update($id, $data);
    }

    public function deleteTrip(int $id)
    {
        $trip = $this->tripRepo->delete($id);

        if($trip && $trip->vehicle) {
            $trip->vehicle->update(['current_trip_id' => null]);
        }

        return $trip;
    }
}