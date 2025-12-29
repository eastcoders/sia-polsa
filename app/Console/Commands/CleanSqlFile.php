<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanSqlFile extends Command
{
    protected $signature = 'sql:clean 
                            {input : File SQL input}
                            {output? : File SQL output}';

    protected $description = 'Bersihkan file SQL dari karakter bermasalah';

    public function handle()
    {
        $inputFile = $this->argument('input');
        $outputFile = $this->argument('output') ?: $inputFile.'.clean';

        if (! File::exists($inputFile)) {
            $this->error("File tidak ditemukan: {$inputFile}");

            return;
        }

        $content = File::get($inputFile);

        // 1. Escape single quotes di dalam string
        $content = preg_replace_callback(
            "/'(.*?)'/s",
            function ($matches) {
                $text = $matches[1];
                // Escape single quotes kecuali yang sudah di-escape
                $text = preg_replace("/(?<!\\\\)'/", "\\'", $text);

                return "'".$text."'";
            },
            $content
        );

        // 2. Ganti smart quotes dengan regular quotes
        $content = str_replace(
            ['\'', '"', '`', '´', '‘', '’', '‚', '‛', '`'],
            ["'", '"', '`', "'", "'", "'", "'", "'", "'"],
            $content
        );

        // 3. Hapus NULL byte
        $content = str_replace("\0", '', $content);

        // 4. Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // 5. Hapus BOM (Byte Order Mark)
        if (substr($content, 0, 3) == "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        File::put($outputFile, $content);

        $this->info("✅ File telah dibersihkan: {$outputFile}");
        $this->info('   Size asli: '.filesize($inputFile).' bytes');
        $this->info('   Size baru: '.filesize($outputFile).' bytes');
    }
}
