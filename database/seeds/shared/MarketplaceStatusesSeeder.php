<?php

namespace Database\Seeds\Shared;

use App\Models\MarketplaceStatuses;
use Illuminate\Database\Seeder;

class MarketplaceStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            'pending',
            'accepted',
            'declined'
        ];

        foreach ($statuses as $status) {
            MarketplaceStatuses::create(['status' => $status]);
        }
    }
}
