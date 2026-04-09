@extends('layouts.admin')
@section('title','Add Product')
@section('page-title','Add New Product')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">— No Category —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Price (Rp) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" min="0" step="500"
                           value="{{ old('price', 0) }}" required>
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Stock <span class="text-danger">*</span></label>
                    <input type="number" name="stock" class="form-control" min="0"
                           value="{{ old('stock', 0) }}" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Product Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <div class="form-text">JPG / PNG, max 2 MB</div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Save Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
