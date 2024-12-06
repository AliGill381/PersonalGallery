<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
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
        // dd(Hash::check($credentials['password'], $user->password));
        if (!$user) {
            return response()->json([
                'message' => 'Invalid Email',
                'success' => false
            ], 401);
        }
        if (!Hash::check($credentials['password'], $user->password)) {
            // dd('adfasdfa');
            return response()->json(['message' => 'Invalid credentials'], 401);
        } else {
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        }
    }
    public function userImageStore(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'eventName' => 'required|string|max:255',
            'venueName' => 'required|string|max:255',
            'contactNo' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Check if validation fails
        if ($validator->fails()) {
            // Return the first validation error
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error' => $validator->errors()->first()  // Get the first validation error
            ], 422);
        }
        try {
            $user = Auth::user();
            $uploadedImages = [];

            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('user_images', 'public');
                $userImage = UserImage::create([
                    'user_id' => $user->id,
                    'image_path' => $imagePath,
                    'event_name' => $request->input('eventName'),
                    'venue_name' => $request->input('venueName'),
                    'contact_no' => $request->input('contactNo'),
                    'description' => $request->input('description'),
                ]);
                $uploadedImages[] = [
                    'id' => $userImage->id,
                    'image_path' => asset('storage/' . $userImage->image_path),
                    'event_name' => $userImage->event_name,
                    'venue_name' => $userImage->venue_name,
                    'contact_no' => $userImage->contact_no,
                    'description' => $userImage->description,
                ];
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Images uploaded successfully!',
                'images' => $uploadedImages,
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions and return a failure response
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to upload images. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
