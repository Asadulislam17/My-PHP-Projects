@extends('backend.master')

@section('main_content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">

            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Data</p>
                        <h1 class="h3 mb-1">Districts</h1>
                        <p class="text-muted mb-0">Modal dialogs for confirmations and compact workflows.</p>
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
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal"
                        data-bs-target="#addDistrictModal">Add New District</button>
                </div>

                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Districts
                                Table</span></h2>
                        <p class="text-muted mb-0">List of all available districts.</p>
                    </div>
                </div>

                <!-- টেবিল -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>District Name</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($districts as $district)
                                <tr>
                                    <td class="fw-semibold">{{ $district->id }}</td>
                                    <td>{{ $district->name }}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editDistrictModal{{ $district->id }}">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>

                                            <form action="{{ route('districts.destroy', $district->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this district?');"
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

                                
                                <div class="modal fade" id="editDistrictModal{{ $district->id }}" tabindex="-1"
                                    aria-labelledby="editDistrictModalLabel{{ $district->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('districts.update', $district->id) }}" method="POST"
                                                class="needs-validation" novalidate>
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h2 class="modal-title h5"
                                                        id="editDistrictModalLabel{{ $district->id }}">Edit District</h2>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <label class="form-label" for="districtName{{ $district->id }}">District
                                                        Name</label>
                                                    <input type="text" class="form-control"
                                                        id="districtName{{ $district->id }}" name="name"
                                                        value="{{ $district->name }}" required>
                                                    <div class="invalid-feedback">A district name is required.</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update District</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No districts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

   
    <div class="modal fade" id="addDistrictModal" tabindex="-1" aria-labelledby="addDistrictModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('districts.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="addDistrictModalLabel">Add New District</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <label class="form-label" for="modalDistrictName">District Name</label>
                        <input type="text" class="form-control" id="modalDistrictName" name="name" required
                            placeholder="e.g. Dhaka, Khulna">
                        <div class="invalid-feedback">A district name is required.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save District</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
