<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('features.ruangan.dashboard');
    }
}
