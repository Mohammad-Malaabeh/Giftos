@extends('layouts.admin')

@section('page_title', 'New coupon')

@section('content')
    @include('admin.coupons._form', [
        'coupon' => $coupon,
        'route' => route('admin.coupons.store'),
        'method' => 'POST',
    ])
@endsection
