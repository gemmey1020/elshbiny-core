<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FitOutQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'step' => $this->step,
            'question_en' => $this->question_en,
            'question_ar' => $this->question_ar,
            'options' => $this->options,
            'is_active' => $this->is_active,
        ];
    }
}
