<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //hanles login functionality
    public function login(Request $request)
    {
        try{
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('api-token')->plainTextToken;

                $user = $user->toArray();
                $user['created_at'] = explode('T', $user['created_at'])[0];

                return response()->json(['isSuccess' => true,'user' => $user, 'token' => $token]);
            }

            return response()->json(['isSuccess' => false,'message' => 'Invalid Credentials'], 401);

        }catch (ValidationException $e) {
            return response()->json([
                'isSuccess' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }


    //handles register functionality
    public function register(Request $request)
    {
        try{
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:4',
                'role' => 'required|string|in:admin,reviewer,candidate',
            ]);

            $hashedPassword = Hash::make($request->password);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'role' => $request->role,
            ]);

            $token = $user->createToken('api-token')->plainTextToken;
            $user = $user->toArray();
            $user['created_at'] = explode('T', $user['created_at'])[0];
            
            return response()->json([
                'isSuccess' => true,
                'user' => $user,
                'token' => $token,
            ], 201);

        }catch (ValidationException $e) {
            return response()->json([
                'isSuccess' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    //handles logout functionality
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->tokens()->delete();
            return response()->json(['isSuccess' => true, 'message' => 'Logged out successfully']);
        }
        return response()->json(['isSuccess' => false, 'message' => 'User not authenticated'], 401);
    }

}
