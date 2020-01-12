<?php

namespace Elegant\Console;

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
media:clean
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
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return;
        }
    }
}
