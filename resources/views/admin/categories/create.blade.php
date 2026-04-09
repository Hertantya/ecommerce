{{-- resources/views/admin/categories/create.blade.php --}}
@extends('layouts.admin')
@section('title','Add Category')
@section('page-title','Add Category')
@section('content')
<div class="row justify-content-center"><div class="col-md-5">
<div class="card border-0 shadow-sm p-4">
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
</div></div>
@endsection
