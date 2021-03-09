<?php

namespace Blomstra\Gdpr\Providers;

use Blomstra\Gdpr\Exporter;
use Flarum\Foundation\AbstractServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Filesystem\Filesystem;

class GdprProvider extends AbstractServiceProvider
{
    public function boot()
    {
        /** @var Repository $config */
        $config = $this->app->get('config');

        $disks = $config->get('filesystems.disks', []);
        $disks['gdpr-export'] = [
            'driver' => 'local',
            'root'   => $this->app['flarum']->storagePath().'/gdpr-exports'
        ];
        $config->set('filesystems.disks', $disks);

        $this->app
            ->when(Exporter::class)
            ->needs(Filesystem::class)
            ->give(function () {
                return app('filesystem')->disk('gdpr-export');
            });
    }
}
