<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LintCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply style fixes to the codebase';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Running Laravel Pint...');
        $pint = base_path('vendor/bin/pint');

        return (int) passthru($pint);
    }
}
