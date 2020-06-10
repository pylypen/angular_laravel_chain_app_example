<?php

namespace App\Console\Commands;

use App\Models\DevelopersAccounts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CreateDevelopersAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:developer-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command allows to create new CL Developer account.';

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
        $this->info('Entering Dev Acc generation sequence....');

        $dev_acc = new DevelopersAccounts();

        $dev_acc->email = $this->promptEmail();
        $dev_acc->issued_to = $this->promptName();

        $dev_acc->save();

        $dev_acc->acc_key = md5($dev_acc->id . $dev_acc->email . env('APP_KEY'));
        $secret_key = Str::random(32);
        $dev_acc->acc_secret = Crypt::encryptString($secret_key);

        $dev_acc->save();

        $this->info('Dev User generated: ');
        $this->warn('Account Key: ' . $dev_acc->acc_key);
        $this->warn('Account Secret: ' . $secret_key);

        return;
    }

    private function promptEmail()
    {
        $data = [
            'email' => $this->ask('Dev account contact email?')
        ];

        $validity = Validator::make($data, ['email' => 'required|string|email|max:255|unique:developers_accounts']);

        if ($validity->fails()) {
            $this->error($validity->errors()->first());
            return $this->promptEmail();
        } else {
            return $data['email'];
        }
    }

    private function promptName()
    {
        $data = [
            'issued_to' => $this->ask('Whom this dev account will be given to?')
        ];

        $validity = Validator::make($data, ['issued_to' => 'required|string|max:255']);

        if ($validity->fails()) {
            $this->error($validity->errors()->first());
            return $this->promptName();
        } else {
            return $data['issued_to'];
        }
    }


}
