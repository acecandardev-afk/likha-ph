<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends AdminController
{
    /**
     * Display all categories.
     */
    public function index()
    {
        $categories = Category::withCount('products')->get();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Store a new category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:10',
        ]);

        Category::create($validated);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Update a category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete category with existing products.']);
        }

        AuditLog::record('category.removed', 'Removed the '.$category->name.' category.', $category);

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}
