@extends('layouts.admin')

@section('page_title', 'Edit category')

@section('content')
    @include('admin.categories._form', [
        'category' => $category,
        'parents' => $parents,
        'route' => route('admin.categories.update', $category),
        'method' => 'PUT',
    ])
@endsection
