<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    
    public function index(Request $request)
    {
        $products = Product::latest()->get();
        return view('products.index', compact('products'));
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        
        return redirect()->route('products.index')->with('success', 'Product saved successfully!');
    }

  
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

  
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

      
        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    
    public function destroy(Product $product)
    {
        $product->delete();
        
        return redirect()->route('products.index')->with('warning', 'Product deleted successfully!');
    }
}
