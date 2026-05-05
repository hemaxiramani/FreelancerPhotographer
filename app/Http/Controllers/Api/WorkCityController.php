<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WorkCityController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/profile/work-cities
     */
    public function index(Request $request)
    {
        $cities = $request->user()
                          ->workCities()
                          ->with(['country:id,name', 'state:id,name', 'city:id,name'])
                          ->get();

        return $this->success($cities);
    }

    /**
     * POST /api/v1/profile/work-cities
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'state_id'   => 'required|exists:states,id',
            'city_id'    => 'required|exists:cities,id',
        ]);

        $user = $request->user();

        // Check duplicate
        $exists = $user->workCities()->where('city_id', $validated['city_id'])->exists();
        if ($exists) {
            return $this->error('This city is already in your work cities list', 422);
        }

        $workCity = $user->workCities()->create($validated);
        $workCity->load(['country:id,name', 'state:id,name', 'city:id,name']);

        return $this->created($workCity, 'Work city added');
    }

    /**
     * DELETE /api/v1/profile/work-cities/{id}
     */
    public function destroy(Request $request, $id)
    {
        $workCity = $request->user()->workCities()->find($id);

        if (! $workCity) {
            return $this->notFound('Work city not found');
        }

        $workCity->delete();

        return $this->success(null, 'Work city removed');
    }
}
