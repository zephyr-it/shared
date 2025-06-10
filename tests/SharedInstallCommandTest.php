<?php

use Symfony\Component\Console\Command\Command;

it('rejects conflicting tenant options', function () {
    $this->artisan('shared:install', ['--tenant' => true, '--tenant-only' => true])
        ->expectsOutput('The --tenant and --tenant-only options cannot be used together.')
        ->assertExitCode(Command::INVALID);
});
