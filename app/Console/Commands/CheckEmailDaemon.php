<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckEmailDaemon extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-email-daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        exec('ps -fe | grep "artisan queue:work"', $system);

        // control over available slots
        if ((count($system) - 1) < 1) {
            exec('php artisan queue:work --daemon > storage/logs/laravel.log &');
        }
    }
}
