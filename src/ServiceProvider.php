<?php

namespace A17\TwillDataImporter;

use Illuminate\Support\Str;
use A17\Twill\Helpers\Capsule;
use A17\Twill\Facades\TwillCapsules;
use Illuminate\Support\Facades\Event;
use A17\Twill\TwillPackageServiceProvider;
use A17\TwillDataImporter\Listeners\ImportFile;
use A17\TwillDataImporter\Events\FileWasEnqueued;
use A17\TwillDataImporter\Services\TwillDataImporter;

class ServiceProvider extends TwillPackageServiceProvider
{
    /** @var bool */
    protected $autoRegisterCapsules = false;

    protected Capsule $capsule;

    public function boot(): void
    {
        if ($this->registerConfig()) {
            $this->registerThisCapsule();

            $this->registerListeners();

            parent::boot();
        }
    }

    protected function registerThisCapsule(): void
    {
        $namespace = $this->getCapsuleNamespace();

        $this->capsule = TwillCapsules::registerPackageCapsule(
            Str::afterLast($namespace, '\\'),
            $namespace,
            $this->getPackageDirectory() . '/src',
            // null, // singular   ------------ Not available on Twill yet
            // true, // enabled   ------------ Not available on Twill yet
            // false, // automatic navigation   ------------ Not available on Twill yet
        );

        app()->singleton(TwillDataImporter::class, fn() => new TwillDataImporter());
    }

    public function registerConfig(): bool
    {
        $package = 'twill-data-importer';

        $path = __DIR__ . "/config/$package.php";

        $this->mergeConfigFrom($path, $package);

        $this->publishes([
            $path => config_path("$package.php"),
        ]);

        return !!config('twill-data-importer.enabled');
    }

    private function registerListeners(): void
    {
        Event::listen(
            FileWasEnqueued::class,
            [ImportFile::class, 'handle']
        );
    }
}
