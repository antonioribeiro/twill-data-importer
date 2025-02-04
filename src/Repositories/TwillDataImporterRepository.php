<?php

namespace A17\TwillDataImporter\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleFiles;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\TwillDataImporter\Models\TwillDataImporter;

/**
 * @method \Illuminate\Database\Eloquent\Builder published()
 */
class TwillDataImporterRepository extends ModuleRepository
{
    use HandleFiles;
    use HandleRevisions;

    public function __construct(TwillDataImporter $model)
    {
        $this->model = $model;
    }

    public function beforeSave(TwillModelContract $object, array $fields): void
    {
        if (!isset($object->canBeEdited) || !$object->canBeEdited) {
            throw new \Exception('This import is locked and cannot be edited.');
        }

        parent::beforeSave($object, $fields);
    }

    /**
     * @param TwillDataImporter $model
     * @param array $fields
     * @return void
     */
    public function afterSave($model, $fields): void
    {
        parent::afterSave($model, $fields);

        if ($fields['clear_log'] ?? false) {
            $model->error_message = null;

            $model->save();
        }

        if ($fields['import_after_update'] ?? false) {
            $model->enqueueImport();
        }
    }
}
