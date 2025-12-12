@extends('layouts.admin')

@section('page_title', 'New category')

@section('content')
    @include('admin.categories._form', [
        'category' => $category,
        'parents' => $parents,
        'route' => route('admin.categories.store'),
        'method' => 'POST',
    ])
@endsection
