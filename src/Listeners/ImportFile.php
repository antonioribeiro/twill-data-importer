<?php

namespace A17\TwillDataImporter\Listeners;

use A17\TwillDataImporter\Events\FileWasEnqueued;

class ImportFile
{
    public function handle(FileWasEnqueued $event): void
    {
        $event->file->import();
    }
}
