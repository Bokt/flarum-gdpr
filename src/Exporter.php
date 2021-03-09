<?php

namespace Blomstra\Gdpr;

use Blomstra\Gdpr\Contracts\DataType;
use Blomstra\Gdpr\Models\Export;
use Carbon\Carbon;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Laminas\Diactoros\Response;
use ZipArchive;

class Exporter
{
    protected static $types = [
        Data\Assets::class, Data\Posts::class
    ];
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;
    protected $storagePath;
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->settings = app('flarum.settings');
        $this->storagePath = app('flarum')->storagePath();
        $this->filesystem = $filesystem;
    }

    static public function addType(string $type)
    {
        static::$types[] = $type;
    }

    public function export(User $user): Export
    {
        $file = tempnam($this->storagePath . DIRECTORY_SEPARATOR . 'tmp', 'gdpr-export-' . $user->username);
        $zip = new ZipArchive;
        $now = Carbon::now();

        $zip->open($file);
        $zip->setArchiveComment("Export of user data for {$user->username} ({$user->email}) on $now. From: {$this->settings->get('forum_title')}.");

        foreach (static::$types as $type) {
            /** @var DataType $segment */
            $segment = new $type($user);

            $segment->export($zip);
        }

        $zip->close();

        if ($this->filesystem->exists($user->id)) {
            $this->filesystem->delete($user->id);
        }

        $export = Export::exported($user, basename($file));

        $this->filesystem->writeStream($export->id, $handle = fopen($file, 'r'));

        fclose($handle);

        unlink($file);

        return $export;
    }

    public function getZip(Export $export)
    {
        return new Response(
            $this->filesystem->readStream($export->id),
            200,
            [
                'Content-Type' => 'application/zip',
                'Content-Length' => $this->filesystem->size($export->id),
                'Content-Disposition' => 'attachment; filename="gdpr-data-' . $export->user->username . '-' . $export->created_at->toIso8601String() . '.zip"'
            ]
        );
    }

    public function destroy(Export $export)
    {
        $this->filesystem->delete($export->id);

        $export->delete();
    }
}
