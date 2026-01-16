<?php

namespace App\Models;

use App\Services\ExamPeriodService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class JadwalUjian extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_ujian' => 'date',
        'deadline_submission' => 'datetime',
        'is_published' => 'boolean',
        'jam_mulai' => 'datetime',
        'jam_selesai' => 'datetime',
    ];

    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    public function ruangKelas()
    {
        return $this->belongsTo(RuangKelas::class, 'id_ruang', 'id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to filter by active exam period (Auto-Switch Logic).
     * Uses ExamPeriodService to determine relevant exam type.
     * Fallback: Shows upcoming exams if not in exam period.
     */
    public function scopeForActiveExamPeriod(Builder $query): Builder
    {
        $relevantType = ExamPeriodService::getRelevantExamType();

        if ($relevantType) {
            return $query->where('jenis_ujian', $relevantType);
        }

        // Fallback: Show upcoming exams (Option A from decision)
        return $query->where('tanggal_ujian', '>=', now()->subDays(7));
    }

    /**
     * Scope to filter by semester via kelasKuliah relation.
     */
    public function scopeForSemester(Builder $query, string $semesterId): Builder
    {
        return $query->whereHas('kelasKuliah', function (Builder $q) use ($semesterId) {
            $q->where('id_semester', $semesterId);
        });
    }

    /**
     * Scope to filter by jenis_ujian (UTS/UAS).
     */
    public function scopeJenisUjian(Builder $query, string $type): Builder
    {
        return $query->where('jenis_ujian', $type);
    }
}

