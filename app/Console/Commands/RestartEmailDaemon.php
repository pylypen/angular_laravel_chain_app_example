<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RestartEmailDaemon extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:restart-email-daemon';

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

        
        foreach ($system as $s) {
            $id = [];
            $data = explode(' ', $s);
            
            foreach ($data as $d) {
                if (!empty($d)) {
                    @$id[] = $d;
                }
            }

            $this->info('ID: ' . $id[1]);
            
            if (!empty($id[1])) {
                if ((int)$id[1] && (int)$id[1] > 1000) {
                    exec('kill -9 ' . (int)$id[1]);
                    $this->info('Process ' . (int)$id[1] . ' was deleted');
                }
            } else {
                $this->error('ID is empty');
            }
        }

        exec('php artisan queue:work --daemon > storage/logs/laravel.log &');
    }
}
