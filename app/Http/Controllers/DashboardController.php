<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::all();

        return Inertia::render('Dashboard', [
            'warehouses' => $warehouses,
        ]);
    }

    public function fleet()
    {
        dd('fleet page');
    }

    public function driver()
    {
        dd('driver page');
    }

    public function dispatcher()
    {
        dd('dispatcher page');
    }

    public function finance()
    {
        dd('finance page');
    }

    public function sales()
    {
        dd('sales page');
    }

    public function test()
    {
        return Inertia::render('Test');
    }
}
