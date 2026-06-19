<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{

    public function index()
    {
        $subjects = Subject::all();
        return view('backend.subjects.index', compact('subjects'));
    }


    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|unique:subjects,name|max:100',
        ]);

        $subject = new Subject;
        $subject->name = $request->name;
        $subject->save();

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully!');
    }


    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $subject->name = $request->name;
        $subject->save();

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully!');
    }


    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully!');
    }
}
