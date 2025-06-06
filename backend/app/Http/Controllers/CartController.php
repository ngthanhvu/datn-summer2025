<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Variants;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $query = Cart::with('variant');

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $sessionId = $request->header('X-Session-Id');
            $query->where('session_id', $sessionId);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = Variants::findOrFail($request->variant_id);
        $price = $variant->price; // snapshot

        $data = [
            'variant_id' => $variant->id,
            'quantity' => $request->quantity,
            'price' => $price,
        ];

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        } else {
            $data['session_id'] = $request->header('X-Session-Id');
        }

        $item = Cart::where('variant_id', $variant->id)
            ->where(function ($q) use ($data) {
                if (isset($data['user_id'])) $q->where('user_id', $data['user_id']);
                else $q->where('session_id', $data['session_id']);
            })
            ->first();

        if ($item) {
            $item->quantity += $data['quantity'];
            $item->save();
        } else {
            $item = Cart::create($data);
        }

        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $item = Cart::findOrFail($id);

        // Bảo vệ: chỉ chủ sở hữu mới được sửa
        if (Auth::check() && $item->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = Cart::findOrFail($id);

        if (Auth::check() && $item->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
