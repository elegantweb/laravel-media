<?php

namespace Elegant\Console;

use Elegant\Media\Media;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class RegenerateCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = <<<EOF
media:regenerate
{--id=* : Regenerate specific media}
{--model-type=* : Regenerate media of specific model}
{--model-id=* : Regenerate media of specific model}
{--group=* : Regenerate specific groups}
{--conversion=* : Regenerate specific conversions}
{--only-missing : Regenerate only missing conversions}
{--force : Force the operation to run when in production}
EOF;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate the derived images of media';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->confirmToProceed())
            return;

        $media = $this->getMedia();

        $progressBar = $this->output->createProgressBar($media->count());

        foreach ($media as $m) {
            if ($this->shouldGenerateMedia($m)) {
                $this->generateMedia($m);
            }
        }
    }

    protected function getMedia(): Collection
    {
        $ids = $this->option('id');
        if (!empty($ids))
            return Media::whereIn('id', $ids)->get();

        $modelTypes = $this->option('model-type');
        if (!empty($modelTypes))
            return Media::whereIn('model_type', $modelTypes)->get();

        $modelIds = $this->option('model-id');
        if (!empty($modelIds)
            return Media::whereIn('model_id', $modelIds)->get();
    }

    protected function shouldGenerateMedia(Media $media): bool
    {
        $groups = $this->option('group');

        if (!empty($groups) and !in_array($m->group, $groups))
            return false;

        return true;
    }

    protected function generateMedia(Media $media)
    {
        $model = $media->model;

        $group = $model->getMediaGroup($media->group);

        $conversions = $group->getConversions();

        foreach ($conversions as $conversion) {
            if ($this->shouldPerformConversion($conversion)) {
                $this->performConversion($conversion, $media);
            }
        }
    }

    protected function shouldPerformConversion(string $name, Media $originalMedia): bool
    {
        $conversions = $this->option('conversion');

        if (!empty($conversions) and !in_array($name, $conversions))
            return false;

        if (!$originalMedia->hasConversion($name))
            return true;

        if ($originalMedia->getConversion($name)->fileExists())
            return !$this->option('only-missing');

        return true;
    }

    protected function performConversion(Media $originalMedia, string $name)
    {
        $tmpfile = new TemporaryFile();

        // maybe the original media file is remote, so we create a local temp file and work on it
        stream_copy_to_stream($originalMedia->stream(), $tmpfile->openFile('w'));

        (new FileAdder($originalMedia->model, $tmpfile))->toMediaConversion($name, $originalMedia);
    }
}
