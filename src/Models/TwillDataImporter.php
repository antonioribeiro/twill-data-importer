<?php

namespace A17\TwillDataImporter\Models;

use A17\Twill\Models\Model;
use Illuminate\Support\Carbon;
use A17\Twill\Models\Behaviors\HasFiles;
use A17\Twill\Models\Behaviors\HasRevisions;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $data_type
 * @property string|null $title
 * @property string|null $status
 * @property string|null $mime_type
 * @property string|null $base_name
 * @property string|null $error_message
 * @property Carbon|null $imported_at
 * @property int|null $imported_records
 * @property int|null $total_records
 * @property string|null $headers
 * @property bool $canBeEdited
 */
class TwillDataImporter extends Model
{
    use HasFiles;
    use HasRevisions;
    use ImporterModelTrait;

    public const ENQUEUED_STATUS = 'enqueued';
    public const RUNNING_STATUS = 'running';
    public const IMPORTED_STATUS = 'imported';
    public const STATUS_MISSING_FILE = 'missing-file';
    public const UNSUPPORTED_FILE_STATUS = 'unsupported-file';
    public const ERROR_STATUS = 'error';
    public const FILE_IS_EMPTY_STATUS = 'file-is-empty';
    public const VALIDATION_ERROR_STATUS = 'validation-error';
    public const ZERO_RECORDS_IMPORTED_STATUS = 'zero-records-imported';

    public const STATUSES = [
        self::ENQUEUED_STATUS => 'Enqueued',
        self::STATUS_MISSING_FILE => 'Missing file',
        self::UNSUPPORTED_FILE_STATUS => 'Unsupported file',
        self::ERROR_STATUS => 'Error',
        self::IMPORTED_STATUS => 'Successfully imported',
        self::FILE_IS_EMPTY_STATUS => 'File is empty',
        self::ZERO_RECORDS_IMPORTED_STATUS => 'Zero records imported',
    ];

    protected $table = 'twill_data_importer';

    protected $fillable = [
        'title',
        'data_type',
        'status',
        'success',
        'imported',
        'imported_at',
        'imported_records',
        'total_records',
        'mime_type',
        'base_name',
        'headers',
        'error_message',
    ];

    public array $filesParams = ['data-files'];

    public function revisions(): HasMany
    {
        return $this->hasMany($this->getRevisionModel(), 'twill_data_importer_id')->orderBy('created_at', 'desc');
    }

    public function getStatusForHumansAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }

    public function getCanBeEditedAttribute(): bool
    {
        return !$this->wasImported;
    }

    public function getWasImportedAttribute(): bool
    {
        return isset($this->status) && $this->status === TwillDataImporter::IMPORTED_STATUS && $this->imported_at !== null && $this->imported_records > 0;
    }
}
