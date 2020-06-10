<?php

namespace App\Console\Commands;

use App\Models\DevelopersAccounts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class RemindDevelopersAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev-acc:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command has been made to recover missing account secret.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Started secret key remind process .....');

        $key = $this->promptKey();

        $dev_acc = DevelopersAccounts::where(['acc_key' => $key])->first();

        $this->info('Dev User Found: ');
        $this->warn('Account Key: ' . $dev_acc->acc_key);
        $this->warn('Account Secret: ' . decrypt($dev_acc->acc_secret, false));

        return;
    }


    private function promptKey()
    {
        $data = [
            'key' => $this->ask('Dev account key?')
        ];

        $validity = Validator::make($data, ['key' => 'required|string|max:255|exists:developers_accounts,acc_key']);

        if ($validity->fails()) {
            $this->error($validity->errors()->first());
            return $this->promptKey();
        } else {
            return $data['key'];
        }
    }

}
