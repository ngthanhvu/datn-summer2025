<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->get();
        return response()->json($attributes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $attribute = Attribute::create([
            'name' => $request->name,
            'code' => Str::slug($request->name),
        ]);

        return response()->json($attribute, 201);
    }

    public function show(Attribute $attribute)
    {
        return response()->json($attribute->load('values'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $attribute->update([
            'name' => $request->name,
            'code' => Str::slug($request->name),
        ]);

        return response()->json($attribute);
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return response()->json(null, 204);
    }
} 