<?php

namespace A17\TwillDataImporter\Services\Importers;

use Illuminate\Support\Collection;
use A17\TwillDataImporter\Models\TwillDataImporter;

interface Contract
{
    public function import(TwillDataImporter $file): void;

    public function readFile(): Collection;

    public function importFile(Collection $contents): void;

    public function importRow($row): bool;
}
