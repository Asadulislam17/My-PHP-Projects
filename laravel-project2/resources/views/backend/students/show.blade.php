@extends('backend.master')

@section('main_content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">

            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-person-lines-fill" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Management</p>
                        <h1 class="h3 mb-1">Student Details</h1>
                        <p class="text-muted mb-0">Detailed profile view of the student.</p>
                    </div>
                </div>
                <div class="heading-actions">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('students.index') }}">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to List
                    </a>
                </div>
            </div>

            <section class="row g-3">

                <div class="col-12 col-xl-4">
                    <div class="panel h-100 text-center profile-card">
                        <div class="profile-cover">
                            <img src="{{ asset('assets/images/png/dasher-ui-bootstrap-5.jpg') }}" alt="dashboard preview">
                        </div>

                        @if ($student->gender == 'Female')
                            <img class="avatar-img avatar-xl profile-photo" src="https://unsplash.com"
                                alt="{{ $student->first_name }}">
                        @else
                            <img class="avatar-img avatar-xl profile-photo" src="https://unsplash.com"
                                alt="{{ $student->first_name }}">
                        @endif

                        <h2 class="h5 mt-3 mb-1">{{ $student->first_name }} {{ $student->last_name }}</h2>
                        <p class="text-muted mb-3">Student Identity</p>

                        <div class="d-flex justify-content-center gap-2">
                            <span class="badge text-bg-primary">{{ $student->gender }}</span>
                            <span class="badge text-bg-success">Verified</span>
                        </div>

                        <div class="info-list mt-4 text-start">
                            <div><span>Email</span><strong>{{ $student->email }}</strong></div>
                            <div><span>Phone</span><strong>{{ $student->phone ?? 'N/A' }}</strong></div>
                            <div><span>District</span><strong>{{ $student->district }}</strong></div>
                        </div>
                    </div>
                </div>



                <div class="col-12 col-xl-8">
                    <div class="panel h-100 p-4">
                        <div class="panel-header px-0 pt-0">
                            <div>
                                <h2 class="h5 mb-1 section-title">
                                    <i class="bi bi-person-gear" aria-hidden="true"></i>
                                    <span>Academic Information</span>
                                </h2>
                                <p class="text-muted mb-0">Detailed profile overview and enrolled courses.</p>
                            </div>
                        </div>


                        <div class="table-responsive mt-3">
                            <table class="table table-borderless align-middle">
                                <tbody>
                                    <tr class="border-bottom">
                                        <th style="width: 30%;" class="text-muted py-3">First Name</th>
                                        <td class="fw-semibold py-3">{{ $student->first_name }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted py-3">Last Name</th>
                                        <td class="fw-semibold py-3">{{ $student->last_name }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted py-3">Email Address</th>
                                        <td class="text-primary py-3">{{ $student->email }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted py-3">Phone Number</th>
                                        <td class="py-3">{{ $student->phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted py-3">Gender</th>
                                        <td class="py-3">
                                            <span class="badge bg-light text-dark border">{{ $student->gender }}</span>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted py-3">District</th>
                                        <td class="py-3">{{ $student->district }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th class="text-muted py-3">Enrolled Subjects</th>
                                        <td class="py-3">
                                            @if ($student->subject)
                                                @foreach (explode(',', $student->subject) as $sub)
                                                    <span class="badge text-bg-primary mb-1">{{ $sub }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">No subjects selected</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('students.edit', $student->id) }}" class="btn btn-primary">
                                <i class="bi bi-pencil-square"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </main>
@endsection
