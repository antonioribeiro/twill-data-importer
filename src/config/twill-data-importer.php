<?php

return [
    'enabled' => env('TWILL_DATA_IMPORTER_ENABLED', true),

    // By mime type
    'importers' => [
//        'application/json' => \A17\TwillDataImporter\Services\Importers\CsvImporter::class,
        'text/csv' => \App\Services\DataImporter\CsvImporter::class,
    ],
];
