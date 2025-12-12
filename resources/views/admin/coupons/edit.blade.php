@extends('layouts.admin')

@section('page_title', 'Edit coupon')

@section('content')
    @include('admin.coupons._form', [
        'coupon' => $coupon,
        'route' => route('admin.coupons.update', $coupon),
        'method' => 'PUT',
    ])
@endsection
