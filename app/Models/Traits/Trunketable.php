<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait Trunketable
{

    public static function truncate()
    {
        if (new self() instanceof Model) {

            DB::statement('set foreign_key_checks = 0;');

            self::query()->truncate();

            DB::statement('set foreign_key_checks = 1;');

        }
    }

}