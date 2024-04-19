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
    public function readFile(): Collection
    {
        try {
            $csv = Reader::createFromPath($this->file->localFile);
        } catch (UnavailableStream) {
            $this->error('Could not read file');

            return collect();
        }

        try {
            $csv->setHeaderOffset(0);
        } catch (Exception) {
            $this->error('Could not process header');

            return collect();
        }

        try {
            $header = $csv->getHeader();
        } catch (SyntaxError) {
            $this->error('Could not read header');

            return collect();
        }

        $header = $this->normalizeHeader($header);

        $data = [];

        try {
            foreach ($csv->getRecords($header) as $record) {
                $data[] = $record;
            }
        } catch (Exception) {
            $this->error('Data error');

            return collect();
        }

        return collect($data);
    }

    public function importRow($row): bool
    {
        return false;
    }

    private function normalizeHeader(array $header): array
    {
        $header = collect($header)->map(function ($value) {
            return Str::snake(Str::replace(',', '', $value));
        })->toArray();

        $this->file->headers = implode("\n\r", $header);

        $this->file->save();

        return $header;
    }
}
