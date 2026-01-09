<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseBallot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'answers_full' => 'array',
        'calculated_score' => 'float',
    ];

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function metricValues()
    {
        return $this->hasMany(ResponseMetricValue::class);
    }
}
