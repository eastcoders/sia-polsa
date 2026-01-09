<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseMetricValue extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function responseBallot()
    {
        return $this->belongsTo(ResponseBallot::class);
    }
}
