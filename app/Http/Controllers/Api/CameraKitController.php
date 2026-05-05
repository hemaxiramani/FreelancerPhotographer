<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CameraKitController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/profile/camera-kit
     */
    public function index(Request $request)
    {
        $items = $request->user()
                         ->cameraKits()
                         ->orderBy('created_at', 'desc')
                         ->get(['id', 'item_name', 'created_at']);

        return $this->success($items);
    }

    /**
     * POST /api/v1/profile/camera-kit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
        ]);

        $item = $request->user()->cameraKits()->create($validated);

        return $this->created($item, 'Kit item added');
    }

    /**
     * DELETE /api/v1/profile/camera-kit/{id}
     */
    public function destroy(Request $request, $id)
    {
        $item = $request->user()->cameraKits()->find($id);

        if (! $item) {
            return $this->notFound('Kit item not found');
        }

        $item->delete();

        return $this->success(null, 'Kit item removed');
    }
}
