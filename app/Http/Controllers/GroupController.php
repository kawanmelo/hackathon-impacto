<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Services\OpenAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(private readonly OpenAIService $openAIService)
    {
    }

    public function generateReport(Group $group): JsonResponse
    {
        return response()->json($this->openAIService->generateReportForTurma($group->id));
    }

}
