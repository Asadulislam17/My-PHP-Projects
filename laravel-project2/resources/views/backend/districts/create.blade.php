@extends('backend.master')

@section('main_content')
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-4 py-4">
            
            
            <div class="page-heading">
                <div class="page-heading-copy">
                    <span class="page-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                    <div>
                        <p class="eyebrow mb-1">Form</p>
                        <h1 class="h3 mb-1">Add New District</h1>
                        <p class="text-muted mb-0">Create a new district record in the database.</p>
                    </div>
                </div>
            </div>

            <section class="panel" style="max-width: 600px;">
                
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('districts.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                </div>

                <div class="panel-body">
                   
                    <form action="#" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label">District Name</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. Dhaka, Khulna, Barisal">
                        </div>

                        <button type="submit" class="btn btn-primary">Save District</button>
                    </form>
                </div>
            </section>

        </div>
    </main>
@endsection
