<?php

namespace Elegant\Media\Console;

use Elegant\Media\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class CleanCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = <<<EOF
media:clean {disk?}
{--dry-run : List files that will be removed without removing them}
{--force : Force the operation to run when in production}
EOF;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean deprecated conversions and files without related model';

    /**
     * Disk name to use for storage.
     *
     * @var string
     */
    protected $diskName;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $this->diskName = $this->argument('disk') ?? config('media.disk');

        $this->info("Cleaning {$this->diskName} disk...");

        $this->deleteOrphanMedia();

        $this->deleteDeprecatedConversions();

        $this->deleteOrphanFiles();

        $this->info('All done!');
    }

    protected function deleteOrphanMedia(): void
    {
        $media = Media::where('disk', $this->diskName)->get();

        foreach ($media as $m) {
            if (!$m->model()->exists()) {
                $this->deleteMedia($m);
            }
        }
    }

    protected function deleteDeprecatedConversions(): void
    {
        $media = Media::where('disk', $this->diskName)->where('manipulation', '!=', null)->get();

        foreach ($media as $conv) {
            $group = $conv->model->model->getMediaGroup($conv->model->group);
            if (null === $group or !in_array($conv->manipulation, $group->getManipulations())) {
                $this->deleteMedia($conv);
            }
        }
    }

    protected function deleteOrphanFiles(): void
    {
        $disk = Storage::disk($this->diskName);

        $directories = $disk->directories();

        foreach ($directories as $directory) {
            $this->checkDirectory($disk, $directory);
        }
    }

    protected function checkDirectory($disk, string $dir): void
    {
        $files = $disk->files($dir);

        foreach ($files as $file) {
            $this->checkFile($disk, $file);
        }

        $directories = $disk->directories($dir);

        foreach ($directories as $directory) {
            $this->checkDirectory($disk, $directory);
        }

        $all = $disk->allFiles($dir);

        // if directory is empty so it is an orphan directory
        if (empty($all)) {
            $this->deleteDirectory($disk, $dir);
        }
    }

    protected function checkFile($disk, string $file): void
    {
        // do we have any active media in database for this file?
        $exists = Media::path($file)->exists();

        if (!$exists) {
            $this->deleteFile($disk, $file);
        }
    }

    protected function deleteMedia(Media $media): void
    {
        $this->info("Media #{$media->getKey()} " . ($this->option('dry-run') ? 'found' : 'removed'));

        if (!$this->option('dry-run')) {
            $media->delete();
        }
    }

    protected function deleteFile($disk, string $file): void
    {
        $this->info("Orphan file {$file} " . ($this->option('dry-run') ? 'found' : 'removed'));

        if (!$this->option('dry-run')) {
            $disk->delete($file);
        }
    }

    protected function deleteDirectory($disk, string $directory): void
    {
        $this->info("Orphan directory {$directory} " . ($this->option('dry-run') ? 'found' : 'removed'));

        if (!$this->option('dry-run')) {
            $disk->deleteDirectory($directory);
        }
    }
}
