<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Sl</th>
            <th>Product Name</th>
            <th>Description</th>
            <th>Price</th>
            <th width="180px">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $key => $product)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->description }}</td>
                <td>${{ number_format($product->price, 2) }}</td>
                <td>
                    <div class="d-flex gap-2">
                        
                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                            Edit
                        </a>

                        
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            @csrf
                            @method('DELETE') 
                            <button type="submit" class="btn btn-danger btn-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-danger">No Product Found!</td>
            </tr>
        @endforelse
    </tbody>
</table>
