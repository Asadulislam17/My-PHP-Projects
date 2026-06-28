    @extends('backend.master')

    @section('main_content')
        <main class="dashboard-content">
            <div class="container-fluid px-3 px-lg-4 py-4">

                <div class="page-heading">
                    <div class="page-heading-copy">
                        <span class="page-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
                        <div>
                            <p class="eyebrow mb-1">Management</p>
                            <h1 class="h3 mb-1">Add Student</h1>
                            <p class="text-muted mb-0">Create a new student account with validated fields.</p>
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

                       
                        <form class="panel needs-validation" method="POST" action="{{ route('students.store') }}"
                            enctype="multipart/form-data" novalidate>
                            @csrf

                            <div class="panel-header">
                                <div>
                                    <h2 class="h5 mb-1 section-title"><i class="bi bi-ui-checks-grid"
                                            aria-hidden="true"></i><span>Validation Form</span></h2>
                                    <p class="text-muted mb-0">Bootstrap-ready fields with custom validation feedback.</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="firstName">First name</label>
                                    <input class="form-control" id="firstName" name="first_name" value="{{ old('first_name') }}"
                                        type="text" required>
                                    <div class="invalid-feedback">First name is required.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="lastName">Last name</label>
                                    <input class="form-control" id="lastName" name="last_name" value="{{ old('last_name') }}"
                                        type="text" required>
                                    <div class="invalid-feedback">Last name is required.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="formEmail">Email</label>
                                    <input class="form-control" id="formEmail" name="email" value="{{ old('email') }}"
                                        type="email" required>
                                    <div class="invalid-feedback">Valid email is required.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="formPhone">Phone</label>
                                    <input class="form-control" id="formPhone" name="phone" value="{{ old('phone') }}"
                                        type="tel" required>
                                    <div class="invalid-feedback">Phone number is required.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="district">District</label>
                                    <select class="form-select" id="district" name="district" required>
                                        <option value="">Choose district</option>
                                        @foreach ($districts as $district)
                                            <option value="{{ $district->name }}"
                                                {{ old('district') == $district->name ? 'selected' : '' }}>
                                                {{ $district->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Choose a district.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="studentImage">Profile Image</label>
                                    <input class="form-control" id="studentImage" name="image" type="file"
                                        accept="image/*">
                                    <div class="invalid-feedback">Choose a valid image.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label d-block">Gender</label>
                                    <div class="pt-1">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="genderMale"
                                                value="Male" {{ old('gender') == 'Male' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="genderMale">Male</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="genderFemale"
                                                value="Female" {{ old('gender') == 'Female' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="genderFemale">Female</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="gender" id="genderOther"
                                                value="Other" {{ old('gender') == 'Other' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="genderOther">Others</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Choose a gender.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label d-block">Subject</label>
                                    <div class="pt-1">
                                        @foreach ($subjects as $subject)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="subject[]"
                                                    id="subject_{{ $subject->id }}" value="{{ $subject->name }}"
                                                    @checked(is_array(old('subject')) && in_array($subject->name, old('subject')))>
                                                <label class="form-check-label" for="subject_{{ $subject->id }}">
                                                    {{ $subject->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="invalid-feedback">Choose at least one subject.</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                                <a class="btn btn-outline-secondary" href="{{ route('students.index') }}">Cancel</a>
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-person-check" aria-hidden="true"></i> Create Student
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </main>
    @endsection
