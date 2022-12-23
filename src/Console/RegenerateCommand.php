<?php

namespace Elegant\Media\Console;

use Elegant\Media\Media;
use Elegant\Media\TemporaryFile;
use Elegant\Media\FileAdder;
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
{--manipulation=* : Regenerate conversions with specific manipulation}
{--only-missing : Regenerate only missing conversions}
{--force : Force the operation to run when in production}
EOF;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate media conversions';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!$this->confirmToProceed()) {
            return;
        }

        $media = $this->getMedia();

        $bar = $this->output->createProgressBar($media->count());

        $this->info('Regenerating media conversions...');

        $bar->start();

        foreach ($media as $m) {
            $this->generateMedia($m);
            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        $this->info('All done!');
    }

    protected function getMedia(): Collection
    {
        // return media without any manipulation (don't return conversions)
        $query = Media::where('manipulation', null);

        $ids = $this->option('id');
        if (!empty($ids))
            $query->whereIn('id', $ids);

        $modelTypes = $this->option('model-type');
        if (!empty($modelTypes))
            $query->whereIn('model_type', $modelTypes);

        $modelIds = $this->option('model-id');
        if (!empty($modelIds))
            $query->whereIn('model_id', $modelIds);

        $groups = $this->option('group');
        if (!empty($groups))
            $query->whereIn('group', $groups);

        return $query->get();
    }

    protected function generateMedia(Media $media): void
    {
        $group = $media->model->getMediaGroup($media->group);

        if (null === $group) {
            return;
        }

        $manipulations = $group->getManipulations();

        foreach ($manipulations as $manipulation) {
            if ($this->shouldPerformManipulation($manipulation, $media)) {
                $this->performManipulation($manipulation, $media);
            }
        }
    }

    protected function shouldPerformManipulation(string $name, Media $originalMedia): bool
    {
        $manipulations = $this->option('manipulation');

        if (!empty($manipulations) and !in_array($name, $manipulations))
            return false;

        if (!$originalMedia->hasConversion($name))
            return true;

        if ($originalMedia->getConversion($name)->file()->exists())
            return !$this->option('only-missing');

        return true;
    }

    protected function performManipulation(string $name, Media $originalMedia): void
    {
        // delete old conversion
        $originalMedia->deleteConversion($name);

        // add new conversion
        (new FileAdder($originalMedia->model, $originalMedia->file()))
                        ->toMediaConversion($name, $originalMedia);
    }
}
