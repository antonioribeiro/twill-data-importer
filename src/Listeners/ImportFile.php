<?php

namespace A17\TwillDataImporter\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use A17\TwillDataImporter\Events\FileWasEnqueued;

class ImportFile implements ShouldQueue
{
    public function handle(FileWasEnqueued $event): void
    {
        $event->file->import();
    }
}
