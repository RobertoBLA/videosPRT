<?php

namespace App\Http\Controllers;

use App\Models\video;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException as ValidationValidationException;

abstract class Controller
{
    // Placeholder image URL (can be moved to config if needed)
    const PLACEHOLDER_IMAGE = 'https://static.thenounproject.com/png/1269202-200.png';

    /**
     * Display a listing of all items.
     */
    public function index()
    {
        $items = video::all();
        return view('main', compact('videos'));
    }



    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'video' => 'required|file|mimes:mp4,mov,avi|max:20480',
        ]);

        if ($request->hasFile('video')) {
            $validatedData['url'] = $request->file('video')->store('videos', 'public');
        }

        // Set default values for checkboxes
        $validatedData['autoplay'] = true;
        $validatedData['loop'] = true;
        $validatedData['auto_next'] = true;

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
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validatedData = $request->validate([
                'autoplay' => 'required|boolean',
                'loop' => 'required|boolean',
                'auto_next' => 'required|boolean',
            ]);

            $video = video::findOrFail($id);
            $video->update($validatedData);

            // Return a success response
            return response()->json([
                'message' => 'Item updated successfully!',
                'item' => [
                    'id' => $video->id,
                    'autoplay' => $video->autoplay,
                    'loop' => $video->loop,
                    'auto_next' => $video->auto_next
                ],
            ]);
        } catch (ValidationValidationException $e) {
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
