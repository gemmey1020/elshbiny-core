<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitOutQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'step',
        'question_en',
        'question_ar',
        'options',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'step' => 'integer',
            'options' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
