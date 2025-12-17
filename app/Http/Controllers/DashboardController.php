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

    public function test()
    {
        return Inertia::render('Test');
    }
}
