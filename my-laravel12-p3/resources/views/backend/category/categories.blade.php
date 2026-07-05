@extends('backend.master')

@section('main_content')
<main class="page-content">
    <!-- ১. DataTables-এর সঠিক বুটস্ট্র্যাপ ৫ সিএসএস লিংক -->
    <link href="https://datatables.net" rel="stylesheet">

    <!-- Breadcrumb -->
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

    <!-- AJAX সাকসেস/এরর অ্যালার্ট কন্টেইনার -->
    <div id="alertContainer"></div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 text-uppercase"><i class="bi bi-tags me-2"></i>Categories Table (AJAX)</h5>
                <!-- মডাল ওপেন বাটন -->
                <button class="btn btn-primary btn-sm px-3" type="button" id="createNewCategory" data-bs-toggle="modal" data-bs-target="#ajaxCategoryModal">Add New Category</button>
            </div>

            <!-- Yajra DataTable স্ট্রাকচার -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0 table-hover category-table w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Category Name</th>
                            <th>Slug</th>
                            <th class="text-end" width="150px">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- SINGLE DYNAMIC POPUP MODAL -->
<div class="modal fade" id="ajaxCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="categoryForm" name="categoryForm">
                <input type="hidden" name="category_id" id="category_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHeading">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="alert alert-danger d-none" id="modalErrorBox">
                        <ul class="mb-0" id="modalErrorList"></ul>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="categoryName">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="name" placeholder="Enter category name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- ২. DataTables-এর সঠিক জাভাস্ক্রিপ্ট কোর ফাইলসমূহ -->
<script src="


"></script>
<script src="https://datatables.net"></script>

<script type="text/javascript">
  jQuery(document).ready(function ($) {
      
      // CSRF টোকেন বাইন্ডিং
      $.ajaxSetup({
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
      });
    
      // Yajra DataTable রেন্ডার করা
      var table = $('.category-table').DataTable({
          processing: true,
          serverSide: true,
          ajax: "{{ route('categories.index') }}",
          columns: [
              {data: 'DT_RowIndex', name: 'DT_RowIndex'},
              {data: 'name', name: 'name'},
              {data: 'slug', name: 'slug'},
              {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end'},
          ]
      });
     
      // Add New Category ক্লিকের একশন
      $(document).on('click', '#createNewCategory', function () {
          $('#category_id').val('');
          $('#categoryForm').trigger("reset");
          $('#modalHeading').html("Add New Category");
          $('#modalErrorBox').addClass('d-none');
      });
    
      // Edit বাটনের অ্যাকশন
      $(document).on('click', '.editCategory', function () {
        var category_id = $(this).data('id');
        $('#modalErrorBox').addClass('d-none');
        
        $.get("{{ route('categories.index') }}" +'/' + category_id +'/edit', function (data) {
            $('#modalHeading').html("Edit Category");
            $('#category_id').val(data.id);
            $('#categoryName').val(data.name);
            var editModal = new bootstrap.Modal(document.getElementById('ajaxCategoryModal'));
            editModal.show();
        });
     });
    
      // AJAX ফর্ম সাবমিশন লজিক (ডেটাবেজে ডেটা পাঠাবে)
      $('#categoryForm').submit(function (e) {
          e.preventDefault();
          $('#saveBtn').html('Saving...').prop('disabled', true);
          $('#modalErrorBox').addClass('d-none');
          $('#modalErrorList').html('');
      
          $.ajax({
              data: $(this).serialize(),
              url: "{{ route('categories.store') }}",
              type: "POST",
              dataType: 'json',
              success: function (data) {
                  $('#categoryForm').trigger("reset");
                  
                  // মডাল ক্লোজ করে ব্যাকড্রপ রিমুভ করা
                  var modalEl = document.getElementById('ajaxCategoryModal');
                  var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                  modalInstance.hide();
                  
                  $('.modal-backdrop').remove();
                  $('body').removeClass('modal-open').css('overflow', '');
                  
                  $('#saveBtn').html('Save Changes').prop('disabled', false);
                  table.draw(); // পেজ রিফ্রেশ ছাড়া টেবিল আপডেট করবে
                  
                  $('#alertContainer').html('<div class="alert alert-success alert-dismissible fade show" role="alert">Category saved successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
              },
              error: function (data) {
                  $('#saveBtn').html('Save Changes').prop('disabled', false);
                  if (data.status === 422) {
                      $('#modalErrorBox').removeClass('d-none');
                      var errors = data.responseJSON.errors;
                      $.each(errors, function (key, value) {
                          $('#modalErrorList').append('<li>' + value + '</li>');
                      });
                  } else {
                      alert('Server Error: Data could not be saved.');
                  }
              }
          });
      });
    
      // ক্যাটাগরি ডিলিট অ্যাকশন
      $(document).on('click', '.deleteCategory', function () {
          var category_id = $(this).data("id");
          if(confirm("Are you sure you want to delete this category?")) {
              $.ajax({
                  type: "DELETE",
                  url: "{{ route('categories.index') }}"+'/'+category_id,
                  success: function (data) {
                      table.draw();
                      $('#alertContainer').html('<div class="alert alert-success alert-dismissible fade show" role="alert">Category deleted successfully!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
                  },
                  error: function (data) { console.log('Error:', data); }
              });
          }
      });
  });
</script>
@endpush
