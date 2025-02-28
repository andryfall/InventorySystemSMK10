<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    /**
     * Change the authenticated user's name.
     */
    public function changeName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        /** @var \App\Models\User $user **/

        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return response()->json(['message' => 'Name updated successfully', 'user' => $user]);
    }

    /**
     * Change the authenticated user's password.
     */
    
     public function changePassword(Request $request)
     {
         $request->validate([
             'current_password' => 'required|string',
             'new_password' => 'required|string|min:8|confirmed',
             'new_password_confirmation' => 'required|string|min:8',
         ]);

         /** @var \App\Models\User $user **/
     
         $user = Auth::user();
     
         if (!Hash::check($request->current_password, $user->password)) {
             return response()->json(['message' => 'Current password is incorrect'], 403);
         }
     
         if ($request->new_password !== $request->new_password_confirmation) {
             return response()->json(['message' => 'New password confirmation does not match'], 422);
         }
     
         $user->password = Hash::make($request->new_password);
         $user->save();
     
         return response()->json(['message' => 'Password updated successfully']);
     }
     
}
