<?php

namespace A17\TwillDataImporter\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Http\Controllers\Admin\ModuleController;
use A17\TwillDataImporter\Models\TwillDataImporter;
use A17\TwillDataImporter\Repositories\TwillDataImporterRepository;

class TwillDataImporterController extends ModuleController
{
    protected $moduleName = 'twillDataImporter';

    protected $titleColumnKey = 'site_key';

    protected $indexOptions = ['edit' => false];

    public function redirectToEdit(TwillDataImporterRepository $repository): RedirectResponse
    {
        return redirect()->route($this->namePrefix() . 'twillDataImporter.show', [
            'twillDataImporter' => $repository->theOnlyOne()->id,
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function index(?int $parentModuleId = null): mixed
    {
        return redirect()->route($this->namePrefix() . 'twillDataImporter.redirectToEdit');
    }

    public function edit(TwillModelContract|int $id): mixed
    {
        $repository = new TwillDataImporterRepository(new TwillDataImporter());

        return parent::edit($repository->theOnlyOne()->id);
    }

    protected function formData($request): array
    {
        return [
            'editableTitle' => false,
            'customTitle' => ' ',
        ];
    }

    protected function getViewPrefix(): string|null
    {
        return Str::kebab($this->moduleName) . '::admin';
    }

    private function namePrefix(): string|null
    {
        return config('twill.admin_route_name_prefix');
    }
}
