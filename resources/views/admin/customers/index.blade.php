@extends('layouts.admin')
@section('title','Customers')
@section('page-title','Customers')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                        <th>Orders</th><th>Joined</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($customers as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td class="fw-semibold">{{ $c->name }}</td>
                        <td>{{ $c->email }}</td>
                        <td>{{ $c->phone ?? '—' }}</td>
                        <td><span class="badge bg-primary">{{ $c->orders_count }}</span></td>
                        <td class="text-muted small">{{ $c->created_at->format('d M Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.customers.destroy', $c->id) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete customer {{ $c->name }} and ALL their orders?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No customers yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($customers->hasPages())
        <div class="card-footer bg-white">{{ $customers->links() }}</div>
    @endif
</div>
@endsection
