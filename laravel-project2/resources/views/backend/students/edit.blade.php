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
                        <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Users
                    </a>
                </div>
            </div>

            <section class="row g-3">
                <div class="col-12 col-xl-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h2>Whops! There were some problems with your input</h2>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                   
                    <form class="panel needs-validation" method="POST" action="{{ route('students.update', $student->id) }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title">
                                    <i class="bi bi-person-check" aria-hidden="true"></i><span>Edit Student Information</span>
                                </h2>
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
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" id="email" name="email" value="{{ old('email', $student->email) }}" type="email" required>
                                <div class="invalid-feedback">Enter a valid email.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone</label>
                                <input class="form-control" id="phone" name="phone" value="{{ old('phone', $student->phone) }}" type="tel" required>
                                <div class="invalid-feedback">Phone number is required.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">Gender</label>

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
                                <div class="invalid-feedback">Choose a gender.</div>
                            </div>

                            
                            @php
                                $all_subjects = ['PHP', 'Java', 'Mysql', 'React Js'];
                                $saved_subjects = old('subject', explode(',', $student->subject ?? ''));
                            @endphp

                            <div class="col-md-6">
                                <label class="form-label d-block">Subject</label>
                                @foreach ($all_subjects as $index => $sub)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="subject[]" id="subject_{{ $index }}" value="{{ $sub }}"
                                            @checked(is_array($saved_subjects) && in_array($sub, $saved_subjects))>
                                        <label class="form-check-label" for="subject_{{ $index }}">{{ $sub }}</label>
                                    </div>
                                @endforeach
                                <div class="invalid-feedback">Choose at least one subject.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="district">District</label>
                                <select class="form-select" id="district" name="district" required>
                                    <option value="">Choose district</option>
                                    @php
                                        $districts = ['Bagerhat', 'Bandarban', 'Barguna', 'Barisal', 'Bhola', 'Bogra', 'Brahmanbaria', 'Chandpur', 'Chapai Nawabganj', 'Chittagong', 'Chuadanga', 'Comilla', "Cox's Bazar", 'Dhaka', 'Dinajpur', 'Faridpur', 'Feni', 'Gaibandha', 'Gazipur', 'Gopalganj', 'Habiganj', 'Jamalpur', 'Jessore', 'Jhalokati', 'Jhenaidah', 'Joypurhat', 'Khagrachhari', 'Khulna', 'Kishoreganj', 'Kurigram', 'Kushtia', 'Lakshmipur', 'Lalmonirhat', 'Madaripur', 'Magura', 'Manikganj', 'Meherpur', 'Moulvibazar', 'Munshiganj', 'Mymensingh', 'Naogaon', 'Narail', 'Narayanganj', 'Narsingdi', 'Natore', 'Netrokona', 'Nilphamari', 'Noakhali', 'Pabna', 'Panchagarh', 'Patuakhali', 'Pirojpur', 'Rajbari', 'Rajshahi', 'Rangamati', 'Rangpur', 'Satkhira', 'Shariatpur', 'Sherpur', 'Sirajganj', 'Sunamganj', 'Sylhet', 'Tangail', 'Thakurgaon'];
                                        $selected_district = old('district', $student->district);
                                    @endphp
                                    
                                    @foreach($districts as $district)
                                        <option value="{{ $district }}" {{ $selected_district == $district ? 'selected' : '' }}>{{ $district }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Choose a district.</div>
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
