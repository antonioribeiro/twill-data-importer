<?php

namespace A17\TwillDataImporter\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use A17\TwillDataImporter\Models\TwillDataImporter as TwillDataImporterModel;

class TwillDataImporter
{
    use Config;

    protected array|null $config = null;

    protected bool|null $isConfigured = null;

    protected TwillDataImporterModel|null $current = null;

    public function runningOnTwill(): bool
    {
        $prefix = config('twill.admin_route_name_prefix') ?? 'admin.';

        return Str::startsWith((string) Route::currentRouteName(), $prefix);
    }
}
