@extends('layouts.admin')

@section('page_title', 'Edit product')

@section('content')
    @include('admin.products._form', [
        'product' => $product,
        'categories' => $categories,
        'route' => route('admin.products.update', $product),
        'method' => 'PUT',
    ])
@endsection
