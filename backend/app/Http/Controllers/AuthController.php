<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    // controller for registering the user
    public function register(Request $request)
    {
        // Added user registration validation
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'in:user,agent,admin'
        ]);
        // create user using create method

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role ?? 'user',
            'password' => Hash::make($request->password),
        ]);
        // return the registered user as json data
        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ]);
    }
    // Login Controller
    public function login(Request $request)
    {
        // Validate email and password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);
        // Find entered user using email
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect']]);
        }
        // Return the user with the authentication token which is used for bearer token authentication
        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ]);
    }
    // Logout route to clear the user login token
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged Out']);
    }

    // Return the Logged in User profile
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
    public function getAgents()
    {
        $agents = User::where('role', 'agent')->select('id', 'name', 'email')->get();
        return response()->json($agents);
    }
}
