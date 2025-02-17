<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the login request
        $validated = $request->validate([
            'username' => 'required|string', // Using 'username' instead of 'email'
            'password' => 'required|string',
        ]);

        // Attempt to find the user by username
        $user = User::where('username', $validated['username'])->first();

        // If no user is found or the password doesn't match, return an error
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Generate a personal access token if the login is successful
        $token = $user->createToken('InventorySystem')->plainTextToken;

        // Return the user and token in the response
        return response()->json(['user' => $user, 'token' => $token]);
    }
}
