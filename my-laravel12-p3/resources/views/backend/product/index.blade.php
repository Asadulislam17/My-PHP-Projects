@extends('backend.master')
@push('style')
    <link href="{{ asset('') }}assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('main_content')
    {{-- <main class="page-content">
       
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 text-uppercase">Product List</h5>
            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">Add New Product</a>
        </div>

        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example2" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $key => $product)
                                <tr>
                                    <td>{{ $product->id}}</td>
                                    <td>
                                        @if ($product->image)
                                            <img src="{{ asset($product->image) }}" alt="product" width="50" class="img-thumbnail">
                                        @else
                                            <span class="text-muted">No Image</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category }}</td>
                                    <td>${{ number_format($product->price, 2) }}</td>
                                    <td>
                                        @if ($product->status == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm me-2">Edit</a>
                                        
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>SL</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </main> --}}
    <main class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Products</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Product List</li>
                    </ol>
                </nav>
            </div>
            <!-- ডানদিকের সেটিংস বাটন ও ড্রপডাউনটি ফর্মে প্রোডাক্ট যোগ করার শর্টকাটসহ রাখা হয়েছে -->
            <div class="ms-auto">
                <div class="btn-group">
                    <a href="{{ route('products.create') }}" class="btn btn-primary">Add New Product</a>
                    <button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                        data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
                        <a class="dropdown-item" href="{{ route('products.create') }}">Create Product</a>
                        <a class="dropdown-item" href="{{ route('categories.index') }}">Manage Categories</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="javascript:;">Refresh Table</a>
                    </div>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->

        <h6 class="mb-0 text-uppercase">All Available Products</h6>
        <hr>

        <!-- সফলতার মেসেজ অ্যালার্ট (বুটস্ট্র্যাপ ক্লোজ বাটনসহ) -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example2" class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $key => $product)
                                <tr>
                                    
                                    <td class="fw-semibold">{{ $key + 1 }}</td>
                                    <td>
                                        @if ($product->image)
                                            <img src="{{ asset($product->image) }}" alt="product" width="50"
                                                class="img-thumbnail">
                                        @else
                                            <span class="text-muted" style="font-size: 13px;">No Image</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category }}</td>
                                    {{-- <td>{{ $product->category->name }}</td> --}}
                                    <td>${{ number_format($product->price, 2) }}</td>
                                    <td>
                                        @if ($product->status == 'In Stock')
                                            <span class="badge bg-success">In Stock</span>
                                        @elseif($product->status == 'Out of Stock')
                                            <span class="badge bg-danger">Out of Stock</span>
                                            
                                        @elseif($product->status == 'Pre-Order' || $product->status == 1)
                                            <span class="badge bg-warning text-dark">Pre-Order</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex order-actions gap-2">
                                            
                                            <a href="{{ route('products.edit', $product->id) }}"
                                                class="btn btn-warning btn-sm me-2">Edit</a>

                                           
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No products found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
@endsection
@push('scripts')
    <script src="{{ asset('') }}assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#example2').DataTable({
                lengthChange: false,
                buttons: ['copy', 'excel', 'pdf', 'print']
            });

            table.buttons().container()
                .appendTo('#example2_wrapper .col-md-6:eq(0)');
        });
    </script>
@endpush
