<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display all the orders
     */
    public function index(Request $request)
    {
        $orders = Order::with(['user:id,name,email', 'picture'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->whereHas('user', fn ($u) => $u->where('name', 'like', '%' . $request->q . '%'))
                ->orWhereHas('picture', fn ($p) => $p->withTrashed()->where('title', 'like', '%' . $request->q . '%'))))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Delete order
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return back()->with([
            'success' => __('admin.flash.order_deleted'),
        ]);
    }
}
