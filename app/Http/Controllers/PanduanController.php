<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PanduanController extends Controller
{
    public function formatDocx()
    {
        return view('panduan.format-docx');
    }
}
