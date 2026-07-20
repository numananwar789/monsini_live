<?php

namespace App\Http\Controllers;

use App\Models\SubProduct;
use Illuminate\Http\Request;

class SubProductController extends Controller
{
    public function index()
    {
        $subProducts = SubProduct::all();
        return view('sub_products.index', compact('subProducts'));
    }



    public function show(SubProduct $subProduct)
    {
        return $subProduct;
    }





    public function store(Request $request)
    {
        $request->validate([
            'sub_product_name' => 'required|string|max:255',
        ]);

        $subProduct = SubProduct::create($request->all());

        return response()->json($subProduct);
    }

    public function update(Request $request, SubProduct $subProduct)
    {
        $request->validate([
            'sub_product_name' => 'required|string|max:255',
        ]);

        $subProduct->update($request->all());

        return response()->json($subProduct);
    }

    public function destroy(SubProduct $subProduct)
    {
        $subProduct->delete();
        return response()->json(['success' => true]);
    }
}
