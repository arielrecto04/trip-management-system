<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;

class UpdateStatusTrip extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trips:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update trip status and vehicle current_trip_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        Trip::where('status', 'pending')
            ->where('planned_start_time', '<=', $now)
            ->get()
            ->each(function ($trip) {
                $trip->update(['status' => 'in progress']);

                if($trip->vehicle) {
                    $trip->vehicle->update(['current_trip_id' => $trip->id]);
                }
            });
        
        Trip::where('status', 'in progress')
            ->where('planned_start_time', '<', $now)
            ->get()
            ->each(function ($trip) {
                if($trip->status === 'completed' || $trip->status === 'cancelled') {
                    $trip->vehicle?->update(['current_trip_id' => null]);
                }
            });

        Trip::whereIn('status', ['completed', 'cancelled'])
            ->whereNotNull('vehicle_id')
            ->get()
            ->each(function($trip) {
                $trip->vehicle->update(['current_trip_id' => null]);
            });
        
        $this->info('Trip statuses and vehicle current_trip_id updated successfully');
    }
}
