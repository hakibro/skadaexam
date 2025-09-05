<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PanduanController extends Controller
{
    public function formatDocx()
    {
        return view('features.naskah.panduan.format-docx');
    }
}
