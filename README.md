# Data Importer Twill Capsule

This Twill Capsule is intended to enable developers to create data importers (CSV, JSON...) to their Twill application. 

## Screenshots

### CMS configuration
![screenshot01](docs/screenshot01.png)

![screenshot02](docs/screenshot02.png)

## Supported Headers


## Installing

## Supported Versions
Composer will manage this automatically for you, but these are the supported versions between Twill and this package.

| Twill Version | Data Importer Capsule |
|---------------|-----------------------|
| 3.x           | 1.x                   |

### Require the Composer package:

``` bash
composer require area17/twill-data-importer
```

### Publish the configuration

Publishing the config file is mandatory as you will need it to configure your importers:

``` bash
php artisan vendor:publish --provider="A17\TwillDataImporter\ServiceProvider"
```
### Install dependencies
This package depends on these other packages, in case you need

| File format | Package     |
|-------------|-------------|
| CSV         | league/csv  |

### Usage 

Create an importer class that implements one of the base importers and the `importRow()` method:

```php
<?php

namespace App\Services\DataImporter;

use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artists\Repositories\ArtistRepository;
use A17\TwillDataImporter\Services\Importers\CsvImporter as CsvImporterBase;

class CsvImporter extends CsvImporterBase
{
    protected ArtistRepository $artistRepository;

    public function __construct()
    {
        $this->artistRepository = app(ArtistRepository::class);
    }

    /**
     * File columns or attributes (example: Headshot Credit) are all snake cased (headshot_credit) 
     */
    protected array $fieldRelations = [
        'name' => 'full_name',
        'prounouns' => 'prounouns',
        'city_state' => 'city_state',
        'bio' => 'bio',
        'headshot' => 'photo_area_1',
        'headshot_credit' => 'photo_description',
        'headshot_image_i_d' => 'artist_photo_description',
        'website' => 'web_site_url',
        'social_media' => 'additional_links',
        'practice_descriptor' => 'donor',
        'year' => 'year',
        'initiative' => 'book_covers',
    ];

    public function importRow($row): bool
    {
        $artist = new Artist();

        $data = [];

        foreach ($row as $key => $value) {
            $field = $this->getField($key);

            if (is_null($field)) {
                $this->error("CsvImporter class: field '$key' not found");

                return false;
            }

            $data[$field] = $this->translate($value, $artist, $field);
        }

        $this->artistRepository->firstOrCreate(['full_name' => $data['full_name']], $data);

        return true;
    }

    private function getField(string $key): string|null
    {
        return $this->fieldRelations[$key] ?? null;
    }

    private function translate($value, Artist $artist, string $field): string|array|null
    {
        if (collect($artist->translatedAttributes)->contains($field)) {
            return ['en' => $value];
        }

        return $value;
    }
}
```

Then set your importers on the `config/twill-data-importer.php` config file:

```php
<?php

return [
    'importers' => [
        'default' => [
            'caption' => 'Select an importer',
        ],

        'artists' => [
            'caption' => 'Artists importer',

            'mime-types' => [
                'text/csv' => \App\Services\DataImporter\ArtistsCsvImporter::class,
                'text/plain' => \App\Services\DataImporter\ArtistsCsvImporter::class,
            ],
        ],

        'artworks' => [
            'caption' => 'Artworks Importer',

            'mime-types' => [
                'application/json' => \App\Services\DataImporter\ArtworksJsonImporter::class,
            ],
        ],
    ],
];
```

If you just need one single type of importer you can just use the default configuration:

```php
<?php

return [
    'importers' => [
        'default' => [
            'mime-types' => [
                'application/json' => \App\Services\DataImporter\ArtworksJsonImporter::class,
            ],
        ],
    ],
];
```

There will be no select input to chose between different types of importers in the edit page.

### Menu

If you are clearing the Twill menu in order to create a new one yourself, you will need to add it manually:

```php
TwillNavigation::clear();

...

TwillNavigation::addLink(
    NavigationLink::make()
        ->forModule('TwillDataImporter')
        ->title('Data importer')
);
```

## Contribute

Please contribute to this project by submitting pull requests.
