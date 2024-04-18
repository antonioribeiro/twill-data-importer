<?php

namespace A17\TwillDataImporter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use A17\TwillDataImporter\Support\Facades\TwillDataImporter;

abstract class Middleware
{
    protected string $type = '*';

    protected function handleRequest(
        Request $request,
        Closure $next,
    ): Response|RedirectResponse|JsonResponse|BinaryFileResponse {
        $response = $next($request);

        return TwillDataImporter::middleware($response, $this->type);
    }

    protected function middleware(
        Request $request,
        Closure $next,
        string $type = '*',
    ): Response|RedirectResponse|JsonResponse|BinaryFileResponse {
        $this->setType($type);

        return $this->handleRequest($request, $next);
    }

    protected function setType(string $type): void
    {
        $this->type = $type;
    }
}
