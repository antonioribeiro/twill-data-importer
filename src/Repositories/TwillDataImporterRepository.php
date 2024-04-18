<?php

namespace A17\TwillDataImporter\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Repositories\Behaviors\HandleFiles;
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

    /**
     * @param TwillDataImporter $model
     * @param array $fields
     * @return void
     */
    public function afterSave($model, $fields): void
    {
        parent::afterSave($model, $fields);

        $model->enqueueImport();
    }
}
