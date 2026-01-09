<?php

namespace App\Filament\Mahasiswa\Pages;

use Filament\Pages\Page;
use BackedEnum;

class IsiKuesioner extends Page
{
    use \Filament\Forms\Concerns\InteractsWithForms;
    use \Filament\Actions\Concerns\InteractsWithActions;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'filament.mahasiswa.pages.isi-kuesioner';
    protected static ?string $navigationLabel = 'Isi Kuesioner';
    protected ?string $heading = 'Kuesioner Wajib';

    public ?array $data = [];
    public ?\App\Models\SurveyTicket $activeTicket = null;

    public function mount(): void
    {
        $this->activeTicket = \App\Models\SurveyTicket::where('user_id', auth()->id())
            ->where('status', 'PENDING')
            ->orderBy('id') // FIFO
            ->with(['formTemplate', 'reference'])
            ->first();

        if (!$this->activeTicket) {
            // If no tickets, we might want to redirect or show a "All done" state
            // For now, we stay here but the view will handle the empty state
        } else {
            $this->form->fill();
        }
    }

    // Hide navigation if no tickets
    public static function shouldRegisterNavigation(): bool
    {
        return \App\Models\SurveyTicket::where('user_id', auth()->id())
            ->where('status', 'PENDING')
            ->exists();
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        if (!$this->activeTicket) {
            return $form;
        }

        $schemaSnapshot = $this->activeTicket->formTemplate->schema_snapshot;
        $questions = $schemaSnapshot; // Assuming schema_snapshot is the array of questions directly or ['schema_snapshot' => [...]]

        // Handle if it's wrapped
        if (isset($questions['schema_snapshot']) && is_array($questions['schema_snapshot'])) {
            $questions = $questions['schema_snapshot'];
        }

        $formFields = [];

        // Add Header Info (Who are we rating?)
        $targetName = '-';
        if ($this->activeTicket->reference instanceof \App\Models\DosenPengajarKelasKuliah) {
            $targetName = $this->activeTicket->reference->dosenAlias?->nama_dosen . ' (' . $this->activeTicket->reference->kelasKuliah?->matkul?->nama_matkul . ')';
        } elseif ($this->activeTicket->reference instanceof \App\Models\AktivitasKuliahMahasiswa) {
            $targetName = 'Layanan Akademik / Umum';
        }

        $formFields[] = \Filament\Forms\Components\Section::make('Target Evaluasi')
            ->schema([
                \Filament\Forms\Components\Placeholder::make('target_info')
                    ->label('Yang Dinilai')
                    ->content($targetName),
                \Filament\Forms\Components\Placeholder::make('info_anonim')
                    ->label('Privasi Aman')
                    ->content('Jawaban Anda bersifat anonim dan tidak akan mempengaruhi nilai akademik.')
                    ->extraAttributes(['class' => 'text-success-600 font-bold']),
            ]);

        // Build Dynamic Questions
        foreach ($questions as $index => $q) {
            $field = null;
            $name = 'question_' . $index;
            $label = $q['text'];

            if ($q['type'] === 'scale') {
                $field = \Filament\Forms\Components\ToggleButtons::make($name)
                    ->label($label)
                    ->options([
                        1 => '1 - Kurang',
                        2 => '2 - Cukup',
                        3 => '3 - Baik',
                        4 => '4 - Sangat Baik',
                    ])
                    ->inline()
                    ->required();
            } elseif ($q['type'] === 'essay') {
                $field = \Filament\Forms\Components\Textarea::make($name)
                    ->label($label)
                    ->rows(3)
                    ->required();
            } elseif ($q['type'] === 'choice') {
                $options = collect($q['options'] ?? [])->pluck('label', 'value');
                $field = \Filament\Forms\Components\Radio::make($name)
                    ->label($label)
                    ->options($options)
                    ->required();
            }

            if ($field) {
                $formFields[] = $field;
            }
        }

        return $form
            ->schema($formFields)
            ->statePath('data');
    }

    public function submit()
    {
        $ticket = $this->activeTicket;
        if (!$ticket)
            return;

        $data = $this->form->getState();
        $template = $ticket->formTemplate;
        $questions = $template->schema_snapshot; // Re-fetch to map metrics
        if (isset($questions['schema_snapshot']))
            $questions = $questions['schema_snapshot'];

        \Illuminate\Support\Facades\DB::transaction(function () use ($ticket, $data, $questions, $template) {
            // 1. Determine Target ID from Ticket Reference (Polymorphic resolution)
            $targetId = null;
            if ($ticket->reference instanceof \App\Models\DosenPengajarKelasKuliah) {
                $targetId = $ticket->reference->id_dosen_alias; // Dosen ID
            }
            // For general survey, target_id might be null or Prodi ID

            // 2. Calculate Summary Score (Only for Likert)
            $totalScore = 0;
            $countScore = 0;

            foreach ($questions as $index => $q) {
                $key = 'question_' . $index;
                $answer = $data[$key] ?? null;

                if ($q['type'] === 'scale' && is_numeric($answer)) {
                    $totalScore += $answer;
                    $countScore++;
                }
            }

            $finalScore = $countScore > 0 ? round($totalScore / $countScore, 2) : null;

            // 3. Create Ballot (ANONYMOUS - No User ID)
            $ballot = \App\Models\ResponseBallot::create([
                'form_template_id' => $template->id,
                'target_id' => $targetId,
                'answers_full' => $data,
                'calculated_score' => $finalScore,
                'created_at' => now(),
            ]);

            // 4. Create Metric Values (For Reporting)
            $metrics = [];
            foreach ($questions as $index => $q) {
                $key = 'question_' . $index;
                $answer = $data[$key] ?? null;

                if ($q['type'] === 'scale' && !empty($q['metric_key'])) {
                    $metrics[] = [
                        'response_ballot_id' => $ballot->id,
                        'metric_key' => $q['metric_key'],
                        'score' => (int) $answer,
                    ];
                }
            }

            if (!empty($metrics)) {
                \App\Models\ResponseMetricValue::insert($metrics);
            }

            // 5. Mark Ticket as Completed
            $ticket->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
            ]);
        });

        \Filament\Notifications\Notification::make()
            ->title('Terima kasih! Masukan Anda telah tersimpan.')
            ->success()
            ->send();

        // Refresh to check for next ticket
        return redirect()->to(static::getUrl());
    }
}
