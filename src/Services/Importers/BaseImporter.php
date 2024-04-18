<?php

namespace A17\TwillDataImporter\Services\Importers;

use Illuminate\Support\Collection;
use A17\TwillDataImporter\Models\TwillDataImporter;

abstract class BaseImporter implements Contract
{
    protected TwillDataImporter $file;

    public function import(TwillDataImporter $file): void
    {
        $this->file = $file;

        $contents = $this->readFile();

        $this->importFile($contents);
    }

    public function error(string $error): void
    {
        $this->file->setStatus(TwillDataImporter::ERROR_STATUS);

        $this->file->error_message = $error;

        $this->file->save();
    }

    public function importFile(Collection $contents): void
    {
        foreach ($contents as $row) {
            if (!$this->importRow($row)) {
                return;
            }
        }

        $this->file->imported_at = now();

        $this->file->setStatus(TwillDataImporter::IMPORTED_STATUS);
    }
}
