@extends('backend.master')

@section('main_content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">

            <!-- 📌 পেজ হেডার এবং আইকন ক্যাটাগরি অনুযায়ী পরিবর্তন করা হয়েছে -->
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-tags" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Data</p>
                        <h1 class="h3 mb-1">Categories</h1>
                        <p class="text-muted mb-0">Manage your product categories with modal dialogs.</p>
                    </div>
                </div>
            </div>

            <!-- সফলতার মেসেজ অ্যালার্ট -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- ভ্যালিডেশন এরর মেসেজ (যদি ফরম সাবমিটে কোনো ভুল হয়) -->
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

            <section class="panel">
                
                <!-- ক্যাটাগরি যোগ করার বাটন -->
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal"
                        data-bs-target="#addCategoryModal">Add New Category</button>
                </div>

                <div class="panel-header">
                    <div>
                        <h2 class="h5 mb-1 section-title"><i class="bi bi-table" aria-hidden="true"></i><span>Categories
                                Table</span></h2>
                        <p class="text-muted mb-0">List of all available product categories.</p>
                    </div>
                </div>

                <!-- ক্যাটাগরি তালিকা টেবিল -->
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
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
                                            <!-- এডিট বাটন (আইডি ডাইনামিক করা হয়েছে) -->
                                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editCategoryModal{{ $category->id }}">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>

                                            <!-- ডিলিট ফরম -->
                                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this category?');"
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

                                <!-- 📝 প্রতিটি ক্যাটাগরির জন্য আলাদা এডিট মডেল পপআপ -->
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
                                                    <label class="form-label" for="categoryName{{ $category->id }}">Category
                                                        Name</label>
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
            </section>
        </div>
    </main>

    <!-- ➕ নতুন ক্যাটাগরি তৈরি করার মডেল পপআপ (টেবিলের বাইরে রাখা হয়েছে) -->
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
                        <input type="text" class="form-control" id="newCategoryName" name="name" placeholder="Enter category name" required>
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
