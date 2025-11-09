<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{
    public function show(Student $student): JsonResponse
    {
        return response()->json($student);
    }
}
