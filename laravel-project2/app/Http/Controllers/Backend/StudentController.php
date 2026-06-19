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
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // ছবির সিকিউরিটি ভ্যালিডেশন
            'subject.*'  => 'nullable|string|max:50',
        ]);

        // ২. নতুন স্টুডেন্ট অবজেক্ট তৈরি
        $student = new Student;
        $student->first_name = $request->first_name;
        $student->last_name  = $request->last_name;
        $student->email      = $request->email;
        $student->phone      = $request->phone;
        $student->gender     = $request->gender;
        $student->district   = $request->district;

        // === 📷 ছবি আপলোড করার মূল লজিক শুরু ===
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

        // ১. ডাটা ভ্যালিডেশন (ছবির ফরম্যাট এবং সাইজ চেক সহ)
        $request->validate([
            'first_name' => 'required|string|max:50|min:3',
            'last_name'  => 'required|string|max:50',
            'email'      => 'required|email|max:100|unique:students,email,' . $id,
            'phone'      => 'nullable|string|max:15',
            'gender'     => 'required|in:Male,Female,Other',
            'district'   => 'required|string|max:50',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // ২ এমবি সাইজ লিমিট
            'subject.*'  => 'nullable|string|max:50',
        ]);

        // ২. ফিল্ডের ডাটা পরিবর্তন
        $student->first_name = $request->first_name;
        $student->last_name  = $request->last_name;
        $student->email      = $request->email;
        $student->phone      = $request->phone;
        $student->gender     = $request->gender;
        $student->district   = $request->district;

        // === 📷 এডিট পেজে ছবি পরিবর্তন করার লজিক শুরু ===
        if ($request->hasFile('image')) {

            // যদি ডাটাবেজে আগে কোনো ছবি থাকে, তবে নতুন ছবি সেভ করার আগে পুরোনো ছবিটি লোকাল ফোল্ডার থেকে ডিলিট করে দেবো
            if ($student->image && file_exists(public_path($student->image))) {
                unlink(public_path($student->image));
            }

            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/students'), $filename);

            // নতুন ছবির পাথ ডাটাবেজে সেভ হলো
            $student->image = 'uploads/students/' . $filename;
        }
        // === 📷 ছবি পরিবর্তন করার লজিক শেষ ===

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
