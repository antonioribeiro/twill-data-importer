<?php

namespace A17\TwillDataImporter\Models;

use A17\Twill\Models\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use A17\TwillDataImporter\Events\FileWasEnqueued;

/*
 * This trait exists just to separate the logic from the model
 */
trait ImporterModelTrait
{
    public string|null $localFile;

    public function enqueueImport(): void
    {
        if ($this->wasImported) {
            return;
        }

        $this->setStatus(self::ENQUEUED_STATUS);

        FileWasEnqueued::dispatch($this);
    }

    public function import(): void
    {
        if (!$this->isReady()) {
            return;
        }

        $importer = app($this->getImporterClass());

        $importer->import($this);
    }

    protected function isReady(): bool
    {
        if (!$this->hasFile()) {
            return false;
        }

        if ($this->defaultImporterHasNoClassClass()) {
            return false;
        }

        if ($this->wasImported) {
            return false;
        }

        if ($this->getImporterClass() === null) {
            return false;
        }

        if (!$this->filesAreSupported()) {
            $this->setStatus(self::UNSUPPORTED_FILE_STATUS);

            return false;
        }

        return true;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;

        $this->save();
    }

    protected function hasFile(): bool
    {
        return filled($this->getFile());
    }

    protected function filesAreSupported(): bool
    {
        $file = $this->getFile();

        if (blank($file)) {
            $this->error('File is empty');

            return false;
        }

        if (!$this->isSupportedFile()) {
            return false;
        }

        return true;
    }

    protected function isSupportedFile(): bool
    {
        return in_array($this->mime_type, $this->getSupportedMimeTypes());
    }

    public function getLocalFile(File|null $file): string|null
    {
        if ($file === null) {
            return null;
        }

        /** @phpstan-ignore-next-line */
        $fileName = storage_path('app/tmp/' . $file->filename);

        $baseName = basename($fileName);

        $this->base_name = $baseName;

        $this->save();

        /** @phpstan-ignore-next-line */
        $contents = Storage::disk(config('twill.file_library.disk'))->get($file->uuid);

        if (!is_dir(dirname($fileName))) {
            mkdir(dirname($fileName), 0777, true);
        }

        file_put_contents($fileName, $contents);

        return $fileName;
    }

    protected function getSupportedMimeTypes(): array
    {
        return $this->getMimeTypes()->keys()->toArray();
    }

    protected function getImporters(): Collection
    {
        return new Collection(config('twill-data-importer.importers'));
    }

    protected function getImporter(): Collection|null
    {
        $importers = $this->getImporters();

        $importer = $importers[$this->data_type] ?? [];

        if (blank($importer)) {
            if (count($importers) === 1 && $this->data_type === 'default') {
                $importer = $importers->first();
            } else {
                $this->error(
                    "Importer was not defined for the data type '$this->data_type'. Check the configuration file.",
                );

                return null;
            }
        }

        return new Collection($importer);
    }

    protected function getMimeTypes(): Collection|null
    {
        $importer = $this->getImporter()['mime-types'] ?? null;

        if (blank($importer)) {
            return null;
        }

        return new Collection($importer);
    }

    protected function getFile(): File|null
    {
        $file = $this->files()->first();

        $this->localFile = $this->getLocalFile($file);

        if (blank($this->localFile) || blank($file)) {
            $this->error('File was not specified.');

            return null;
        }

        if (!file_exists($this->localFile)) {
            /** @phpstan-ignore-next-line */
            $this->error("File not found: $this->file?->localFile");

            return null;
        }

        $type = $this->getMimeType($this->localFile);

        $this->mime_type = $type === false ? null : $type;

        $this->base_name = basename($this->localFile);

        $this->save();

        return $file;
    }

    protected function getImporterClass(): string|null
    {
        $class = $this->getMimeTypes()[$this->mime_type] ?? null;

        if (blank($this->getMimeTypes()) && $this->data_type === 'default' && count($this->getImporters()) > 1) {
            $this->error('Data type to import not selected.');

            return null;
        }

        if (blank($class)) {
            $this->error(
                "Importer class was not defined for the data type '$this->data_type' and mime type '$this->mime_type'. Check the configuration file.",
            );

            return null;
        }

        if (!class_exists($class)) {
            $this->error('Importer class does not exist: ' . $class);

            return null;
        }

        return $class;
    }

    public function error(string|array $error): void
    {
        if (is_array($error)) {
            $error = implode("\n", $error);
        }

        $this->setStatus(TwillDataImporter::ERROR_STATUS);

        $startedAt = '--- ' . ((string) now()) . ' ----------------------------------------------------------------';

        $this->error_message = $startedAt . "\n\n" . $error . "\n\n" . $this->error_message;

        $this->save();
    }

    public function errorStatus(string $status): void
    {
        $this->setStatus($status);

        $this->save();
    }

    protected function defaultImporterHasNoClassClass(): bool
    {
        if ($this->data_type === 'default' && $this->getImporterClass() === null) {
            return true;
        }

        return false;
    }

    protected function getMimeType(string $file): string
    {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        $mime_types = [
            'json' => 'application/json',
            'yml' => 'application/yaml',
            'yaml' => 'application/yaml',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'html' => 'text/html',
        ];

        if (isset($mime_types[$extension])) {
            return $mime_types[$extension];
        }

        $type = mime_content_type($file);

        if ($type === false) {
            $type = 'application/octet-stream'; // Generic binary type
        }

        return $type;
    }
}
