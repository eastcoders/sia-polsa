<?php

namespace App\Jobs;

use App\Models\AktivitasKuliahMahasiswa;
use App\Models\FormTemplate;
use App\Models\PesertaKelasKuliah;
use App\Models\SurveyTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSurveyTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200; // 20 minutes for massive generation

    protected FormTemplate $formTemplate;

    /**
     * Create a new job instance.
     */
    public function __construct(FormTemplate $formTemplate)
    {
        $this->formTemplate = $formTemplate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $template = $this->formTemplate;
        $semesterId = $template->semester->id_semester; // Assuming relationship works or accessing id_semester directly via model

        Log::info("Starting Ticket Generation for Template: {$template->title} (Category: {$template->category})");

        if ($template->category === 'UAS_DOSEN') {
            $this->generateUasTickets($template, $semesterId);
        } elseif ($template->category === 'UTS_LAYANAN') {
            $this->generateUtsTickets($template, $semesterId);
        }

        Log::info("Ticket Generation Completed for Template: {$template->title}");
    }

    protected function generateUasTickets(FormTemplate $template, $semesterId)
    {
        // Target: Students in classes for this semester
        // We iterate through PesertaKelasKuliah
        // Optimization: Eager load necessary relationships

        PesertaKelasKuliah::query()
            ->whereHas('kelasKuliah', function ($q) use ($semesterId) {
                $q->where('id_semester', $semesterId);
            })
            ->with([
                'kelasKuliah.dosenPengajarKelasKuliah',
                'riwayatPendidikan.mahasiswa.user' // Fixed: biodata -> mahasiswa
            ])
            ->chunk(100, function ($pesertas) use ($template) {
                $ticketsToInsert = [];
                $timestamp = now();

                foreach ($pesertas as $peserta) {
                    $user = $peserta->riwayatPendidikan?->mahasiswa?->user; // Fixed: biodata -> mahasiswa
    
                    if (!$user) {
                        continue; // Student hasn't activated their account yet
                    }

                    $kelas = $peserta->kelasKuliah;
                    if (!$kelas)
                        continue;

                    foreach ($kelas->dosenPengajarKelasKuliah as $pengajar) {
                        // $pengajar is DosenPengajarKelasKuliah model
                        // We use this as the reference context
    
                        // Check if ticket already exists to avoid duplication
                        $exists = SurveyTicket::where('user_id', $user->id)
                            ->where('form_template_id', $template->id)
                            ->where('reference_type', get_class($pengajar))
                            ->where('reference_id', $pengajar->id)
                            ->exists();

                        if (!$exists) {
                            $ticketsToInsert[] = [
                                'user_id' => $user->id,
                                'form_template_id' => $template->id,
                                'reference_type' => get_class($pengajar),
                                'reference_id' => $pengajar->id,
                                'status' => 'PENDING',
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp,
                            ];
                        }
                    }
                }

                if (count($ticketsToInsert) > 0) {
                    SurveyTicket::insert($ticketsToInsert);
                }
            });
    }

    protected function generateUtsTickets(FormTemplate $template, $semesterId)
    {
        // Target: Active Students in this semester
        // Source: AktivitasKuliahMahasiswa

        AktivitasKuliahMahasiswa::query()
            ->where('id_semester', $semesterId)
            // ->where('id_status_mahasiswa', 'A') // Optional: Filter only active if needed, but usually all enrolled need to fill
            ->with(['riwayatPendidikan.mahasiswa.user']) // Fixed: biodata -> mahasiswa
            ->chunk(200, function ($aktivitasBatch) use ($template) {
                $ticketsToInsert = [];
                $timestamp = now();

                foreach ($aktivitasBatch as $aktivitas) {
                    $user = $aktivitas->riwayatPendidikan?->mahasiswa?->user; // Fixed: biodata -> mahasiswa
    
                    if (!$user) {
                        continue;
                    }

                    // For UTS/Layanan, the reference is the Aktivitas record itself (User's existence in this semester)
                    $exists = SurveyTicket::where('user_id', $user->id)
                        ->where('form_template_id', $template->id)
                        ->where('reference_type', get_class($aktivitas))
                        ->where('reference_id', $aktivitas->id)
                        ->exists();

                    if (!$exists) {
                        $ticketsToInsert[] = [
                            'user_id' => $user->id,
                            'form_template_id' => $template->id,
                            'reference_type' => get_class($aktivitas),
                            'reference_id' => $aktivitas->id,
                            'status' => 'PENDING',
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                if (count($ticketsToInsert) > 0) {
                    SurveyTicket::insert($ticketsToInsert);
                }
            });
    }
}
