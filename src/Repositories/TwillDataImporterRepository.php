<?php

namespace A17\TwillDataImporter\Repositories;

use A17\Twill\Repositories\ModuleRepository;
use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Repositories\Behaviors\HandleRevisions;
use A17\TwillDataImporter\Models\TwillDataImporter;
use A17\TwillDataImporter\Support\Facades\TwillDataImporter;

/**
 * @method \Illuminate\Database\Eloquent\Builder published()
 */
class TwillDataImporterRepository extends ModuleRepository
{
    use HandleRevisions;

    public function __construct(TwillDataImporter $model)
    {
        $this->model = $model;
    }

    public function theOnlyOne(): TwillDataImporter
    {
        $record = TwillDataImporter::query()
            ->orderBy('id')
            ->first();

        return $record ?? $this->generate();
    }

    private function generate(): TwillDataImporter
    {
        /** @var TwillDataImporter $model */
        $model = app(self::class)->create([
            'hsts' => config('twill-data-importer.headers.hsts.default')['value'],
            'csp_block' => config('twill-data-importer.headers.csp.default')['block'],
            'csp_report_only' => config('twill-data-importer.headers.csp.default')['report-only'],
            'expect_ct' => config('twill-data-importer.headers.expect-ct.default')['value'],
            'xss_protection_policy' => config('twill-data-importer.headers.xss-protection-policy.default')['value'],
            'x_frame_policy' => config('twill-data-importer.headers.x-frame-policy.default')['value'],
            'x_content_type_policy' => config('twill-data-importer.headers.x-content-type-policy.default')['value'],
            'referrer_policy' => config('twill-data-importer.headers.referrer-policy.default')['value'],
            'permissions_policy' => config('twill-data-importer.headers.permissions-policy.default')['value'],
            'unwanted_headers' => implode(',', config('twill-data-importer.unwanted-headers')),
        ]);

        return $model;
    }

    public function getFormFields(TwillModelContract $object): array
    {
        $fields = parent::getFormFields($object);

        $fields['headers'] = TwillDataImporter::getAvailableHeaders();

        return $fields;
    }
}
