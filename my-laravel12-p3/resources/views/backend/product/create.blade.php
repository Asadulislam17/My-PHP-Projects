@extends('backend.master')

@section('main_content')
    <main class="page-content">

        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Products</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add Product</li>
                    </ol>
                </nav>
            </div>
        </div>

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
                        <h5 class="mb-4">Add New Product</h5>


                        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf


                            <div class="row mb-3">
                                <label for="product_name" class="col-sm-3 col-form-label">Product Name</label>
                                <div class="col-sm-9">
                                    <input type="text" name="name" class="form-control" id="product_name"
                                        value="{{ old('name') }}" placeholder="Enter Product Name" maxlength="60"
                                        required>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="product_category" class="col-sm-3 col-form-label">Select Category</label>
                                <div class="col-sm-9">
                                    <select class="form-select" name="category_id" id="product_category" required>
                                        <option value="" selected disabled>Choose a Category</option>

                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>



                            <div class="row mb-3">
                                <label for="product_price" class="col-sm-3 col-form-label">Product Price</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" name="price" class="form-control"
                                        id="product_price" value="{{ old('price') }}" placeholder="Enter Product Price"
                                        required>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label for="product_image" class="col-sm-3 col-form-label">Product Image</label>
                                <div class="col-sm-9">
                                    <input type="file" name="image" class="form-control" id="product_image">
                                </div>
                            </div>


                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Status</label>
                                <div class="col-sm-9 d-flex align-items-center gap-3">
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_instock"
                                            value="In Stock" {{ old('status', 'In Stock') == 'In Stock' ? 'checked' : '' }}
                                            required>
                                        <label class="form-check-label" for="status_instock">
                                            In Stock
                                        </label>
                                    </div>

                                   
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_outstock"
                                            value="Out of Stock" {{ old('status') == 'Out of Stock' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_outstock">
                                            Out of Stock
                                        </label>
                                    </div>

                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_preorder"
                                            value="Pre-Order" {{ old('status') == 'Pre-Order' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_preorder">
                                            Pre-Order
                                        </label>
                                    </div>
                                </div>

                                
                                @error('status')
                                    <div class="text-danger small ms-3">{{ $message }}</div>
                                @enderror
                            </div>




                            <div class="row mb-3">
                                <label for="product_description" class="col-sm-3 col-form-label">Description</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="description" id="product_description" rows="3"
                                        placeholder="Enter Product Description" maxlength="250">{{ old('description') }}</textarea>
                                </div>
                            </div>


                            <div class="row">
                                <label class="col-sm-3 col-form-label"></label>
                                <div class="col-sm-9">
                                    <div class="d-md-flex d-grid align-items-center gap-3">
                                        <button type="submit" class="btn btn-primary px-4">Save Product</button>
                                        <button type="reset" class="btn btn-light px-4">Reset</button>
                                        <a href="{{ route('products.index') }}" class="btn btn-secondary px-4">Back to
                                            List</a>
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
