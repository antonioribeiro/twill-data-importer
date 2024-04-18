<?php

use A17\TwillDataImporter\Services\Helpers;
use A17\TwillDataImporter\Services\TwillDataImporter;

if (!function_exists('data_importer')) {
    function data_importer(): TwillDataImporter
    {
        return Helpers::instance();
    }
}

if (!function_exists('csp_nonce')) {
    function csp_nonce(): string
    {
        return Helpers::nounce();
    }
}
