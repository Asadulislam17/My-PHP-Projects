    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function() {

        
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000",
        };

      
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        
        $('#studentForm').submit(function(e) {
            e.preventDefault();
            
            let studentId = $('#student_id').val();
            let url = "{{ route('students.store') }}"; 
            let type = "POST";
            let studentData = new FormData(this);

           
            if (studentId) {
                url = "/students/" + studentId; 
                studentData.append('_method', 'PUT'); 
            }

            $('.text-danger').text(''); 

            $.ajax({
                url: url,
                type: "POST", 
                data: studentData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#myModal').modal('hide');
                    $('#studentForm').trigger("reset");
                    $('#student_id').remove(); 
                    $('.modal-title').text('Add New Student');

                    toastr.success(response.message);

                    let address = response.student.address ? response.student.address : 'N/A';

                    if (studentId) {
                       
                        let row = $('#tr-' + response.student.id);
                        row.find('td:nth-child(2)').text(response.student.name);
                        row.find('td:nth-child(3)').text(response.student.email);
                        row.find('td:nth-child(4)').text(address);
                    } else {
                        
                        let sl = $('table tbody tr').length + 1; 
                        let newRow = `
                            <tr id="tr-${response.student.id}">
                                <td>${sl}</td>
                                <td>${response.student.name}</td>
                                <td>${response.student.email}</td>
                                <td>${address}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="${response.student.id}">Edit</button>
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="${response.student.id}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        `;

                        if($('table tbody tr td').hasClass('text-danger')) {
                            $('table tbody').html(newRow);
                        } else {
                            $('table tbody').prepend(newRow); 
                        }
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('.' + key + '_error').text(value);
                    });
                }
            });
        });

        
        $(document).on('click', '.delete-btn', function() {
            if (confirm('Are you sure you want to delete this student?')) {
                let studentId = $(this).data('id');
                let row = $(this).closest('tr'); 

                $.ajax({
                    url: "/students/" + studentId,
                    type: "DELETE", 
                    success: function(response) {
                        row.remove(); 
                        toastr.success(response.message); 
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong!');
                    }
                });
            }
        });

        
        $(document).on('click', '.edit-btn', function() {
            let studentId = $(this).data('id');
            $('.text-danger').text(''); 

            $.ajax({
                url: "/students/" + studentId + "/edit",
                type: "GET",
                success: function(response) {
                    $('#name').val(response.name);
                    $('#email').val(response.email);
                    $('#address').val(response.address);

                    
                    if ($('#student_id').length == 0) {
                        $('#studentForm').append(`<input type="hidden" id="student_id" name="student_id" value="${response.id}">`);
                    } else {
                        $('#student_id').val(response.id);
                    }

                    $('.modal-title').text('Edit Student');
                    $('#myModal').modal('show');
                },
                error: function(xhr) {
                    toastr.error('Student data not found!');
                }
            });
        });

        
        $('#myModal').on('hidden.bs.modal', function () {
            $('#studentForm').trigger("reset");
            $('#student_id').remove();
            $('.modal-title').text('Add New Student');
            $('.text-danger').text('');
        });

    });
</script>
