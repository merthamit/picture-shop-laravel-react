<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Picture;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index(Request $request)
    {
        return response()->json([
            'cartItems' => $this->cart->items($request->user()->id),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'picture_id' => 'required|exists:pictures,id',
        ]);
        $picture = Picture::whereStatus(1)->findOrFail($request->picture_id);

        return response()->json([
            'cartItems' => $this->cart->add($request->user()->id, $picture),
        ]);
    }

    public function destroy(Request $request, $pictureId)
    {
        return response()->json([
            'cartItems' => $this->cart->remove($request->user()->id, (int) $pictureId),
        ]);
    }

    public function clear(Request $request)
    {
        $this->cart->clear($request->user()->id);

        return response()->json([
            'cartItems' => [],
        ]);
    }
}
