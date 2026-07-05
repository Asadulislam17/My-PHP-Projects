<!DOCTYPE html>
<html lang="en">

<head>
    <title>Bootstrap 5 Website Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        .fakeimg {
            height: 200px;
            background: #aaa;
        }
    </style>
</head>

<body>

   
    <div class="p-5 bg-primary text-white text-center">
        <h1>Student Management System</h1>
        <p>Laravel & Bootstrap 5 CRUD Application</p>
    </div>

 
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Dashboard</a>
                </li>
            </ul>
        </div>
    </nav>

    
    <div class="container mt-5">
        
        

        <div class="row">
            <div class="col-sm-12">
                <div class="d-flex justify-content-between mb-3">
                    <h2>Student List</h2>
                    
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModal">
                        + New Student Add
                    </button>
                </div>
                
               
                @include('students.table')
            </div>
        </div>
    </div>

   
    <div class="mt-5 p-4 bg-dark text-white text-center">
        <p>Footer © 2026</p>
    </div>

    
    @include('students.entry')

  
    @include('students.scripts')

</body>
</html>