<?php

namespace A17\TwillDataImporter\Services\Importers;

use League\Csv\Reader;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use Illuminate\Support\Str;
use League\Csv\UnavailableStream;
use Illuminate\Support\Collection;

abstract class CsvImporter extends BaseImporter
{
    public function readFile(): Collection|false
    {
        if (blank($this->file->localFile)) {
            $this->error('File was not specified.');

            return false;
        }

        if (!file_exists($this->file->localFile)) {
            $this->error("File not found: $this->file->localFile");

            return false;
        }

        if ($this->fileHasAnomaly()) {
            $this->error('File has an anomalies: not the same humber of columns in all rows.');

            return false;
        }

        try {
            $csv = Reader::createFromPath($this->file->localFile);
        } catch (UnavailableStream) {
            $this->error('Could not read file');

            return false;
        }

        try {
            $csv->setHeaderOffset(0);
        } catch (Exception) {
            $this->error('Could not process header');

            return false;
        }

        try {
            $header = $csv->getHeader();
        } catch (SyntaxError) {
            $this->error('Could not read header');

            return false;
        }

        $header = $this->normalizeHeader($header);

        $data = [];

        if ($this->notSameNumberOfColumns($header, $csv->getRecords())) {
            $this->error('One or more records does not have the same number of columns as the header.');

            return false;
        }

        try {
            foreach ($csv->getRecords($header) as $record) {
                $data[] = $record;
            }
        } catch (Exception) {
            $this->error('Data error');

            return false;
        }

        return collect($data);
    }

    public function importRow(array $row): bool
    {
        return false;
    }

    protected function normalizeHeader(array $header): array
    {
        $header = collect($header)
            ->map(function ($value) {
                return Str::snake(Str::camel(Str::slug($value)));
            })
            ->toArray();

        $this->file->headers = implode("\n\r", $header);

        $this->file->save();

        return $header;
    }

    protected function notSameNumberOfColumns(array $header, \Iterator $records): bool
    {
        $count = count($header);

        foreach ($records as $record) {
            if (count($record) !== $count) {
                return true;
            }
        }

        return false;
    }

    protected function fileHasAnomaly(): bool
    {
        if (blank($this->file->localFile)) {
            return true;
        }

        $file = fopen($this->file->localFile, 'r');

        if ($file === false) {
            return true;
        }

        $header = fgetcsv($file);

        if (!is_countable($header)) {
            return true;
        }

        $numColumns = count($header);

        $hasAnomaly = false;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) !== $numColumns) {
                $hasAnomaly = true;

                break;
            }
        }

        fclose($file);

        return $hasAnomaly;
    }

    abstract public function requiredColumns(): Collection;
}
