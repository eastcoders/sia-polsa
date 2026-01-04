<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Wilayah;

echo "\n--- CHECKING SPECIFIC IDS FROM SCREENSHOT ---\n";
$ids = ['030000', '030600'];
foreach ($ids as $id) {
    $w = Wilayah::where('id_wilayah', $id)->first();
    if ($w) {
        echo "ID: $id => Name: '{$w->nama_wilayah}', Level: {$w->id_level_wilayah}, Parent: '{$w->id_induk_wilayah}'\n";
    } else {
        echo "ID: $id not found.\n";
    }
}
