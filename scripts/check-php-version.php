#!/usr/bin/env php
<?php

/**
 * Script de vérification de version PHP
 * Conquête Spatiale
 */

$minVersion = '8.2.0';
$recommendedVersion = '8.3.0';
$currentVersion = PHP_VERSION;

echo "========================================\n";
echo "  Vérification de la version PHP\n";
echo "========================================\n\n";

echo "Version actuelle : PHP $currentVersion\n";
echo "Version minimale : PHP $minVersion\n";
echo "Version recommandée : PHP $recommendedVersion\n\n";

// Vérifier la version minimale
if (version_compare($currentVersion, $minVersion, '<')) {
    echo "❌ ERREUR : Votre version PHP est trop ancienne.\n";
    echo "   Installez PHP $minVersion ou supérieur.\n";
    exit(1);
}

// Vérifier si la version recommandée est installée
if (version_compare($currentVersion, $recommendedVersion, '<')) {
    echo "⚠️  ATTENTION : Vous n'utilisez pas la version recommandée.\n";
    echo "   Considérez la mise à jour vers PHP $recommendedVersion.\n";
    echo "   Le projet fonctionnera mais peut avoir des comportements différents\n";
    echo "   selon l'environnement.\n\n";
} else {
    echo "✅ Parfait ! Vous utilisez une version recommandée de PHP.\n\n";
}

// Afficher les extensions importantes
echo "Extensions requises :\n";

$requiredExtensions = [
    'pdo',
    'pdo_sqlite',
    'mbstring',
    'openssl',
    'curl',
    'zip',
    'fileinfo',
];

$allOk = true;

foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '❌';
    echo "  $status $ext\n";

    if (!$loaded) {
        $allOk = false;
    }
}

echo "\n";

if (!$allOk) {
    echo "⚠️  Certaines extensions sont manquantes.\n";
    echo "   Activez-les dans votre php.ini\n";
    exit(1);
}

echo "✅ Toutes les extensions requises sont installées.\n";
echo "\n========================================\n";

exit(0);
