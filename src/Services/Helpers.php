<?php

namespace A17\TwillDataImporter\Services;

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
}
