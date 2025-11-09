<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use Illuminate\Http\JsonResponse;

class DisciplineController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Discipline::query()
                ->select(['id', 'name', 'code'])
                ->orderBy('name')
                ->get()
        );
    }
}
