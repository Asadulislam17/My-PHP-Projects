@extends('backend.master')

@section('main_content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Management</p>
                        <h1 class="h3 mb-1">Edit Student</h1>
                        <p class="text-muted mb-0">Update student account details with validated fields.</p>
                    </div>
                </div>
                <div class="heading-actions">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('students.index') }}">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Students
                    </a>
                </div>
            </div>

            <section class="row g-3">
                
                <div class="col-12 col-xl-12">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            <h2 class="h6 fw-bold">Whoops! There were some problems with your input:</h2>
                            <ul class="mb-0 card-text small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="panel needs-validation" method="POST" action="{{ route('students.update', $student->id) }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-ui-checks-grid" aria-hidden="true"></i><span>Edit Student Information</span></h2>
                                <p class="text-muted mb-0">Modify the student record safely.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="firstName">First name</label>
                                <input class="form-control" id="firstName" name="first_name" value="{{ old('first_name', $student->first_name) }}" type="text" required>
                                <div class="invalid-feedback">First name is required.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="lastName">Last name</label>
                                <input class="form-control" id="lastName" name="last_name" value="{{ old('last_name', $student->last_name) }}" type="text" required>
                                <div class="invalid-feedback">Last name is required.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="formEmail">Email</label>
                                <input class="form-control" id="formEmail" name="email" value="{{ old('email', $student->email) }}" type="email" required>
                                <div class="invalid-feedback">Valid email is required.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="formPhone">Phone</label>
                                <input class="form-control" id="formPhone" name="phone" value="{{ old('phone', $student->phone) }}" type="tel" required>
                                <div class="invalid-feedback">Phone number is required.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">Gender</label>
                                <div class="pt-1">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" {{ old('gender', $student->gender) == 'Male' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="genderMale">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" {{ old('gender', $student->gender) == 'Female' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="genderFemale">Female</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other" {{ old('gender', $student->gender) == 'Other' ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="genderOther">Others</label>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Choose a gender.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Subject</label>
                                <div class="pt-1">
                                    @php
                                        $saved_subjects = $student->subject ? explode(',', $student->subject) : [];
                                    @endphp

                                    @foreach ($subjects as $subject)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="subject[]"
                                                id="subject_{{ $subject->id }}" value="{{ $subject->name }}"
                                                @checked((is_array(old('subject')) && in_array($subject->name, old('subject'))) || in_array($subject->name, $saved_subjects))>
                                            <label class="form-check-label" for="subject_{{ $subject->id }}">
                                                {{ $subject->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="invalid-feedback">Choose at least one subject.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="district">District</label>
                                <select class="form-select" id="district" name="district" required>
                                    <option value="">Choose district</option>
                                    @foreach ($districts as $district)
                                        <option value="{{ $district->name }}"
                                            {{ old('district', $student->district) == $district->name ? 'selected' : '' }}>
                                            {{ $district->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Choose a district.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="studentImage">Profile Image</label>
                                <input class="form-control mb-2" id="studentImage" name="image" type="file" accept="image/*">
                                
                                @if($student->image)
                                    <div class="mt-2">
                                        <span class="text-muted d-block small mb-1">Current Image:</span>
                                        <img src="{{ asset($student->image) }}" alt="Current Profile" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                @endif
                                <div class="invalid-feedback">Choose a valid image.</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                            <a class="btn btn-outline-secondary" href="{{ route('students.index') }}">Cancel</a>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-person-check" aria-hidden="true"></i> Update Student
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
@endsection
