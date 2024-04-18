<?php

namespace A17\TwillDataImporter\Services\Headers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RemoveUnwanted extends Header
{
    public function remove(Response|RedirectResponse|JsonResponse|BinaryFileResponse $response): void
    {
    }
}
