<?php

namespace App\Services;

use App\Models\Finance\ExamDispensation;
use App\Models\Finance\FinancialInvoice;

class FinanceService
{
    /**
     * Check if a student can access exams/KRS based on financial status.
     * 
     * @param string $studentId (ID Registrasi Mahasiswa)
     * @param string $type Context: 'UTS', 'UAS', 'KRS'
     * @return array ['allowed' => bool, 'reason' => string|null]
     */
    public function canAccessExam(string $studentId, string $type = 'UAS'): array
    {
        // 1. Check for Overdue Unpaid Invoices
        $overdueInvoices = FinancialInvoice::where('id_registrasi_mahasiswa', $studentId)
            ->where('status', 'UNPAID')
            ->where('due_date', '<', now()) // Overdue
            ->exists();

        if (!$overdueInvoices) {
            return ['allowed' => true, 'reason' => null];
        }

        // 2. If Overdue, Check for Active Dispensation
        $dispensation = ExamDispensation::where('id_registrasi_mahasiswa', $studentId)
            ->where('type', $type)
            ->where('valid_until', '>=', now())
            ->exists();

        if ($dispensation) {
            return ['allowed' => true, 'reason' => 'Dispensation Active'];
        }

        return ['allowed' => false, 'reason' => 'Outstanding Overdue Invoices'];
    }
}
