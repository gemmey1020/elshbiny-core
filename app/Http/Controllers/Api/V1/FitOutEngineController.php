<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FitOutQuestionResource;
use App\Models\FitOutQuestion;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FitOutEngineController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $questions = FitOutQuestion::query()
            ->where('is_active', true)
            ->orderBy('step')
            ->orderBy('id')
            ->get();

        return FitOutQuestionResource::collection($questions);
    }
}
