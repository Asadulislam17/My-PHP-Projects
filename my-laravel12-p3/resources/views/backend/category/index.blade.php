@extends('backend.master')

@section('main_content')

    <main class="page-content">


        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Categories</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Category List</li>
                    </ol>
                </nav>
            </div>
        </div>


        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif


        <div class="card">
            <div class="card-body">


                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 text-uppercase"><i class="bi bi-tags me-2"></i>Categories Table</h5>
                    <button class="btn btn-primary btn-sm px-3" type="button" data-bs-toggle="modal"
                        data-bs-target="#addCategoryModal">Add New Category</button>
                </div>


                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle mb-0 table-hover">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Category Name</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td class="fw-semibold">{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">

                                            <button type="button" class="btn btn-warning btn-sm text-dark"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCategoryModal{{ $category->id }}">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>

                                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this category?');"
                                                class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>


                                <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1"
                                    aria-labelledby="editCategoryModalLabel{{ $category->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('categories.update', $category->id) }}" method="POST"
                                                class="needs-validation" novalidate>
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h2 class="modal-title h5"
                                                        id="editCategoryModalLabel{{ $category->id }}">Edit Category</h2>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <label class="form-label"
                                                        for="categoryName{{ $category->id }}">Category Name</label>
                                                    <input type="text" class="form-control"
                                                        id="categoryName{{ $category->id }}" name="name"
                                                        value="{{ $category->name }}" required>
                                                    <div class="invalid-feedback">A category name is required.</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary"
                                                        data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Category</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No categories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </main>


    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('categories.store') }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="addCategoryModalLabel">Add New Category</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start">
                        <label class="form-label" for="newCategoryName">Category Name</label>
                        <input type="text" class="form-control" id="newCategoryName" name="name"
                            placeholder="Enter category name" required>
                        <div class="invalid-feedback">Please enter a valid category name.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
