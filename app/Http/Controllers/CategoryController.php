<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index', [
            'categories' => Category::withCount('products')->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return redirect()->route('categories.index')->with('success', 'Categoria creada.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('categories.index')->with('success', 'Categoria actualizada.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors('No se puede eliminar una categoria con productos.');
        }

        $category->delete();

        return back()->with('success', 'Categoria eliminada.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.($category?->id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]) + ['active' => false];
    }
}
