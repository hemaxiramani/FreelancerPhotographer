<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('photographers')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        Category::create($validated);

        return back()->with('success', "Category \"{$validated['name']}\" created.");
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $id,
        ]);

        $category->update($validated);

        return back()->with('success', "Category updated to \"{$validated['name']}\".");
    }

    public function toggle($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['status' => ! $category->status]);
        $status = $category->status ? 'activated' : 'deactivated';

        return back()->with('success', "Category \"{$category->name}\" has been $status.");
    }
}
