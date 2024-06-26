<?php

namespace A17\TwillDataImporter\Http\Controllers;

use Illuminate\Support\Str;
use A17\Twill\Services\Forms\Form;
use Illuminate\Support\Collection;
use A17\Twill\Services\Forms\Columns;
use A17\Twill\Services\Forms\Fieldset;
use A17\Twill\Services\Forms\Fields\Files;
use A17\Twill\Services\Forms\Fields\Input;
use A17\Twill\Services\Forms\Fields\Select;
use A17\Twill\Services\Listings\Columns\Text;
use A17\Twill\Services\Listings\TableColumns;
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

    public function getForm(TwillModelContract $model): Form
    {
        $form = parent::getForm($model);

        // Fieldset configuration

        $fields = [];

        if ($this->multipleImportersAvailable()) {
            $fields[] = $this->typeSelect();
        }

        $fields[] = Files::make()->name('data-files')->label('Files to import')->max(1);

        $form->addFieldset(Fieldset::make()->title('Configuration')->fields($fields));

        // Fieldset report

        $form->addFieldset(
            Fieldset::make()
                ->title('Report (read only)')
                ->fields([
                    Columns::make()
                        ->left([Input::make()->name('base_name')->label('File name')->readOnly()])
                        ->right([Input::make()->name('mime_type')->label('File type')->readOnly()]),

                    Columns::make()
                        ->left([Input::make()->name('imported_at')->label('Imported at')->readOnly()])
                        ->middle([Input::make()->name('imported_records')->label('Imported records')->readOnly()])
                        ->right([Input::make()->name('total_records')->label('Total records')->readOnly()]),

                    Input::make()
                        ->name('headers')
                        ->label('Headers found on file')
                        ->type('textarea')
                        ->rows(3)
                        ->note('Headers will be transformed to snake case')
                        ->readOnly(),

                    Input::make()->name('status_for_humans')->label('Current status')->readOnly(),

                    Input::make()
                        ->name('error_message')
                        ->label('Last error message')
                        ->type('textarea')
                        ->rows(3)
                        ->readOnly()
                        ->connectedTo('status', 'error'),
                ]),
        );

        return $form;
    }

    protected function additionalIndexTableColumns(): TableColumns
    {
        $table = parent::additionalIndexTableColumns();

        $table->push(Text::make()->field('base_name')->title('File name'));

        $table->push(Text::make()->field('mime_type')->title('File type'));

        $table->push(Text::make()->field('status')->title('Status'));

        $table->push(Text::make()->field('imported_at')->title('Imported at'));

        $table->push(Text::make()->field('imported_records')->title('Records'));

        return $table;
    }

    public function typeSelect(): Select
    {
        $importers = new Collection(config('twill-data-importer.importers'));

        $importers = $importers
            ->mapWithKeys(function ($importer, $key) {
                return [$key => ['value' => $key, 'label' => $importer['caption']]];
            })
            ->toArray();

        return Select::make()->name('data_type')->label('Data type')->options($importers);
    }

    protected function multipleImportersAvailable(): bool
    {
        return count(config('twill-data-importer.importers')) > 1;
    }
}
