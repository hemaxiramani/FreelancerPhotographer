<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/locations/countries
     */
    public function countries()
    {
        $countries = Country::active()
                            ->orderBy('name')
                            ->get(['id', 'name', 'iso2']);

        return $this->success($countries);
    }

    /**
     * GET /api/v1/locations/states?country_id={id}
     */
    public function states(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $states = State::active()
                       ->where('country_id', $request->country_id)
                       ->orderBy('name')
                       ->get(['id', 'country_id', 'name', 'state_code']);

        return $this->success($states);
    }

    /**
     * GET /api/v1/locations/cities?state_id={id}&search={q}
     */
    public function cities(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'search'   => 'nullable|string|max:100',
        ]);

        $query = City::active()
                     ->where('state_id', $request->state_id);

        if ($request->filled('search')) {
            $query->where('name', 'like', $request->search . '%');
        }

        $cities = $query->orderBy('name')
                        ->limit(100)
                        ->get(['id', 'state_id', 'name']);

        return $this->success($cities);
    }
}
