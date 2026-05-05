<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::withCount('states')->orderBy('name')->get();

        $selectedCountry = null;
        $states = collect();
        $selectedState = null;
        $cities = collect();

        if ($request->filled('country_id')) {
            $selectedCountry = Country::find($request->country_id);
            $states = State::where('country_id', $request->country_id)
                           ->withCount('cities')
                           ->orderBy('name')
                           ->get();
        }

        if ($request->filled('state_id')) {
            $selectedState = State::find($request->state_id);
            $cities = City::where('state_id', $request->state_id)
                          ->orderBy('name')
                          ->get();
        }

        return view('admin.locations.index', compact('countries', 'selectedCountry', 'states', 'selectedState', 'cities'));
    }

    public function storeCity(Request $request)
    {
        $validated = $request->validate([
            'state_id' => 'required|exists:states,id',
            'name'     => 'required|string|max:150',
        ]);

        City::create([
            'state_id'      => $validated['state_id'],
            'name'          => $validated['name'],
            'is_user_added' => true,
            'status'        => true,
        ]);

        $state = State::find($validated['state_id']);

        return back()->with('success', "City \"{$validated['name']}\" added to {$state->name}.");
    }

    public function toggle($type, $id)
    {
        $model = match ($type) {
            'country' => Country::findOrFail($id),
            'state'   => State::findOrFail($id),
            'city'    => City::findOrFail($id),
            default   => abort(404),
        };

        $model->update(['status' => ! $model->status]);
        $status = $model->status ? 'activated' : 'deactivated';

        return back()->with('success', ucfirst($type) . " \"{$model->name}\" has been $status.");
    }

    // AJAX endpoints for cascading dropdowns
    public function getStates(Request $request)
    {
        $states = State::active()
            ->where('country_id', $request->country_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($states);
    }

    public function getCities(Request $request)
    {
        $cities = City::active()
            ->where('state_id', $request->state_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }
}
