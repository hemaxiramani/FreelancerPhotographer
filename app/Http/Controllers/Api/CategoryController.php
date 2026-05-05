<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/categories
     */
    public function index()
    {
        $categories = Category::active()
                              ->orderBy('name')
                              ->get(['id', 'name']);

        return $this->success($categories);
    }

    /**
     * POST /api/v1/profile/categories
     * Sync selected categories with charge_per_day
     *
     * Body: { categories: [ { id: 1, charge_per_day: 5000 }, { id: 3, charge_per_day: 8000 } ] }
     */
    public function syncMyCategories(Request $request)
    {
        $validated = $request->validate([
            'categories'                 => 'required|array',
            'categories.*.id'            => 'required|exists:categories,id',
            'categories.*.charge_per_day' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();

        // Build sync array: [ category_id => ['charge_per_day' => value] ]
        $syncData = [];
        foreach ($validated['categories'] as $cat) {
            $syncData[$cat['id']] = [
                'charge_per_day' => $cat['charge_per_day'] ?? null,
            ];
        }

        $user->categories()->sync($syncData);

        $user->load('categories');

        return $this->success($user->categories, 'Categories updated');
    }
}
