@extends('backend.master')

@section('main_content')
    <main class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Products</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Product</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="row">
            <div class="col-lg-12">

                
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Edit Product: {{ $product->name }}</h5>

                        
                        <form action="{{ route('products.update', $product->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="row mb-3">
                                <label for="product_name" class="col-sm-3 col-form-label">Product Name</label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" id="product_name"
                                        value="{{ old('name', $product->name) }}" placeholder="Enter Product Name"
                                        maxlength="60" required>
                                </div>
                            </div>

                            
                            <div class="row mb-3">
                                <label for="product_category" class="col-sm-3 col-form-label">Select Category</label>
                                <div class="col-sm-9">
                                    <select class="form-select" name="category" id="product_category" required>
                                        <option value="" disabled>Choose a Category</option>
                                        <option value="Electronics"
                                            {{ old('category', $product->category) == 'Electronics' ? 'selected' : '' }}>
                                            Electronics</option>
                                        <option value="Clothing"
                                            {{ old('category', $product->category) == 'Clothing' ? 'selected' : '' }}>
                                            Clothing</option>
                                        <option value="Grocery"
                                            {{ old('category', $product->category) == 'Grocery' ? 'selected' : '' }}>Grocery
                                        </option>
                                    </select>
                                </div>
                            </div>

                            
                            <div class="row mb-3">
                                <label for="product_price" class="col-sm-3 col-form-label">Product Price</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" name="price" class="form-control"
                                        id="product_price" value="{{ old('price', $product->price) }}"
                                        placeholder="Enter Product Price" required>
                                </div>
                            </div>

                            
                            <div class="row mb-3">
                                <label for="product_image" class="col-sm-3 col-form-label">Product Image</label>
                                <div class="col-sm-9">
                                    <input type="file" name="image" class="form-control mb-2" id="product_image">

                                    
                                    @if ($product->image)
                                        <div class="mt-2">
                                            <p class="mb-1 text-muted" style="font-size: 13px;">Current Image:</p>
                                            <img src="{{ asset($product->image) }}" alt="product" width="100"
                                                class="img-thumbnail">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Status</label>
                                <div class="col-sm-9 d-flex align-items-center gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_active"
                                            value="1" {{ old('status', $product->status) == '1' ? 'checked' : '' }}
                                            required>
                                        <label class="form-check-label" for="status_active">
                                            Active
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_inactive"
                                            value="0" {{ old('status', $product->status) == '0' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_inactive">
                                            Inactive
                                        </label>
                                    </div>
                                </div>
                            </div>


                            
                            <div class="row mb-3">
                                <label for="product_description" class="col-sm-3 col-form-label">Description</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="description" id="product_description" rows="3"
                                        placeholder="Enter Product Description" maxlength="250">{{ old('description', $product->description) }}</textarea>
                                </div>
                            </div>

                            
                            <div class="row">
                                <label class="col-sm-3 col-form-label"></label>
                                <div class="col-sm-9">
                                    <div class="d-md-flex d-grid align-items-center gap-3">
                                        <button type="submit" class="btn btn-primary px-4">Update Product</button>
                                        <a href="{{ route('products.index') }}" class="btn btn-secondary px-4">Cancel</a>
                                    </div>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

    </main>
@endsection
