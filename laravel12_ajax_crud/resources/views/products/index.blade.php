<!DOCTYPE html>
<html lang="en">

<head>
    <title>Product Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    


    <style>
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <div class="p-5 bg-primary text-white text-center">
        <h1>Product Management System</h1>
        <p>Laravel & Bootstrap 5 CRUD Application</p>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('products.index') }}">Products</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-flex justify-content-between mb-3">
                    <h2>Product List</h2>
                    
                    <!-- Button to Open Product Add Modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                        + New Product Add
                    </button>
                </div>
                
                <!-- Product Table Include -->
                @include('products.table')
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="mt-5 p-4 bg-dark text-white text-center">
        <p>Footer © 2026</p>
    </div>

    <!-- Product Entry Modal Include -->
    @include('products.entry')


    @include('notifications')

</body>
</html>
