<?php

namespace App\Http\Controllers;

use App\Models\video;
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
            $validatedData['autoplay'] = true;
            $validatedData['loop'] = true;
            $validatedData['auto_next'] = true;
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
                    'autoplay' => $video->autoplay,
                    'loop' => $video->loop,
                    'auto_next' => $video->auto_next,
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

    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'autoplay' => 'required|boolean',
                'loop' => 'required|boolean',
                'auto_next' => 'required|boolean',
                'order' => 'required|integer',  // Ensure 'order' is part of the request
            ]);

            // Find the video by ID and update it
            $video = video::findOrFail($id);
            $video->update($validatedData);  // This will include the order field

            // Return a success response
            return response()->json([
                'message' => 'Video updated successfully!',
                'item' => [
                    'id' => $video->id,
                    'name' => $video->name,
                    'autoplay' => $video->autoplay,
                    'loop' => $video->loop,
                    'auto_next' => $video->auto_next,
                    'order' => $video->order,
                ],
            ]);
        } catch (ValidationException $e) {
            // Return validation errors as JSON
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updateStatus(Request $request, $id)
    {
        $video = video::findOrFail($id);
        $video->status = $request->status; // Store as 0 or 1
        $video->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'item' => [
                'status' => $video->status,
            ]
        ]);
    }

    public function reorder(Request $request)
    {
        $orderData = $request->input('order');

        foreach ($orderData as $item) {
            video::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json([
            'message' => 'Order updated successfully',
        ]);
    }
}
