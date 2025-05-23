<?php

namespace ZephyrIt\Shared\Commands;

use Illuminate\Console\Command;

class SharedCommand extends Command
{
    public $signature = 'shared';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
