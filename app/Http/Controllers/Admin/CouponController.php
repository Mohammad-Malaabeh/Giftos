<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Coupon;
use App\Support\Activity;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $coupons = Coupon::query()
            ->when($q, fn($qq) => $qq->where('code', 'like', "%{$q}%"))
            ->latest('id')
            ->paginate(20)->withQueryString();

        return view('admin.coupons.index', compact('coupons', 'q'));
    }

    public function create()
    {
        $coupon = new Coupon();
        return view('admin.coupons.create', compact('coupon'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => ['required', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'active' => ['required', 'boolean'],
        ]);
        $coupon = Coupon::create($data);
        Activity::log('coupon.created', $coupon, ['code' => $coupon->code]);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => ['required', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'active' => ['required', 'boolean'],
        ]);
        $coupon->update($data);
        Activity::log('coupon.updated', $coupon, ['dirty' => array_keys($coupon->getChanges())]);
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        Activity::log('coupon.deleted', $coupon, ['code' => $coupon->code]);
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }
}
