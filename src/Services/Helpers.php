<?php

namespace A17\TwillDataImporter\Services;

use Illuminate\Support\Str;
use A17\TwillDataImporter\Services\TwillDataImporter;
use A17\TwillDataImporter\Support\Facades\TwillDataImporter as TwillDataImporterFacade;

class Helpers
{
    public static function load(): void
    {
        require __DIR__ . '/../Support/helpers.php';
    }

    public static function instance(): TwillDataImporter
    {
        if (!app()->bound('data-importer')) {
            app()->singleton('data-importer', fn() => new TwillDataImporter());
        }

        return app('data-importer');
    }

    public static function nounce(): string
    {
        return TwillDataImporterFacade::nounce();
    }
}
