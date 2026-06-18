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
                    <span class="page-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Management</p>
                        <h1 class="h3 mb-1">Add User</h1>
                        <p class="text-muted mb-0">Create a new user account with role and team assignments.</p>
                    </div>
                </div>
                <div class="heading-actions"><a class="btn btn-outline-secondary btn-sm"
                        href="{{ route('students.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to
                        Users</a></div>
            </div>

            <section class="row g-3">
                <div class="col-12 col-xl-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h2>Whops! There ware some problems with your input</h2>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="panel needs-validation" method="POST" action="{{ route('students.store') }}" novalidate>

                        @csrf

                        <div class="panel-header">
                            <div>
                                <h2 class="h5 mb-1 section-title"><i class="bi bi-person-plus"
                                        aria-hidden="true"></i><span>Student Information</span></h2>
                                <p class="text-muted mb-0">Create a student account with validated fields.</p>
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
                                <label class="form-label" for="email">Email</label>
                                <input class="form-control" id="email" name="email" value="{{ old('email') }}"
                                    type="email" required>
                                <div class="invalid-feedback">Enter a valid email.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="phone">Phone</label>
                                <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}"
                                    type="tel" required>
                                <div class="invalid-feedback">Phone number is required.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-block">Gender</label>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="genderMale"
                                        value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }} required>
                                    <label class="form-check-label" for="genderMale">Male</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="genderFemale"
                                        value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }} required>
                                    <label class="form-check-label" for="genderFemale">Female</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="genderOther"
                                        value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }} required>
                                    <label class="form-check-label" for="genderOther">Others</label>
                                </div>

                                <div class="invalid-feedback">Choose a gender.</div>
                            </div>


                            @php
                                $all_subjects = ['PHP', 'Java', 'Mysql', 'React Js'];
                            @endphp

                            <div class="col-md-6">
                                <label class="form-label d-block">Subject</label>

                                @foreach ($all_subjects as $index => $sub)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="subject[]"
                                            id="subject_{{ $index }}" value="{{ $sub }}"
                                            @checked(is_array(old('subject')) && in_array($sub, old('subject')))>

                                        <label class="form-check-label"
                                            for="subject_{{ $index }}">{{ $sub }}</label>
                                    </div>
                                @endforeach

                                <div class="invalid-feedback">Choose at least one subject.</div>
                            </div>




                            <div class="col-md-6">
                                <label class="form-label" for="district">District</label>
                                <select class="form-select" id="district" name="district" required>
                                    <option value="">Choose district</option>
                                    <option value="Bagerhat" {{ old('district') == 'Bagerhat' ? 'selected' : '' }}>
                                        Bagerhat</option>
                                    <option value="Bandarban" {{ old('district') == 'Bandarban' ? 'selected' : '' }}>
                                        Bandarban</option>
                                    <option value="Barguna" {{ old('district') == 'Barguna' ? 'selected' : '' }}>Barguna
                                    </option>
                                    <option value="Barisal" {{ old('district') == 'Barisal' ? 'selected' : '' }}>Barisal
                                    </option>
                                    <option value="Bhola" {{ old('district') == 'Bhola' ? 'selected' : '' }}>Bhola
                                    </option>
                                    <option value="Bogra" {{ old('district') == 'Bogra' ? 'selected' : '' }}>Bogra
                                    </option>
                                    <option value="Brahmanbaria"
                                        {{ old('district') == 'Brahmanbaria' ? 'selected' : '' }}>Brahmanbaria</option>
                                    <option value="Chandpur" {{ old('district') == 'Chandpur' ? 'selected' : '' }}>
                                        Chandpur</option>
                                    <option value="Chapai Nawabganj"
                                        {{ old('district') == 'Chapai Nawabganj' ? 'selected' : '' }}>Chapai Nawabganj
                                    </option>
                                    <option value="Chittagong" {{ old('district') == 'Chittagong' ? 'selected' : '' }}>
                                        Chittagong</option>
                                    <option value="Chuadanga" {{ old('district') == 'Chuadanga' ? 'selected' : '' }}>
                                        Chuadanga</option>
                                    <option value="Comilla" {{ old('district') == 'Comilla' ? 'selected' : '' }}>Comilla
                                    </option>
                                    <option value="Cox's Bazar" {{ old('district') == "Cox's Bazar" ? 'selected' : '' }}>
                                        Cox's Bazar</option>
                                    <option value="Dhaka" {{ old('district') == 'Dhaka' ? 'selected' : '' }}>Dhaka
                                    </option>
                                    <option value="Dinajpur" {{ old('district') == 'Dinajpur' ? 'selected' : '' }}>
                                        Dinajpur</option>
                                    <option value="Faridpur" {{ old('district') == 'Faridpur' ? 'selected' : '' }}>
                                        Faridpur</option>
                                    <option value="Feni" {{ old('district') == 'Feni' ? 'selected' : '' }}>Feni</option>
                                    <option value="Gaibandha" {{ old('district') == 'Gaibandha' ? 'selected' : '' }}>
                                        Gaibandha</option>
                                    <option value="Gazipur" {{ old('district') == 'Gazipur' ? 'selected' : '' }}>Gazipur
                                    </option>
                                    <option value="Gopalganj" {{ old('district') == 'Gopalganj' ? 'selected' : '' }}>
                                        Gopalganj</option>
                                    <option value="Habiganj" {{ old('district') == 'Habiganj' ? 'selected' : '' }}>
                                        Habiganj</option>
                                    <option value="Jamalpur" {{ old('district') == 'Jamalpur' ? 'selected' : '' }}>
                                        Jamalpur</option>
                                    <option value="Jessore" {{ old('district') == 'Jessore' ? 'selected' : '' }}>Jessore
                                    </option>
                                    <option value="Jhalokati" {{ old('district') == 'Jhalokati' ? 'selected' : '' }}>
                                        Jhalokati</option>
                                    <option value="Jhenaidah" {{ old('district') == 'Jhenaidah' ? 'selected' : '' }}>
                                        Jhenaidah</option>
                                    <option value="Joypurhat" {{ old('district') == 'Joypurhat' ? 'selected' : '' }}>
                                        Joypurhat</option>
                                    <option value="Khagrachhari"
                                        {{ old('district') == 'Khagrachhari' ? 'selected' : '' }}>Khagrachhari</option>
                                    <option value="Khulna" {{ old('district') == 'Khulna' ? 'selected' : '' }}>Khulna
                                    </option>
                                    <option value="Kishoreganj" {{ old('district') == 'Kishoreganj' ? 'selected' : '' }}>
                                        Kishoreganj</option>
                                    <option value="Kurigram" {{ old('district') == 'Kurigram' ? 'selected' : '' }}>
                                        Kurigram</option>
                                    <option value="Kushtia" {{ old('district') == 'Kushtia' ? 'selected' : '' }}>Kushtia
                                    </option>
                                    <option value="Lakshmipur" {{ old('district') == 'Lakshmipur' ? 'selected' : '' }}>
                                        Lakshmipur</option>
                                    <option value="Lalmonirhat" {{ old('district') == 'Lalmonirhat' ? 'selected' : '' }}>
                                        Lalmonirhat</option>
                                    <option value="Madaripur" {{ old('district') == 'Madaripur' ? 'selected' : '' }}>
                                        Madaripur</option>
                                    <option value="Magura" {{ old('district') == 'Magura' ? 'selected' : '' }}>Magura
                                    </option>
                                    <option value="Manikganj" {{ old('district') == 'Manikganj' ? 'selected' : '' }}>
                                        Manikganj</option>
                                    <option value="Meherpur" {{ old('district') == 'Meherpur' ? 'selected' : '' }}>
                                        Meherpur</option>
                                    <option value="Moulvibazar" {{ old('district') == 'Moulvibazar' ? 'selected' : '' }}>
                                        Moulvibazar</option>
                                    <option value="Munshiganj" {{ old('district') == 'Munshiganj' ? 'selected' : '' }}>
                                        Munshiganj</option>
                                    <option value="Mymensingh" {{ old('district') == 'Mymensingh' ? 'selected' : '' }}>
                                        Mymensingh</option>
                                    <option value="Naogaon" {{ old('district') == 'Naogaon' ? 'selected' : '' }}>Naogaon
                                    </option>
                                    <option value="Narail" {{ old('district') == 'Narail' ? 'selected' : '' }}>Narail
                                    </option>
                                    <option value="Narayanganj" {{ old('district') == 'Narayanganj' ? 'selected' : '' }}>
                                        Narayanganj</option>
                                    <option value="Narsingdi" {{ old('district') == 'Narsingdi' ? 'selected' : '' }}>
                                        Narsingdi</option>
                                    <option value="Natore" {{ old('district') == 'Natore' ? 'selected' : '' }}>Natore
                                    </option>
                                    <option value="Netrokona" {{ old('district') == 'Netrokona' ? 'selected' : '' }}>
                                        Netrokona</option>
                                    <option value="Nilphamari" {{ old('district') == 'Nilphamari' ? 'selected' : '' }}>
                                        Nilphamari</option>
                                    <option value="Noakhali" {{ old('district') == 'Noakhali' ? 'selected' : '' }}>
                                        Noakhali</option>
                                    <option value="Pabna" {{ old('district') == 'Pabna' ? 'selected' : '' }}>Pabna
                                    </option>
                                    <option value="Panchagarh" {{ old('district') == 'Panchagarh' ? 'selected' : '' }}>
                                        Panchagarh</option>
                                    <option value="Patuakhali" {{ old('district') == 'Patuakhali' ? 'selected' : '' }}>
                                        Patuakhali</option>
                                    <option value="Pirojpur" {{ old('district') == 'Pirojpur' ? 'selected' : '' }}>
                                        Pirojpur</option>
                                    <option value="Rajbari" {{ old('district') == 'Rajbari' ? 'selected' : '' }}>Rajbari
                                    </option>
                                    <option value="Rajshahi" {{ old('district') == 'Rajshahi' ? 'selected' : '' }}>
                                        Rajshahi</option>
                                    <option value="Rangamati" {{ old('district') == 'Rangamati' ? 'selected' : '' }}>
                                        Rangamati</option>
                                    <option value="Rangpur" {{ old('district') == 'Rangpur' ? 'selected' : '' }}>Rangpur
                                    </option>
                                    <option value="Satkhira" {{ old('district') == 'Satkhira' ? 'selected' : '' }}>
                                        Satkhira</option>
                                    <option value="Shariatpur" {{ old('district') == 'Shariatpur' ? 'selected' : '' }}>
                                        Shariatpur</option>
                                    <option value="Sherpur" {{ old('district') == 'Sherpur' ? 'selected' : '' }}>Sherpur
                                    </option>
                                    <option value="Sirajganj" {{ old('district') == 'Sirajganj' ? 'selected' : '' }}>
                                        Sirajganj</option>
                                    <option value="Sunamganj" {{ old('district') == 'Sunamganj' ? 'selected' : '' }}>
                                        Sunamganj</option>
                                    <option value="Sylhet" {{ old('district') == 'Sylhet' ? 'selected' : '' }}>Sylhet
                                    </option>
                                    <option value="Tangail" {{ old('district') == 'Tangail' ? 'selected' : '' }}>Tangail
                                    </option>
                                    <option value="Thakurgaon" {{ old('district') == 'Thakurgaon' ? 'selected' : '' }}>
                                        Thakurgaon</option>
                                </select>
                                <div class="invalid-feedback">Choose a district.</div>
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
                {{-- <div class="col-12 col-xl-4">
                    <div class="panel h-100">
                        <h2 class="h5 mb-3 section-title"><i class="bi bi-list-check" aria-hidden="true"></i><span>Access
                                Checklist</span></h2>
                        <div class="activity-list">
                            <div class="activity-item"><span class="activity-dot bg-success"></span>
                                <div>
                                    <p class="mb-1 fw-semibold">Assign role</p>
                                    <p class="text-muted small mb-0">Start with the least privileged role.</p>
                                </div>
                            </div>
                            <div class="activity-item"><span class="activity-dot bg-primary"></span>
                                <div>
                                    <p class="mb-1 fw-semibold">Add team</p>
                                    <p class="text-muted small mb-0">Team ownership controls dashboards.</p>
                                </div>
                            </div>
                            <div class="activity-item"><span class="activity-dot bg-warning"></span>
                                <div>
                                    <p class="mb-1 fw-semibold">Send invite</p>
                                    <p class="text-muted small mb-0">Users receive activation by email.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </section>
        </div>
    </main>
@endsection
{{-- @push('custom_js')
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
@endpush --}}
