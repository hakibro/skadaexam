<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('features.naskah.dashboard');
    }
}
