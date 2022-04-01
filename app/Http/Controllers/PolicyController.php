<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index()
    {
        //
    }

    public function view($policy)
    {
        if (in_array($policy, config('policies'))) {
            return view('policies.' . $policy);
        }

        abort(404);
    }
}
