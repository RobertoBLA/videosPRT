<?php

namespace App\Http\Controllers;

use App\Models\video;
use App\Models\config;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VideoController extends Controller
{

    /**
     * Display a listing of all items.
     */
    public function index()
    {
        $videos = video::orderBy('order')->get();
        return view('main', compact('videos'));
    }

    public function getVideos()
    {
        try {
            $videos = Video::orderBy('order')
                ->select('id', 'name', DB::raw("('/storage/' || url) as path"), 'order', 'status')
                ->get();
    
            return response()->json($videos);
        } catch (\Exception $e) {
            Log::error('Failed to fetch videos: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch videos'], 500);
        }
    }
    


    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'video' => 'required|file|mimes:mp4,mov,avi|max:20480',
            ]);

            $videoFile = $request->file('video');
            $fileName = $validatedData['name'] . '.' . $videoFile->getClientOriginalExtension();
            $validatedData['url'] = $videoFile->storeAs('videos', $fileName, 'public');
            $validatedData['status'] = true;
            $validatedData['order'] = video::max('order') + 1;

            $video = video::create($validatedData);

            return response()->json([
                'message' => 'Video created successfully',
                'video' => [
                    'id' => $video->id,
                    'name' => $video->name,
                    'url' => $video->url,
                    'order' => $video->order,
                    'status' => $video->status
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getConfig()
    {
        try {
            $config = config::first();

            if (!$config) {
                return response()->json([
                    'autoplay' => true,
                    'loop' => true,
                    'auto_next' => false,
                ]);
            }

            return response()->json([
                'autoplay' => $config->autoplay,
                'loop' => $config->loop,
                'auto_next' => $config->auto_next,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updatePlayerConfig(Request $request)
    {
        try {
            // Validate the inputs directly for '0' and '1' values
            $validatedData = $request->validate([
                'autoplay' => 'required|in:0,1',
                'loop' => 'required|in:0,1',
                'auto_next' => 'required|in:0,1',
            ]);

            $config = Config::firstOrCreate([]);
            $config->update($validatedData);

            return response()->json(['message' => 'Configuration updated successfully.']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateOrder(Request $request)
    {
        $videoOrder = $request->input('order'); // Array of video IDs and their new order
    
        foreach ($videoOrder as $index => $videoData) {
            // Find the video by ID
            $video = Video::find($videoData['id']);
            
            // Update the video's order and status (optional)
            $video->order = $videoData['order'];
            $video->status = $videoData['status'] ?? $video->status; // If status is provided, update it
    
            $video->save(); // Save the changes
        }
    
        return response()->json(['message' => 'Video order updated successfully']);
    }
    

    public function updateStatus(Request $request, $videoId)
    {
        $video = Video::findOrFail($videoId);
        $video->status = $request->input('status');
        $video->save();
    
        return response()->json(['message' => 'Status updated successfully']);
    }
    
}
