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
        if ($this->wasImported()) {
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

    public function wasImported(): bool
    {
        return filled($this->imported_at);
    }

    protected function isReady(): bool
    {
        if ($this->defaultImporterHasNoClassClass()) {
            return false;
        }

        if ($this->wasImported()) {
            return false;
        }

        if (!$this->hasFile()) {
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

    protected function getImporter(): Collection
    {
        return new Collection($this->getImporters()[$this->data_type] ?? []);
    }

    protected function getMimeTypes(): Collection
    {
        return new Collection($this->getImporter()['mime-types'] ?? null);
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

        $type = mime_content_type($this->localFile);

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
                "Importer class was not defined for the data type '$this->data_type'. Check the configuration file.",
            );

            return null;
        }

        if (!class_exists($class)) {
            $this->error('Importer class does not exist: ' . $class);

            return null;
        }

        return $class;
    }

    public function error(string $error): void
    {
        $this->setStatus(TwillDataImporter::ERROR_STATUS);

        $this->error_message = $error;

        $this->save();
    }

    protected function defaultImporterHasNoClassClass(): bool
    {
        if ($this->data_type === 'default' && $this->getImporterClass() === null) {
            return true;
        }

        return false;
    }
}
