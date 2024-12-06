<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // dd('adfasdfasdf');
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->remember_token = $token;
        $user->save();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $credentials['email'])->first();
       dd($user);
        if (!$user) {
            return response()->json([
                'message' => 'Invalid Email',
                'success' => false
            ], 401);
        }
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        else
        {
            
        }
     
        $token = $user->createToken('auth_token')->plainTextToken;
         dd($token);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
    public function userImagestore(Request $request)
    {
        // Validate the input
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            // Store the image
            $path = $image->store('user_images', 'public');
            $userImage = UserImage::create([
                'user_id' => $user->id,
                'image_path' => $path,
            ]);

            $uploadedImages[] = $userImage;
        }

        return response()->json([
            'message' => 'Images uploaded successfully!',
            'images' => $uploadedImages,
        ]);
    }
}
