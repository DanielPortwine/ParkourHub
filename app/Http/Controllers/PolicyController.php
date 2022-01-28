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
        $policy = Policy::where('slug', $policy)->first();

        if (!empty($policy)) {
            return view('policies.' . $policy->slug);
        }

        abort(404);
    }
}
