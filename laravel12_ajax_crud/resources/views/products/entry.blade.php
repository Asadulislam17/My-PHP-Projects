<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.css" />
<!-- The Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <!-- ১. সরাসরি লারাভেলের স্টোর রাউটে অ্যাকশন সেট করা হলো -->
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
           
            <div class="modal-header">
                <h4 class="modal-title">Product Entry Form</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Product Name -->
                <div class="mb-3 mt-3">
                    <label for="name" class="form-label">Product Name:</label>
                    <input type="text" class="form-control" id="name" placeholder="Enter product name" name="name" required value="{{ old('name') }}">
                </div>
                
                <!-- Product Price -->
                <div class="mb-3">
                    <label for="price" class="form-label">Price:</label>
                    <input type="number" step="0.01" class="form-control" id="price" placeholder="Enter price" name="price" required value="{{ old('price') }}">
                </div>
                
                <!-- Product Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea class="form-control" id="description" rows="3" placeholder="Enter product description" name="description">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
  </div>
</div>
