<?php
namespace Database\Seeds\Shared;

use Illuminate\Database\Seeder;
use App\Models\LessonsProgressStatus;

class LessonsProgressStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void // Database\Seeds\Shared\LessonsProgressStatusTableSeeder
     */
    public function run()
    {

        LessonsProgressStatus::truncate();

        $statuses = [
            ['name' => 'Just Assigned', 'percent' => 0],
            ['name' => 'Started', 'percent' => 25],
            ['name' => 'Half Way', 'percent' => 50],
            ['name' => 'Nearly Done', 'percent' => 75],
            ['name' => 'Completed', 'percent' => 100],
        ];

        foreach ($statuses as $statuse) {
            LessonsProgressStatus::create($statuse);
        }
    }
}
