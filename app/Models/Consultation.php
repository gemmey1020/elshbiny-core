<?php

namespace App\Models;

use App\Models\Concerns\RecordsCrmActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;
    use RecordsCrmActivity;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'company',
        'message',
        'interest_type',
        'source_page',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'status',
    ];

    protected $attributes = [
        'status' => 'new',
    ];
}
