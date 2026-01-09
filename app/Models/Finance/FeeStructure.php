<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Prodi; // Assuming Prodi model exists in App\Models or App\Models\Master

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'angkatan',
        'prodi_id',
        'waktu_kuliah_enum', // Updated column name
        'fee_component_id',
        'amount',
    ];

    public function component()
    {
        return $this->belongsTo(FeeComponent::class, 'fee_component_id');
    }

    // Relationships to other modules (Assuming standard filenames)
    // Adjust if models are in specific subfolders like App\Models\Master
    public function prodi()
    {
        return $this->belongsTo(\App\Models\Prodi::class);
    }
}
