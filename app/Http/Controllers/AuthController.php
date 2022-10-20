<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    //__Register user
    public function register(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user
        ]);
    }

    //__Login user
    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8'
        ]);

        if (!Auth::attempt($validate)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 403);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user
        ], 200);
    }

    //__Logout user
    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out'
        ], 200);
    }

    //__Get user
    public function user()
    {
        return response([
            'user' => Auth::user()
        ], 200);
    }


    //__Update user
    public function update(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string',
        ]);

        $image = $this->saveImg($request->file('image'), 'profiles');

        auth()->user()->update([
            'name' => $request->name,
            'image' => $image,
        ]);

        return response([
            'user' => auth()->user(),
            'message' => 'User updated successfully'
        ], 200);
    }
}
