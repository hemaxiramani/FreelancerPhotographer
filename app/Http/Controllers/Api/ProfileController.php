<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/profile
     */
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'country',
            'state',
            'city',
            'photographerProfile',
            'categories',
            'cameraKits',
            'workCities.country',
            'workCities.state',
            'workCities.city',
        ]);

        return $this->success($user);
    }

    /**
     * PUT /api/v1/profile
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:100',
            'phone'          => 'sometimes|nullable|string|max:20',
            'country_id'     => 'sometimes|exists:countries,id',
            'state_id'       => 'sometimes|exists:states,id',
            'city_id'        => 'sometimes|exists:cities,id',
            'bio'            => 'sometimes|nullable|string|max:1000',
            'experience'     => 'sometimes|nullable|string|max:100',
            'default_charge' => 'sometimes|nullable|numeric|min:0',
            'instagram_link' => 'sometimes|nullable|url|max:500',
            'facebook_link'  => 'sometimes|nullable|url|max:500',
            'portfolio_link' => 'sometimes|nullable|url|max:500',
        ]);

        $user = $request->user();

        // Update user fields
        $userFields = array_intersect_key($validated, array_flip([
            'name', 'phone', 'country_id', 'state_id', 'city_id',
        ]));
        if (! empty($userFields)) {
            $user->update($userFields);
        }

        // Update photographer profile fields
        $profileFields = array_intersect_key($validated, array_flip([
            'bio', 'experience', 'default_charge', 'instagram_link', 'facebook_link', 'portfolio_link',
        ]));
        if (! empty($profileFields)) {
            $user->photographerProfile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileFields
            );
        }

        $user->load(['country', 'state', 'city', 'photographerProfile']);

        return $this->success($user, 'Profile updated');
    }

    /**
     * POST /api/v1/profile/photo
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        // Delete old photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        // Store new photo
        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo' => $path]);

        return $this->success([
            'profile_photo' => $path,
            'url'           => Storage::disk('public')->url($path),
        ], 'Profile photo updated');
    }
}
