<?php 


namespace App\Repositories;

use App\Models\Trip;
use App\Models\User;
use App\Repositories\Interfaces\TripRepositoryInterface;


class TripRepository implements TripRepositoryInterface 
{
    public function all(array $relations = [], array $withCount = [])
    {
        return Trip::with($relations)
            ->withCount($withCount)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function find(int $id, array $relations = [], array $withCount = [])
    {
        return Trip::with($relations)
            ->withCount($withCount)
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Trip::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = Trip::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id)
    {
        $user = Trip::findOrFail($id);
        $user->delete();
        return $user;
    }
}
