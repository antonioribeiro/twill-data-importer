<?php

namespace A17\TwillDataImporter\Http\Requests;

use A17\Twill\Http\Requests\Admin\Request;

class TwillDataImporterRequest extends Request
{
    public function rulesForCreate(): array
    {
        return [];
    }

    public function rulesForUpdate(): array
    {
        return [];
    }
}
