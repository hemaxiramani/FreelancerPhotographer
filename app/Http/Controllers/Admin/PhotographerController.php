<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;

class PhotographerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::photographers()
            ->with(['country', 'state', 'city', 'photographerProfile', 'categories']);

        // Filter by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by country
        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        // Filter by state
        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        // Filter by city (via work_cities or base city)
        if ($request->filled('city_id')) {
            $cityId = $request->city_id;
            $query->where(function ($q) use ($cityId) {
                $q->where('city_id', $cityId)
                  ->orWhereHas('workCities', fn($wc) => $wc->where('city_id', $cityId));
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }

        // Filter by charge range
        if ($request->filled('charge_min') || $request->filled('charge_max')) {
            $query->whereHas('photographerProfile', function ($q) use ($request) {
                if ($request->filled('charge_min')) {
                    $q->where('default_charge', '>=', $request->charge_min);
                }
                if ($request->filled('charge_max')) {
                    $q->where('default_charge', '<=', $request->charge_max);
                }
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sort = $request->input('sort');
        $dir = $request->input('dir', 'asc');
        $dir = in_array($dir, ['asc', 'desc']) ? $dir : 'asc';

        if ($sort === 'name') {
            $query->orderBy('name', $dir);
        } elseif ($sort === 'charge') {
            $query->leftJoin('photographer_profiles', 'users.id', '=', 'photographer_profiles.user_id')
                  ->orderBy('photographer_profiles.default_charge', $dir)
                  ->select('users.*');
        } elseif ($sort === 'date') {
            $query->orderBy('created_at', $dir);
        } else {
            $query->latest();
        }

        $photographers = $query->paginate(15)->withQueryString();
        $countries = Country::active()->orderBy('name')->get();
        $categories = Category::active()->orderBy('name')->get();

        // Pass states/cities for filter dropdowns when country/state is selected
        $states = $request->filled('country_id')
            ? State::where('country_id', $request->country_id)->active()->orderBy('name')->get()
            : collect();

        $cities = $request->filled('state_id')
            ? City::where('state_id', $request->state_id)->active()->orderBy('name')->get()
            : collect();

        return view('admin.photographers.index', compact('photographers', 'countries', 'categories', 'states', 'cities'));
    }

    public function show($id)
    {
        $photographer = User::photographers()
            ->with([
                'country', 'state', 'city',
                'photographerProfile',
                'categories',
                'cameraKits',
                'workCities.country', 'workCities.state', 'workCities.city',
                'hireRequests' => fn($q) => $q->latest()->take(10),
                'hireRequests.city',
            ])
            ->findOrFail($id);

        return view('admin.photographers.show', compact('photographer'));
    }

    public function block($id)
    {
        $user = User::photographers()->findOrFail($id);
        $user->update(['status' => 'blocked']);
        return back()->with('success', $user->name . ' has been blocked.');
    }

    public function unblock($id)
    {
        $user = User::photographers()->findOrFail($id);
        $user->update(['status' => 'active']);
        return back()->with('success', $user->name . ' has been unblocked.');
    }

    public function destroy($id)
    {
        $user = User::photographers()->findOrFail($id);
        $name = $user->name;
        $user->delete();
        return redirect()->route('admin.photographers')->with('success', $name . ' has been removed.');
    }
}
