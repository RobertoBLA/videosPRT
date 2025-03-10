<?php

namespace App\Http\Controllers;

use App\Models\video;
use App\Models\config;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            // Fetch all videos from the database
            $videos = Video::select('url')->get(); // Assuming you have a 'url' column for video URLs
            return view('main', compact('videos'));
    
        
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

            // Generate a custom file name using the 'name' field in the request
            $fileName = $validatedData['name'] . '.' . $videoFile->getClientOriginalExtension();

            // Store the file with the custom name
            $validatedData['url'] = $videoFile->storeAs('videos', $fileName, 'public');

            // Set default values for checkboxes
            $validatedData['status'] = true;

            // Assign a default order if needed
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
                    'auto_next' => true,
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
        try {
            $orderData = $request->input('order');

            foreach ($orderData as $item) {
                video::where('id', $item['id'])->update(['order' => $item['order']]);
            }

            return response()->json([
                'message' => 'Order updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updateStatus(Request $request, $id)
    {
        $video = video::findOrFail($id);
        $video->status = $request->status;
        $video->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'item' => [
                'status' => $video->status,
            ]
        ]);
    }
}
