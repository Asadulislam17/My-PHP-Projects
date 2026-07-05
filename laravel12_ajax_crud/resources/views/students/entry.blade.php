<!-- The Modal -->
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
        <form id="studentForm">
            @csrf
           
            <div class="modal-header">
                <h4 class="modal-title">Registration Form</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

           
            <div class="modal-body">
                
               
                <div class="mb-3 mt-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" class="form-control" id="name" placeholder="Enter your name" name="name">
                    
                    <span class="text-danger name_error small fw-bold"></span>
                </div>
                
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" placeholder="Enter email" name="email">
                    
                    <span class="text-danger email_error small fw-bold"></span>
                </div>
                
               
                <div class="mb-3">
                    <label for="address" class="form-label">Address:</label>
                    <textarea class="form-control" id="address" rows="3" placeholder="Enter your address" name="address"></textarea>
                    
                    <span class="text-danger address_error small fw-bold"></span>
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
