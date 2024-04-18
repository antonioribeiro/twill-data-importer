<?php

namespace A17\TwillDataImporter\Services\Headers;

use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use A17\TwillDataImporter\Models\TwillDataImporter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use A17\TwillDataImporter\Repositories\TwillDataImporterRepository;

class Header
{
    protected TwillDataImporter $dataImporter;

    public function __construct()
    {
        $this->dataImporter = $this->getModel();
    }

    public function setHeaders(Response|RedirectResponse|JsonResponse|BinaryFileResponse $response, array $header): void
    {
        if (!$this->enabled($header)) {
            return;
        }

        $responseHeader = $this->compileHeader($header);

        if (filled($responseHeader)) {
            $response->headers->set($header['header'], $responseHeader);
        }
    }

    protected function compileHeader(array $header): mixed
    {
        return $this->dataImporter->{$this->snake($header['type'])};
    }

    public function getModel(): TwillDataImporter
    {
        return app(TwillDataImporterRepository::class)->theOnlyOne();
    }

    protected function enabled(array $header): bool
    {
        return $this->dataImporter->published &&
            $this->dataImporter->{$this->snake($header['type']) . '_enabled'};
    }

    public function snake(string $string): string
    {
        return Str::snake(Str::camel($string));
    }
}
