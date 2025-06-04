<?php

namespace App\Http\Controllers;

use App\Models\Variants;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index()
    {
        $variants = Variants::with([
            'product:id,name',
        ])->get();

        return response()->json($variants);
    }
}
