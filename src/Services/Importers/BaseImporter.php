<?php

namespace A17\TwillDataImporter\Services\Importers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use A17\TwillDataImporter\Models\TwillDataImporter;

abstract class BaseImporter implements Contract
{
    protected TwillDataImporter $file;

    public function import(TwillDataImporter $file): void
    {
        $this->file = $file;

        $contents = $this->readFile();

        if ($contents === false) {
            return;
        }

        if (!$this->checkRequiredColumns($contents)) {
            return;
        }

        $this->saveTotalRecords($contents->count());

        if ($contents->isEmpty()) {
            $this->error(TwillDataImporter::FILE_IS_EMPTY_STATUS);

            return;
        }

        DB::beginTransaction();

        if (!$this->importFile($contents)) {
            DB::rollBack();

            $this->resetSavedErrorMessage();

            return;
        }

        DB::commit();
    }

    public function error(string $error): void
    {
        $this->file->error($error);
    }

    public function resetSavedErrorMessage(): void
    {
        $this->file->resetSavedErrorMessage();
    }

    public function importFile(Collection $contents): bool
    {
        $this->file->imported_records = 0;

        $this->file->save();

        foreach ($contents as $row) {
            if (!$this->importRow($row)) {
                return false;
            }

            $this->file->imported_records++;

            $this->file->save();
        }

        $this->file->imported_at = now();

        $this->file->setStatus(TwillDataImporter::IMPORTED_STATUS);

        return true;
    }

    protected function saveTotalRecords(int $count): void
    {
        $this->file->total_records = $count;

        $this->file->save();
    }

    protected function checkRequiredColumns(Collection $contents): bool
    {
        $diff = $this->requiredColumns()->diff((new Collection($contents->first()))->keys());

        if ($diff->isEmpty()) {
            return true;
        }

        $this->error('Required headers missing from the file: ' . $diff->implode(', '));

        return false;
    }
}
