@extends('layouts.admin')

@section('page_title', 'New product')

@section('content')
    @include('admin.products._form', [
        'product' => $product,
        'categories' => $categories,
        'route' => route('admin.products.store'),
        'method' => 'POST',
    ])
@endsection
