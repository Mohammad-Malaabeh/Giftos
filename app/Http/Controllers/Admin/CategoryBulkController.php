<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryBulkController extends Controller
{
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'in:activate,deactivate'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:categories,id'],
        ]);

        $status = $data['action'] === 'activate';
        $affected = Category::whereIn('id', $data['ids'])->update(['status' => $status]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'action' => $data['action'], 'affected' => $affected]);
        }

        return back()->with('success', 'Selected categories updated.');
    }

    
}
