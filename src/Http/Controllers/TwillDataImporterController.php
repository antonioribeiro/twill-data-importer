<?php

namespace A17\TwillDataImporter\Http\Controllers;

use Illuminate\Support\Str;
use A17\Twill\Services\Forms\Form;
use A17\Twill\Services\Forms\Fieldset;
use A17\Twill\Services\Forms\Fields\Files;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Listings\TableColumns;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Http\Controllers\Admin\ModuleController;

class TwillDataImporterController extends ModuleController
{
    use FormSubmitOptions;

    protected $moduleName = 'twillDataImporter';

    protected function setUpController(): void
    {
        $this->disablePermalink();
        $this->disablePublish();
    }

    protected function getViewPrefix(): string|null
    {
        return Str::kebab($this->moduleName) . '::admin';
    }

    private function namePrefix(): string|null
    {
        return config('twill.admin_route_name_prefix');
    }

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        $form->addFieldset(
            Fieldset::make()
                    ->title('Data')
                    ->fields([
                        Files::make()->name('data-files')->label('Files to import')->max(1),

                        Input::make()->name('error_message')->label('Last error message')->type('textarea')->rows(3)->readOnly(),
                    ]),
        );

        return $form;
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->push(
            Text::make()
                ->field('base_name')
                ->title('File name'),
        );

        $table->push(
            Text::make()
                ->field('mime_type')
                ->title('File type'),
        );

        $table->push(
            Text::make()
                ->field('status')
                ->title('Status'),
        );

        $table->push(
            Text::make()
                ->field('imported_at')
                ->title('Imported at'),
        );

        $table->push(
            Text::make()
                ->field('imported_records')
                ->title('Records'),
        );

        return $table;
    }
}
