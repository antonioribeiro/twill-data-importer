<?php

namespace A17\TwillDataImporter\Services;

use Illuminate\Support\Arr;

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
