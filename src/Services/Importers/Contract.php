<?php

namespace A17\TwillDataImporter\Services\Importers;

use Illuminate\Support\Collection;
use A17\TwillDataImporter\Models\TwillDataImporter;

interface Contract
{
    public function import(TwillDataImporter $file): void;

    public function readFile(): Collection|false;

    public function importFile(Collection $contents): bool;

    public function importRow(array $row): bool;

    public function requiredColumns(): Collection;
}
