<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FaceRecognitionController extends Controller
{
    public function compareFaces(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'error' => $validator->errors()->first()
            ], 422);
        }
        if($req->file('images'))
        {
            $image=$req->image;
            $imagePath = $image->store('user_images/temp', 'public');
        }
        $client = new RekognitionClient([
            'region'    => env('AWS_DEFAULT_REGION'),
            'version'   => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);
        $path = storage_path('app/public/user_images');
        $tempPath = $imagePath;
        $sourceImagePath = $tempPath;
        $allImages = File::files($path);
        $matches = [];

        foreach ($allImages as $targetImage) {
            $targetImagePath = storage_path("app/public/user_images/{$targetImage->getFilename()}");

            $result = $client->compareFaces([
                'SourceImage' => ['Bytes' => file_get_contents($sourceImagePath)],
                'TargetImage' => ['Bytes' => file_get_contents($targetImagePath)],
                'SimilarityThreshold' => 80,
            ]);

            if (!empty($result['FaceMatches'])) {
                $matches[] = $targetImage->getFilename();
            }
        }
        return view('faces', compact('matches'));
    }
}
