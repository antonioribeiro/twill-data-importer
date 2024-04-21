<?php

namespace A17\TwillDataImporter\Services\Importers;

use Illuminate\Support\Collection;

abstract class JsonImporter extends BaseImporter
{
    public function readFile(): Collection|false
    {
        if ($this->file->localFile === null) {
            return false;
        }

        $contents = file_get_contents($this->file->localFile);

        if (blank($contents) || $contents === false) {
            $this->error("File is empty: $this->file->localFile");

            return false;
        }

        $array = json_decode($contents, true);

        if ($this->jsonContainErrors()) {
            return false;
        }

        return collect($this->normalizeKeys($array));
    }

    protected function normalizeKeys(array $array): array
    {
        $keys = [];

        $array = (new Collection($array))
            ->map(function ($value) use (&$keys) {
                return (new Collection($value))
                    ->mapWithKeys(function ($item, $key) use (&$keys) {
                        $keys[$key] = $key = $this->normalizeColumnName($key);

                        return [$key => $item];
                    })
                    ->toArray();
            })
            ->toArray();

        $this->file->headers = implode("\n\r", $keys);

        $this->file->save();

        return $array;
    }

    protected function jsonContainErrors(): bool
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return false;
            case JSON_ERROR_DEPTH:
                $this->error('JSON ERROR: Maximum stack depth exceeded');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $this->error('JSON ERROR: Underflow or the modes mismatch');
                break;
            case JSON_ERROR_CTRL_CHAR:
                $this->error('JSON ERROR: Unexpected control character found');
                break;
            case JSON_ERROR_SYNTAX:
                $this->error('JSON ERROR: Syntax error, malformed JSON');
                break;
            case JSON_ERROR_UTF8:
                $this->error('JSON ERROR: Malformed UTF-8 characters, possibly incorrectly encoded');
                break;
            case JSON_ERROR_RECURSION:
                $this->error('JSON ERROR: One or more recursive references in the value to be encoded');
                break;
            case JSON_ERROR_INF_OR_NAN:
                $this->error('JSON ERROR: One or more NAN or INF values in the value to be encoded');
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $this->error('JSON ERROR: A value of a type that cannot be encoded was given');
                break;
            case JSON_ERROR_INVALID_PROPERTY_NAME:
                $this->error('JSON ERROR: A property name that cannot be encoded was given');
                break;
            case JSON_ERROR_UTF16:
                $this->error('JSON ERROR: Malformed UTF-16 characters, possibly incorrectly encoded');
                break;
            default:
                $this->error('JSON ERROR: Unknown error');
                break;
        }

        return true;
    }
}
