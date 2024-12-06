<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserEvent;
use App\Models\UserImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_no' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error' => $validator->errors()->first()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_no' => $request->phone_no,
            'password' => Hash::make($request->password),
        ]);


        $token = $user->createToken('auth_token')->plainTextToken;
        $user->remember_token = $token;
        $user->save();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ],201);
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
            ],200);
        }
    }

    public function storeEventAndImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'eventName' => 'required|string|max:255',
            'venueName' => 'required|string|max:255',
            'contactNo' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image files
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error' => $validator->errors()->first()
            ], 422);
        }

        try {
            $user = Auth::user();
            $userEvent = UserEvent::create([
                'user_id' => $user->id,
                'eventName' => $request->input('eventName'),
                'venueName' => $request->input('venueName'),
                'contactNo' => $request->input('contactNo'),
                'description' => $request->input('description'),
            ]);

            $uploadedImages = [];
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('user_images', 'public');
                $userImage = UserImage::create([
                    'user_event_id' => $userEvent->id, // Link to the event
                    'image' => $imagePath,
                ]);
                $uploadedImages[] = [
                    'id' => $userImage->id,
                    'image_path' => asset('storage/' . $userImage->image), // Get the URL of the stored image
                ];
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Event and images uploaded successfully!',
                'event' => [
                    'id' => $userEvent->id,
                    'eventName' => $userEvent->eventName,
                    'venueName' => $userEvent->venueName,
                    'contactNo' => $userEvent->contactNo,
                    'description' => $userEvent->description,
                ],
                'images' => $uploadedImages,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store event and images. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
