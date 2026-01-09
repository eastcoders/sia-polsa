<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'schema_snapshot' => 'array',
        'is_active' => 'boolean',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function tickets()
    {
        return $this->hasMany(SurveyTicket::class);
    }

    public function responses()
    {
        return $this->hasMany(ResponseBallot::class);
    }
}
