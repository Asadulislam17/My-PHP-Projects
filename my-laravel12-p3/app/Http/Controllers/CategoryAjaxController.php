<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CategoryAjaxController extends Controller
{
    
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Category::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<button data-id="'.$row->id.'" class="edit btn btn-primary btn-sm editCategory me-1">Edit</button>';
                    $btn .= '<button data-id="'.$row->id.'" class="btn btn-danger btn-sm deleteCategory">Delete</button>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('backend.category.categories');
    }

    // ২. ক্যাটাগরি ক্রিয়েট ও আপডেট করা
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$request->category_id,
        ]);

        Category::updateOrCreate(
            ['id' => $request->category_id],
            ['name' => $request->name]
        );        

        return response()->json(['success' => 'Category saved successfully.']);
    }

    // ৩. এডিটের জন্য নির্দিষ্ট ডাটা রিভ করা
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    // ৪. ক্যাটাগরি ডিলিট করা
    public function destroy($id)
    {
        Category::findOrFail($id)->delete();
        return response()->json(['success' => 'Category deleted successfully.']);
    }
}

