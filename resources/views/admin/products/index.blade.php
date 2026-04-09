@extends('layouts.admin')
@section('title','Products')
@section('page-title','Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">All Products</h5>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add Product
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Image</th><th>Name</th><th>Category</th>
                        <th>Price</th><th>Stock</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $p)
                    <tr>
                        <td>{{ $p->id }}</td>
                        <td>
                            @if($p->image)
                                <img src="{{ asset('storage/'.$p->image) }}" height="40" class="rounded">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td>{{ $p->category->name ?? '—' }}</td>
                        <td>{{ $p->formattedPrice() }}</td>
                        <td>
                            <span class="badge bg-{{ $p->stock > 10 ? 'success' : ($p->stock > 0 ? 'warning' : 'danger') }}">
                                {{ $p->stock }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.products.edit', $p->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $p->id) }}"
                                  class="d-inline" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No products found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection
