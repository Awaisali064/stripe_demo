<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class register extends Controller
{
    public function register(Request $reqa)
    {
        $value = $reqa->name;
        echo $value;
    }
}
