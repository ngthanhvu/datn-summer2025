<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    public function index(Attribute $attribute)
    {
        return response()->json($attribute->values);
    }

    public function store(Request $request, Attribute $attribute)
    {
        $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $value = $attribute->values()->create([
            'value' => $request->value,
        ]);

        return response()->json($value, 201);
    }

    public function show(Attribute $attribute, AttributeValue $value)
    {
        return response()->json($value);
    }

    public function update(Request $request, Attribute $attribute, AttributeValue $value)
    {
        $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $value->update([
            'value' => $request->value,
        ]);

        return response()->json($value);
    }

    public function destroy(Attribute $attribute, AttributeValue $value)
    {
        $value->delete();
        return response()->json(null, 204);
    }
} 