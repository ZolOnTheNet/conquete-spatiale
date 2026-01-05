<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$systemes = App\Models\SystemeStellaire::all();
$count = 0;

foreach($systemes as $s) {
    $d = sqrt($s->secteur_x*$s->secteur_x + $s->secteur_y*$s->secteur_y + $s->secteur_z*$s->secteur_z);
    if ($d <= 10) $count++;
}

echo "Systèmes stellaires dans 10 AL: $count\n";
