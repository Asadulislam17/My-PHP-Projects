<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        
        $categories = Category::latest()->get();

        return view('backend.category.index', compact('categories'));
    }

    
    public function create()
    {
        return view('backend.category.create');
    }

   
    public function store(Request $request)
    {
        
        $request->validate([
            'name' => 'required|string|unique:categories,name|max:100',
        ]);

        $category = new Category;
        $category->name = $request->name;
        $category->save();

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    
    public function show(string $id)
    {
        //
    }

   
    public function edit(string $id)
    {
        $category = Category::findOrFail($id);
        return view('backend.category.edit', compact('category'));
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
        ]);

        $category->name = $request->name;
        $category->save();

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }
}
