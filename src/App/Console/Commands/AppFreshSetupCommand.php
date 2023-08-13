<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppFreshSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'duralga:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup migrations, seed';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('migrate:fresh');
        $this->call('db:seed');
        $this->call('l5-swagger:generate');
        $this->call('clear-compiled');
        $this->call('optimize:clear');
    }
}
