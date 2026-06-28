<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  
    public function index()
    {
        
        $products = Product::with('category')->latest()->get(); 
        
        return view('backend.product.index', compact('products'));
    }

    
    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();
    
        return view('backend.product.create', compact('categories'));
    }

    
    public function store(Request $request)
    {
       
        $request->validate([
            'name'        => 'required|max:60',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|max:250',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price'       => 'required|numeric',
            'status'      => 'required|string|in:In Stock,Out of Stock,Pre-Order',
        ]);

        $data = $request->all();

      
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $data['image'] = 'uploads/products/' . $imageName;
        }

     
        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

   
    public function show(Product $product)
    {
        return view('backend.product.show', compact('product'));
    }

   
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name', 'asc')->get();
        
        return view('backend.product.edit', compact('product', 'categories'));
    }

  
    public function update(Request $request, Product $product)
    {
        
        $request->validate([
            'name'        => 'required|max:60',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|max:250',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price'       => 'required|numeric',
            'status'      => 'required|string|in:In Stock,Out of Stock,Pre-Order',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
            
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads/products'), $imageName);
            $data['image'] = 'uploads/products/' . $imageName;
        }

       
        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

 
    public function destroy(Product $product)
    {
        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }
}
