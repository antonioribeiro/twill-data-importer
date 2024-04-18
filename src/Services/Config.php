<?php

namespace A17\TwillDataImporter\Services;

use Illuminate\Support\Arr;
use A17\TwillDataImporter\Repositories\TwillDataImporterRepository;
use A17\TwillDataImporter\Models\TwillDataImporter as TwillDataImporterModel;

trait Config
{
    public function config(string|null $key = null, mixed $default = null): mixed
    {
        $this->config ??= filled($this->config) ? $this->config : (array) config('twill-data-importer');

        if (blank($key)) {
            return $this->config;
        }

        return Arr::get((array) $this->config, $key) ?? $default;
    }
}
