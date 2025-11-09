<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function listProducts(): JsonResponse
    {
        return response()->json(Product::all());
    }


    public function buy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $student = Student::query()->findOrFail($validated['student_id']);
        $product = Product::query()->findOrFail($validated['product_id']);

        if ($student->coins < $product->price) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo insuficiente para comprar este produto.',
                'student_coins' => $student->coins,
                'product_price' => $product->price,
            ], 400);
        }

        $student->coins -= $product->price;
        $student->save();


        return response()->json([
            'success' => true,
            'message' => "Produto '{$product->name}' comprado com sucesso!",
            'remaining_coins' => $student->coins,
        ]);
    }
}
