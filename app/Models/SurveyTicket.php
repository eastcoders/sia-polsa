<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyTicket extends Model
{
    protected $guarded = [];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formTemplate()
    {
        return $this->belongsTo(FormTemplate::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
