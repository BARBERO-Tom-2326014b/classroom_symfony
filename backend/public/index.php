<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Quand l'app est lancée avec le serveur PHP built-in (php -S),
// on augmente les limites d'upload pour éviter les POST "vides" silencieux.
// (Ces valeurs peuvent être adaptées selon tes besoins.)
if (PHP_SAPI === 'cli-server') {
    // Important: post_max_size doit être >= upload_max_filesize.
    @ini_set('upload_max_filesize', '250M');
    @ini_set('post_max_size', '260M');
    @ini_set('max_file_uploads', '20');
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
