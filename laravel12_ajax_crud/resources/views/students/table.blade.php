<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Sl</th>
            <th>Name</th>
            <th>Email</th>
            <th>Address</th>
            <th width="180px">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $key => $student)
            
            <tr id="tr-{{ $student->id }}">
                <td>{{ $key + 1 }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->email }}</td>
                <td>{{ $student->address ?? 'N/A' }}</td>
                <td>
                    <div class="d-flex gap-2">
                       
                        <button type="button" class="btn btn-warning btn-sm edit-btn" data-id="{{ $student->id }}">
                            Edit
                        </button>

                     

                        
                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $student->id }}">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-danger">No Student Found!</td>
            </tr>
        @endforelse
    </tbody>
</table>
