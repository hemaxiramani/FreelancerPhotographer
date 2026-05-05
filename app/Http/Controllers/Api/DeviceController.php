<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class DeviceController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/devices
     */
    public function index(Request $request)
    {
        $devices = $request->user()
                           ->deviceTokens()
                           ->orderBy('last_active_at', 'desc')
                           ->get(['id', 'device_name', 'device_type', 'last_active_at', 'created_at']);

        return $this->success($devices);
    }

    /**
     * DELETE /api/v1/devices/{id}
     * Remote logout from another device
     */
    public function destroy(Request $request, $id)
    {
        $deviceToken = $request->user()->deviceTokens()->find($id);

        if (! $deviceToken) {
            return $this->notFound('Device not found');
        }

        // Don't allow deleting current device (use logout instead)
        $currentTokenId = $request->user()->currentAccessToken()->id;
        if ($deviceToken->access_token_id === $currentTokenId) {
            return $this->error('Use logout to remove current device', 422);
        }

        // Revoke the Sanctum token for that device
        if ($deviceToken->access_token_id) {
            PersonalAccessToken::where('id', $deviceToken->access_token_id)->delete();
        }

        // Delete device token record
        $deviceToken->delete();

        return $this->success(null, 'Device removed successfully');
    }
}
