<?php

namespace A17\TwillDataImporter\Services\Importers;

use League\Csv\Reader;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use Illuminate\Support\Str;
use League\Csv\UnavailableStream;
use Illuminate\Support\Collection;

class CsvImporter extends BaseImporter
{
    public function readFile(): Collection|false
    {
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

        if($this->notSameNumberOfColumns($header, $csv->getRecords())) {
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

    public function importRow($row): bool
    {
        return false;
    }

    protected function normalizeHeader(array $header): array
    {
        $header = collect($header)->map(function ($value) {
            return Str::snake(Str::replace(',', '', $value));
        })->toArray();

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
        $file = fopen($this->file->localFile, 'r');

        $header = fgetcsv($file);

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
}
