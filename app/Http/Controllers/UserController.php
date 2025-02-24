<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Get the authenticated user's info.
     */
    public function getUserInfo(Request $request)
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }
}
