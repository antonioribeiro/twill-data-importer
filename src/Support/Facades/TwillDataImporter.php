<?php

namespace A17\TwillDataImporter\Support\Facades;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Facade;
use A17\TwillDataImporter\Services\TwillDataImporter as TwillDataImporterService;

/**
 * @method static Response middleware(Response $response, string $type = '*')
 */
class TwillDataImporter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TwillDataImporterService::class;
    }
}
