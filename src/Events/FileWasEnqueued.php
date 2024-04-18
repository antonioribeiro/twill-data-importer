<?php

namespace A17\TwillDataImporter\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use A17\TwillDataImporter\Models\TwillDataImporter;

class FileWasEnqueued
{
    use Dispatchable;
    use SerializesModels;
    use InteractsWithSockets;

    /**
     * @var \A17\TwillDataImporter\Models\TwillDataImporter
     */
    public TwillDataImporter $file;

    public function __construct(TwillDataImporter $file)
    {
        $this->file = $file;
    }
}
