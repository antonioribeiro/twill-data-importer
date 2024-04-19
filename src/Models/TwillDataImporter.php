<?php

namespace A17\TwillDataImporter\Models;

use A17\Twill\Models\File;
use A17\Twill\Models\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\TwillDataImporter\Events\FileWasEnqueued;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $data_type
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $imported_at
 * @property string|null $status
 * @property string|null $mime_type
 * @property string|null $base_name
 * @property string|null $error_message
 * @property string|null $imported_records
 * @property string|null $total_records
 */
class TwillDataImporter extends Model
{
    use HasFiles;
    use HasRevisions;

    const string ENQUEUED_STATUS = 'enqueued';
    const string STATUS_MISSING_FILE = 'missing-file';
    const string UNSUPPORTED_FILE_STATUS = 'unsupported-file';
    const string ERROR_STATUS = 'error';
    const string IMPORTED_STATUS = 'imported';
    const string FILE_IS_EMPTY_STATUS = 'file-is-empty';

    protected $table = 'twill_data_importer';

    protected $fillable = ['title', 'data_type', 'status', 'success', 'imported', 'imported_at', 'imported_records', 'total_records', 'mime_type', 'base_name'];

    public array $filesParams = ['data-files'];

    public string|null $localFile;

    public function revisions(): HasMany
    {
        return $this->hasMany($this->getRevisionModel(), 'twill_data_importer_id')->orderBy('created_at', 'desc');
    }

    public function enqueueImport(): void
    {
        if ($this->wasImported()) {
            return;
        }

        $this->info('Enqueuing import');

        $this->setStatus(self::ENQUEUED_STATUS);

        FileWasEnqueued::dispatch($this);
    }

    public function import(): void
    {
        $this->info('Importing data');

        if (!$this->isReady()) {
            return;
        }

        $importer = app($this->getImporterClass());

        $importer->import($this);
    }

    public function wasImported(): bool
    {
        $this->info('Checking if the data was imported');

        return filled($this->imported_at);
    }

    private function isReady(): bool
    {
        if ($this->defaultImporterHasNoClassClass()) {
            $this->info('Default importer has no class.');

            return false;
        }

        if ($this->wasImported()) {
            $this->info('Data was already imported');

            return false;
        }

        if (!$this->hasFiles()) {
            $this->info('No file to import');

            $this->setStatus(self::STATUS_MISSING_FILE);

            return false;
        }

        if ($this->getImporterClass() === null) {
            return false;
        }

        $this->info('Files exists');

        if (!$this->filesAreSupported()) {
            $this->info('Unsupported files');

            $this->setStatus(self::UNSUPPORTED_FILE_STATUS);

            return false;
        }

        return true;
    }

    public function setStatus(string $status): void
    {
        $this->info('Setting status to ' . $status);

        $this->status = $status;

        $this->save();
    }

    private function hasFiles(): bool
    {
        $this->info('Checking if there are files to import');

        return filled($this->getFile());
    }

    private function filesAreSupported(): bool
    {
        $file = $this->getFile();

        if (!$this->isSupportedFile($file)) {
            $this->info('Unsupported file: ' . $file->filename);

            return false;
        }

        $this->info('Files is supported');

        return true;
    }

    private function isSupportedFile(mixed $file): bool
    {
        $this->localFile = $this->getLocalFile($file);

        if (!file_exists($this->localFile)) {
            return false;
        }

        $type = mime_content_type($this->localFile);

        $this->mime_type = ($type === false ? null : $type);

        $this->save();

        return in_array($this->mime_type, $this->getSupportedMimeTypes());
    }

    private function info(mixed $string): void
    {
        \Log::info("DATA IMPORTER: $string");
    }

    public function getLocalFile(File $file): string
    {
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

    private function getSupportedMimeTypes(): array
    {
        return $this->getMimeTypes()->keys()->toArray();
    }

    private function getImporter(): Collection
    {
        return collect(config('twill-data-importer.importers')[$this->data_type] ?? []);
    }

    private function getMimeTypes(): Collection
    {
        return collect($this->getImporter()['mime-types'] ?? null);
    }

    private function getFile(): File|null
    {
        return $this->files()->first();
    }

    private function getImporterClass(): string|null
    {
       $class = $this->getMimeTypes()[$this->mime_type] ?? null;

       if (blank($class)) {
           $this->error('Importer class was not defined for: ' . $this->mime_type);

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

    private function defaultImporterHasNoClassClass(): bool
    {
        if ($this->data_type === 'default' && $this->getImporterClass() === null) {
            return true;
        }

        return false;
    }
}
