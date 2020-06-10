<?php

namespace Database\Seeds\Shared;

use App\Models\MediaTypes;
use Illuminate\Database\Seeder;

class MediaTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * // Database\Seeds\Shared\MediaTypesSeeder
     *
     * @return void
     */
    public function run()
    {
        MediaTypes::truncate();

        $statuses = [
            'Video',
            'Audio',
            'Document',
            'Embed',
            'Articulate'
        ];

        foreach ($statuses as $status) {
            MediaTypes::create(['name' => $status]);
        }
    }
}
