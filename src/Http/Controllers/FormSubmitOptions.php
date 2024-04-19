<?php

namespace A17\TwillDataImporter\Http\Controllers;

use Illuminate\Database\Eloquent\Model;

trait FormSubmitOptions
{
    public function getSubmitOptions(Model $item): ?array
    {
        if (filled($item->imported_at)) {
            return [];
        }

        if ($this->moduleHas('revisions') && $this->enableDraftRevisions) {
            return $this->getSubmitOptionsForDraftRevisions($item);
        }

        if ($item->cmsRestoring ?? false) {
            return [
                'draft' => [
                    [
                        'name' => 'restore',
                        'text' => twillTrans('twill::lang.publisher.restore-draft'),
                    ],
                    [
                        'name' => 'restore-close',
                        'text' => twillTrans('twill::lang.publisher.restore-draft-close'),
                    ],
                    [
                        'name' => 'restore-new',
                        'text' => twillTrans('twill::lang.publisher.restore-draft-new'),
                    ],
                    [
                        'name' => 'cancel',
                        'text' => twillTrans('twill::lang.publisher.cancel'),
                    ],
                ],
                'live' => [
                    [
                        'name' => 'restore',
                        'text' => twillTrans('twill::lang.publisher.restore-live'),
                    ],
                    [
                        'name' => 'restore-close',
                        'text' => twillTrans('twill::lang.publisher.restore-live-close'),
                    ],
                    [
                        'name' => 'restore-new',
                        'text' => twillTrans('twill::lang.publisher.restore-live-new'),
                    ],
                    [
                        'name' => 'cancel',
                        'text' => twillTrans('twill::lang.publisher.cancel'),
                    ],
                ],
                'update' => [
                    [
                        'name' => 'restore',
                        'text' => twillTrans('twill::lang.publisher.restore-live'),
                    ],
                    [
                        'name' => 'restore-close',
                        'text' => twillTrans('twill::lang.publisher.restore-live-close'),
                    ],
                    [
                        'name' => 'restore-new',
                        'text' => twillTrans('twill::lang.publisher.restore-live-new'),
                    ],
                    [
                        'name' => 'cancel',
                        'text' => twillTrans('twill::lang.publisher.cancel'),
                    ],
                ],
            ];
        }

        return [
            'update' => [
                [
                    'name' => 'update',
                    'text' => 'Update and import',
                ],
            ],
        ];
    }

    protected function getSubmitOptionsForDraftRevisions(Model $item): ?array
    {
        if ($item->cmsRestoring ?? false) {
            return [
                'draft' => [
                    [
                        'name' => 'draft-revision',
                        'text' => twillTrans('twill::lang.publisher.draft-revision'),
                    ],
                    [
                        'name' => 'draft-revision-close',
                        'text' => twillTrans('twill::lang.publisher.draft-revision-close'),
                    ],
                    [
                        'name' => 'draft-revision-new',
                        'text' => twillTrans('twill::lang.publisher.draft-revision-new'),
                    ],
                    [
                        'name' => 'cancel',
                        'text' => twillTrans('twill::lang.publisher.cancel'),
                    ],
                ],
                'live' => [
                    [
                        'name' => 'restore',
                        'text' => twillTrans('twill::lang.publisher.publish'),
                    ],
                    [
                        'name' => 'restore-close',
                        'text' => twillTrans('twill::lang.publisher.publish-close'),
                    ],
                    [
                        'name' => 'restore-new',
                        'text' => twillTrans('twill::lang.publisher.publish-new'),
                    ],
                    [
                        'name' => 'cancel',
                        'text' => twillTrans('twill::lang.publisher.cancel'),
                    ],
                ],
                'update' => [
                    [
                        'name' => 'restore',
                        'text' => twillTrans('twill::lang.publisher.publish'),
                    ],
                    [
                        'name' => 'restore-close',
                        'text' => twillTrans('twill::lang.publisher.publish-close'),
                    ],
                    [
                        'name' => 'restore-new',
                        'text' => twillTrans('twill::lang.publisher.publish-new'),
                    ],
                    [
                        'name' => 'cancel',
                        'text' => twillTrans('twill::lang.publisher.cancel'),
                    ],
                ],
            ];
        }

        return [
            'draft' => [
                [
                    'name' => 'save',
                    'text' => twillTrans('twill::lang.publisher.save'),
                ],
                [
                    'name' => 'save-close',
                    'text' => twillTrans('twill::lang.publisher.save-close'),
                ],
                [
                    'name' => 'save-new',
                    'text' => twillTrans('twill::lang.publisher.save-new'),
                ],
                [
                    'name' => 'cancel',
                    'text' => twillTrans('twill::lang.publisher.cancel'),
                ],
            ],
            'live' => [
                [
                    'name' => 'publish',
                    'text' => twillTrans('twill::lang.publisher.publish'),
                ],
                [
                    'name' => 'publish-close',
                    'text' => twillTrans('twill::lang.publisher.publish-close'),
                ],
                [
                    'name' => 'publish-new',
                    'text' => twillTrans('twill::lang.publisher.publish-new'),
                ],
                [
                    'name' => 'cancel',
                    'text' => twillTrans('twill::lang.publisher.cancel'),
                ],
            ],
            'update' => [
                [
                    'name' => 'draft-revision',
                    'text' => twillTrans('twill::lang.publisher.draft-revision'),
                ],
                [
                    'name' => 'publish',
                    'text' => twillTrans('twill::lang.publisher.publish'),
                ],
                [
                    'name' => 'publish-close',
                    'text' => twillTrans('twill::lang.publisher.publish-close'),
                ],
                [
                    'name' => 'publish-new',
                    'text' => twillTrans('twill::lang.publisher.publish-new'),
                ],
                [
                    'name' => 'cancel',
                    'text' => twillTrans('twill::lang.publisher.cancel'),
                ],
            ],
        ];
    }
}
