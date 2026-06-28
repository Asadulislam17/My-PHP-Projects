@extends('backend.master')
{{-- @push('page_css')
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@endpush --}}
@section('main_content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-table" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Data</p>
                        <h1 class="h3 mb-1">Students</h1>
                        <p class="text-muted mb-0">Use responsive, searchable tables for operational records.</p>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <section class="panel">
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('students.create') }}" class="btn btn-primary">Add New Student</a>
                </div>

                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Students
                                Table</span></h2>
                        <p class="text-muted mb-0">Searchable responsive table for student data.</p>
                    </div>
                    <input class="form-control form-control-sm table-search" type="search" placeholder="Search students"
                        data-table-search="ordersTable" aria-label="Search students">
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover" id="ordersTable" data-searchable-table>
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>District</th>
                                <th>Subject</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $key => $student)
                                <tr data-href="{{ route('students.show', $student->id) }}" style="cursor: pointer;">

                                    <td class="fw-semibold">{{ $student->id }}</td>

                                    <td class="ignore-click">
                                        @if ($student->image && file_exists(public_path($student->image)))
                                            
                                            <img src="{{ asset($student->image) }}" alt="Student"
                                                class="rounded-circle border"
                                                style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            
                                            <div class="rounded-circle bg-light d-flex align-middle justify-content-center border"
                                                style="width: 40px; height: 40px; line-height: 40px;">
                                                <i class="bi bi-person text-secondary" style="font-size: 20px;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                    <td>{{ $student->email }}</td>
                                    <td>{{ $student->phone ?? 'N/A' }}</td>
                                    <td>{{ $student->gender }}</td>
                                    <td>{{ $student->district }}</td>
                                    <td>{{ $student->subject ?? 'N/A' }}</td>

                                    <td class="text-end ignore-click">
                                        <div class="d-flex justify-content-end gap-2">

                                           
                                            <a href="{{ route('students.show', $student->id) }}"
                                                class="btn btn-info btn-sm text-white">
                                                <i class="bi bi-eye"></i> View
                                            </a>

                                          
                                            <a href="{{ route('students.edit', $student->id) }}"
                                                class="btn btn-light btn-sm">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>

                                            
                                            <form action="{{ route('students.destroy', $student->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this student?');"
                                                class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">No students found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
@endsection

@push('custom_js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
           
            document.querySelectorAll("tbody tr[data-href]").forEach(row => {
                row.addEventListener("click", function(e) {
                    
                    if (e.target.closest('.ignore-click')) {
                        return;
                    }
                    window.location.href = this.dataset.href;
                });
            });
        });
    </script>
@endpush
