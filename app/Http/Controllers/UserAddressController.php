<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();
        return view('addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'zip' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'is_default_shipping' => ['sometimes', 'boolean'],
            'is_default_billing' => ['sometimes', 'boolean'],
        ]);

        $data['user_id'] = $request->user()->id;

        // Ensure only one default
        if (!empty($data['is_default_shipping'])) {
            $request->user()->addresses()->update(['is_default_shipping' => false]);
            $data['is_default_shipping'] = true;
        }
        if (!empty($data['is_default_billing'])) {
            $request->user()->addresses()->update(['is_default_billing' => false]);
            $data['is_default_billing'] = true;
        }

        UserAddress::create($data);

        return back()->with('success', 'Address saved.');
    }

    public function update(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'zip' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2'],
            'is_default_shipping' => ['sometimes', 'boolean'],
            'is_default_billing' => ['sometimes', 'boolean'],
        ]);

        if (!empty($data['is_default_shipping'])) {
            $request->user()->addresses()->update(['is_default_shipping' => false]);
            $data['is_default_shipping'] = true;
        } else {
            $data['is_default_shipping'] = $address->is_default_shipping;
        }

        if (!empty($data['is_default_billing'])) {
            $request->user()->addresses()->update(['is_default_billing' => false]);
            $data['is_default_billing'] = true;
        } else {
            $data['is_default_billing'] = $address->is_default_billing;
        }

        $address->update($data);

        return back()->with('success', 'Address updated.');
    }

    public function destroy(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->delete();
        return back()->with('success', 'Address removed.');
    }

    public function setDefault(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $type = $request->validate([
            'type' => ['required', 'in:shipping,billing']
        ])['type'];

        if ($type === 'shipping') {
            $request->user()->addresses()->update(['is_default_shipping' => false]);
            $address->is_default_shipping = true;
        } else {
            $request->user()->addresses()->update(['is_default_billing' => false]);
            $address->is_default_billing = true;
        }
        $address->save();

        return back()->with('success', 'Default updated.');
    }
}
