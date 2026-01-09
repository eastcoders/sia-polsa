<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // RECURRING, ONE_TIME
    ];

    public function structures()
    {
        return $this->hasMany(FeeStructure::class);
    }
}
