<?php

namespace A17\TwillDataImporter\Services\Headers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use A17\TwillDataImporter\Services\Helpers;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CSP extends Header
{
    public function setHeaders(Response|RedirectResponse|JsonResponse|BinaryFileResponse $response, array $header): void
    {
        if (!$this->enabled($header)) {
            return;
        }

    }

    public function addNounce(string $header): string
    {
        if (!$this->dataImporter->csp_generate_nounce) {
            return $header;
        }

        // Remove nounce
        $pattern = "/ 'nonce-.*?'/";
        $replacement = '';
        $header = preg_replace($pattern, $replacement, $header) ?? '';

        // Add nounce
        $pattern = '/(script-src \'self\'\ )/';
        $replacement = "$1'nonce-" . Helpers::nounce() . "' ";
        $header = preg_replace($pattern, $replacement, $header) ?? '';

        return $header;
    }
}
