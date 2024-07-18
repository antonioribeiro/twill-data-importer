<?php

namespace A17\TwillDataImporter\Services\Importers;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use A17\TwillDataImporter\Models\TwillDataImporter;

abstract class BaseImporter implements Contract
{
    protected TwillDataImporter $file;

    protected Collection $errors;

    public function __construct()
    {
        $this->errors = collect();
    }

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

        if (!$this->validateContents($contents)) {
            $this->error($this->errors->toArray());

            $this->errorStatus(TwillDataImporter::VALIDATION_ERROR_STATUS);

            return;
        }

        $this->saveTotalRecords($contents->count());

        if ($contents->isEmpty()) {
            $this->error(TwillDataImporter::FILE_IS_EMPTY_STATUS);

            return;
        }

        $this->importFile($contents);

        $this->error('Imported sucessfully.');
    }

    abstract public function importRow(array $row): bool;

    abstract public function requiredColumns(): Collection;

    public function error(string|array $error): void
    {
        $this->file->error($error);
    }

    public function errorStatus(string $status): void
    {
        $this->file->errorStatus($status);
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

    protected function validateContents(Collection $contents): bool
    {
        $isValid = true;

        foreach ($contents as $row) {
            $isValid = $isValid && $this->validateRow($row)['valid'];
        }

        return $isValid;
    }

    public function normalizeColumnName(string|null $value): string
    {
        return Str::snake(Str::camel(Str::slug($value ?? '')));
    }

    public function validateRow(array $row): array
    {
        return [
            'valid' => true,

            'errors' => [],
        ];
    }
}
