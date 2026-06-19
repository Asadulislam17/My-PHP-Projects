@extends('backend.master')

@section('main_content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
            
            <!-- পেজের টাইটেল অংশ -->
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-book" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Data</p>
                        <h1 class="h3 mb-1">Subjects</h1>
                        <p class="text-muted mb-0">Modal dialogs for confirmations and compact workflows.</p>
                    </div>
                </div>
            </div>

            <!-- সাকসেস মেসেজ -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <section class="panel">
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addSubjectModal">Add New Subject</button>
                </div>

                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Subjects Table</span></h2>
                        <p class="text-muted mb-0">List of all available subjects.</p>
                    </div>
                </div>

                <!-- টেবিল -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Subject Name</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjects as $subject)
                                <tr>
                                    <td class="fw-semibold">{{ $subject->id }}</td>
                                    <td>{{ $subject->name }}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#editSubjectModal{{ $subject->id }}">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>

                                            <form action="{{ route('subjects.destroy', $subject->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this subject?');" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- ==================== এডিট মডাল ==================== -->
                                <div class="modal fade" id="editSubjectModal{{ $subject->id }}" tabindex="-1" aria-labelledby="editSubjectModalLabel{{ $subject->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('subjects.update', $subject->id) }}" method="POST" class="needs-validation" novalidate>
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h2 class="modal-title h5" id="editSubjectModalLabel{{ $subject->id }}">Edit Subject</h2>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <label class="form-label" for="subjectName{{ $subject->id }}">Subject Name</label>
                                                    <input type="text" class="form-control" id="subjectName{{ $subject->id }}" name="name" value="{{ $subject->name }}" required>
                                                    <div class="invalid-feedback">A subject name is required.</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Subject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No subjects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- ==================== অ্যাড মডাল ==================== -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-labelledby="addSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('subjects.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="addSubjectModalLabel">Add New Subject</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <label class="form-label" for="modalSubjectName">Subject Name</label>
                        <input type="text" class="form-control" id="modalSubjectName" name="name" required placeholder="e.g. PHP, Java, Laravel">
                        <div class="invalid-feedback">A subject name is required.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
