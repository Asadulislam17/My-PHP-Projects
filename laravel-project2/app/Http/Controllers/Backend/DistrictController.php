<?php

namespace App\Http\Controllers\Backend;

use App\Models\District;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DistrictController extends Controller
{

    public function index()
    {

        $districts = District::all();

        return view('backend.districts.index', compact('districts'));
    }

    public function create()
    {
        return view('backend.districts.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:districts,name|max:100',
        ]);
        $district = new District;

        $district->name = $request->name;

        $district->save();

        return redirect()->route('districts.index')->with('success', 'District created successfully!');
    }


    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $district = District::findOrFail($id);

        $district->name = $request->name;

        $district->save();

        return redirect()->route('districts.index')->with('success', 'District updated successfully!');
    }



    public function destroy($id)
    {
        $district = District::findOrFail($id);
        $district->delete();
        return redirect()->route('districts.index')->with('success', 'District deleted successfully!');
    }
}
