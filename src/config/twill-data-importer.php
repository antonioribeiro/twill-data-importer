<?php

return [
    /*
     * Importers are organized by data types and mime types
     * Data type is used to define the type of data model it will import to
     * Each importer must have a caption and a list of mime types it supports
     * The mime type must be a valid mime type for the file being imported
     */
    'importers' => [
        'default' => [
            'caption' => 'Select an importer',
        ],

        'artists' => [
            'caption' => 'Artists importer',

            'mime-types' => [
                /** @phpstan-ignore-next-line **/
                'text/csv' => \App\Services\DataImporter\CsvImporter::class,

                /** @phpstan-ignore-next-line **/
                'text/plain' => \App\Services\DataImporter\CsvImporter::class,

                /** @phpstan-ignore-next-line **/
                'application/json' => \App\Services\DataImporter\JsonImporter::class,
            ],
        ],

        'artworks' => [
            'caption' => 'Artworks Importer',

            'mime-types' => [
                /** @phpstan-ignore-next-line **/
                'application/json' => \App\Services\DataImporter\JsonImporter::class,
            ],
        ],
    ],
];
