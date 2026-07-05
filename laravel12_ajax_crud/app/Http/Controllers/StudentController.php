<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    
    public function index(Request $request)
    {
        $students = Student::latest()->get();
        return view('students.index', compact('students'));
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:students,email',
            'address' => 'nullable|string',
        ]);

        $student = new Student();
        $student->name = $request->name;
        $student->email = $request->email;
        $student->address = $request->address;
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Student saved successfully!',
            'student' => $student
        ]);
    }

   
    public function edit(Student $student)
    {
        return response()->json($student);
    }

   
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:students,email,' . $student->id,
            'address' => 'nullable|string',
        ]);

        $student->name = $request->name;
        $student->email = $request->email;
        $student->address = $request->address;
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully!',
            'student' => $student
        ]);
    }

   
    public function destroy(Student $student)
    {
        $student->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully!'
        ]);
    }
}






// <?php

// namespace App\Http\Controllers;

// use App\Models\Student;
// use Illuminate\Http\Request;

// class StudentController extends Controller
// {
//     // ১. সব স্টুডেন্ট ডাটা টেবিলে পাঠানো (Read)
//     public function index(Request $request)
//     {
//         if ($request->ajax()) {
//             return response()->json(Student::latest()->get());
//         }
//         return view('students.index');
//     }

//     // ২. নতুন ডাটা save() মেথড দিয়ে সেভ করা (Create)
//     public function store(Request $request)
//     {
//         $request->validate([
//             'name'    => 'required|string|max:255',
//             'email'   => 'required|email|unique:students,email',
//             'address' => 'nullable|string',
//         ]);

//         $student = new Student();
//         $student->name = $request->name;
//         $student->email = $request->email;
//         $student->address = $request->address;
//         $student->save();

//         return response()->json([
//             'success' => 'Student saved successfully!',
//             'redirect_url' => '/student'
//         ]);
//     }

//     // ৩. এডিটের জন্য নির্দিষ্ট স্টুডেন্টের ডাটা পাঠানো (Edit)
//     public function edit(Student $student)
//     {
//         return response()->json($student);
//     }

//     // ৪. ডাটা আপডেট করা (Update)
//     public function update(Request $request, Student $student)
//     {
//         $request->validate([
//             'name'    => 'required|string|max:255',
//             'email'   => 'required|email|unique:students,email,' . $student->id,
//             'address' => 'nullable|string',
//         ]);

//         $student->name = $request->name;
//         $student->email = $request->email;
//         $student->address = $request->address;
//         $student->save();

//         return response()->json([
//             'success' => 'Student updated successfully!',
//             'redirect_url' => '/student'
//         ]);
//     }

//     // ৫. স্টুডেন্ট ডিলিট করা (Delete)
//     public function destroy(Student $student)
//     {
//         $student->delete();
//         return response()->json(['success' => 'Student deleted successfully!']);
//     }
// }
