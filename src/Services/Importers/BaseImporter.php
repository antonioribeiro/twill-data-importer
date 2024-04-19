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

        $this->saveTotalRecords($contents->count());

        if ($contents->isEmpty()) {
            $this->error(TwillDataImporter::FILE_IS_EMPTY_STATUS);

            return;
        }

        $this->importFile($contents);
    }

    public function error(string $error): void
    {
        $this->file->error($error);
    }

    public function importFile(Collection $contents): void
    {
        $this->file->imported_records = 0;

        $this->file->save();

        foreach ($contents as $row) {
            if (!$this->importRow($row)) {
                return;
            }
        }

        $this->file->imported_at = now();

        $this->file->setStatus(TwillDataImporter::IMPORTED_STATUS);
    }

    private function saveTotalRecords(int $count): void
    {
        $this->file->total_records = $count;

        $this->file->save();
    }
}
