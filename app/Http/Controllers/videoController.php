<?php

namespace App\Http\Controllers;

use App\Models\video;
use App\Models\config;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException as ValidationException;

class VideoController extends Controller
{

    /**
     * Display a listing of all items.
     */
    public function index()
    {
        $videos = video::all();
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
                    'status' => $video->order
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

    public function updatePlayerConfig(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'autoplay' => 'required|boolean',
                'loop' => 'required|boolean',
                'auto_next' => 'required|boolean',
            ]);

            $config = config::first();

            if ($config) {
                $config->update($validatedData);
            } else {
                $config = config::create($validatedData);
            }

            return response()->json([
                'message' => 'Media player settings updated successfully!',
                'config' => $config,
            ]);
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
