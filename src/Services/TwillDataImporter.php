<?php

namespace A17\TwillDataImporter\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use A17\DataImporter\DataImporter;
use A17\TwillDataImporter\Models\TwillDataImporter as TwillDataImporterModel;

class TwillDataImporter
{
    use Config;
    use Middleware;

    protected array|null $config = null;

    protected bool|null $isConfigured = null;

    protected bool|null $protected = null;

    protected TwillDataImporterModel|null $current = null;

    protected string|null $nounce = null;

    public function runningOnTwill(): bool
    {
        $prefix = config('twill.admin_route_name_prefix') ?? 'admin.';

        return Str::startsWith((string) Route::currentRouteName(), $prefix);
    }

    public function getAvailableHeaders(): Collection
    {
        return (new Collection($this->config('headers')))->reject(fn($header) => !$header['available']);
    }

    public function nounce(): string
    {
        return $this->nounce ??= Str::random(32);
    }
}
