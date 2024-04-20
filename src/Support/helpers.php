<?php

use A17\TwillDataImporter\Services\Helpers;
use A17\TwillDataImporter\Services\TwillDataImporter;

if (!function_exists('data_importer')) {
    function data_importer(): TwillDataImporter
    {
        return Helpers::instance();
    }
}
