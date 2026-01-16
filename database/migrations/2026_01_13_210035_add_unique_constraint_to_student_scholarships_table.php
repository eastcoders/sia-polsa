<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Add unique constraint to prevent duplicate scholarship assignments
     * for the same student in the same period.
     *
     * Business Rule: A student cannot have the same scholarship
     * starting on the same date (valid_from).
     */
    public function up(): void
    {
        Schema::table('student_scholarships', function (Blueprint $table) {
            // Composite unique index: one student can only have one scholarship
            // of the same type starting on the same date
            $table->unique(
                ['id_registrasi_mahasiswa', 'scholarship_id', 'valid_from'],
                'unique_student_scholarship_period'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_scholarships', function (Blueprint $table) {
            $table->dropUnique('unique_student_scholarship_period');
        });
    }
};
