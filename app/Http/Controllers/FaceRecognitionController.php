<?php

namespace App\Http\Controllers;

use Aws\Rekognition\RekognitionClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FaceRecognitionController extends Controller
{
    public function compareFaces()
    {
        // Initialize Rekognition Client
        $client = new RekognitionClient([
            'region'    => env('AWS_DEFAULT_REGION'),
            'version'   => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);

        $path = storage_path('app/public/user_images');
        $tempPath = storage_path('app/public/user_images/temp/ali.jpeg');
        $sourceImagePath = $tempPath; // Directly assign the path
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
                $matches[] = $targetImage->getFilename(); // Collect matched images
            }
        }
        // dd($matches);
        return view('faces' , compact('matches'));
        // return response()->json(['matches' => $matches]);
    }
}
