<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\District;
use App\Models\Subject;


class StudentController extends Controller
{
    public function index()
    {
        // $students = Student::latest()->get(); 
        $students = Student::oldest()->get();
        return view('backend.students.index', compact('students'));
    }

    public function create()
    {

        $districts = District::all();
        $subjects = Subject::all();
        return view('backend.students.create', compact('districts', 'subjects'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'first_name' => 'required|string|max:50|min:3',
            'last_name'  => 'required|string|max:50',
            'email'      => 'required|email|max:100|unique:students,email',
            'phone'      => 'nullable|string|max:15',
            'gender'     => 'required|in:Male,Female,Other',
            'district'   => 'required|string|max:50',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subject.*'  => 'nullable|string|max:50',
        ]);

        
        $student = new Student;
        $student->first_name = $request->first_name;
        $student->last_name  = $request->last_name;
        $student->email      = $request->email;
        $student->phone      = $request->phone;
        $student->gender     = $request->gender;
        $student->district   = $request->district;

      
        if ($request->hasFile('image')) {
            $file = $request->file('image');


            $filename = time() . '.' . $file->getClientOriginalExtension();


            $file->move(public_path('uploads/students'), $filename);


            $student->image = 'uploads/students/' . $filename;
        }

        $subjects = $request->subject ?? [];
        $student->subject = implode(",", $subjects);

        $student->save();

        return redirect()->route('students.index')->with('success', 'Student created successfully with image!');
    }


    public function show(string $id)
    {
        $student = Student::findOrFail($id);
        return view('backend.students.show', compact('student'));
    }

    public function edit(string $id)
    {
        $student = Student::findOrFail($id);
        $districts = District::all();
        $subjects = Subject::all();
        return view('backend.students.edit', compact('student', 'districts', 'subjects'));
    }

    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);

        
        $request->validate([
            'first_name' => 'required|string|max:50|min:3',
            'last_name'  => 'required|string|max:50',
            'email'      => 'required|email|max:100|unique:students,email,' . $id,
            'phone'      => 'nullable|string|max:15',
            'gender'     => 'required|in:Male,Female,Other',
            'district'   => 'required|string|max:50',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'subject.*'  => 'nullable|string|max:50',
        ]);

        
        $student->first_name = $request->first_name;
        $student->last_name  = $request->last_name;
        $student->email      = $request->email;
        $student->phone      = $request->phone;
        $student->gender     = $request->gender;
        $student->district   = $request->district;

        
        if ($request->hasFile('image')) {

           
            if ($student->image && file_exists(public_path($student->image))) {
                unlink(public_path($student->image));
            }

            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/students'), $filename);

            
            $student->image = 'uploads/students/' . $filename;
        }
       
        $subjects = $request->subject ?? [];
        $student->subject = implode(",", $subjects);

        $student->save();

        return redirect()->route('students.index')->with('success', 'Student updated successfully with image!');
    }

    public function destroy(string $id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully!');
    }
}
