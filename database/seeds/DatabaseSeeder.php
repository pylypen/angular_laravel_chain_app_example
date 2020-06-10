<?php


use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->runSeedSequence();
    }

    private function runSeedSequence()
    {
        $environment = env('APP_ENV');
        $this->seedShared();
        switch ($environment) {
            case 'development':
                $this->seedDevelopment();
                break;
            default:
            case 'production':
                $this->seedProduction();
                break;
        }
    }

    //seed with tables that should have no diff on diff env
    private function seedShared()
    {
        $this->call(Database\Seeds\Shared\TruncateDB::class);
        $this->call(Database\Seeds\Shared\SecretQuestionsTableSeeder::class);
        $this->call(Database\Seeds\Shared\SystemUsers::class);
        $this->call(Database\Seeds\Shared\MarketplaceStatusesSeeder::class);
        $this->call(Database\Seeds\Shared\LessonsProgressStatusTableSeeder::class);
        $this->call(Database\Seeds\Shared\MediaTypesSeeder::class);
        $this->call(Database\Seeds\Shared\MediaExtensionsSeeder::class);
    }

    //seed with fakers
    private function seedDevelopment()
    {
        $this->call(Database\Seeds\Development\TenUserAndOrgStatic::class);
        $this->call(Database\Seeds\Development\ThirtyUserAndOrgAdmin::class);
        $this->call(Database\Seeds\Development\OrgSiteSeed::class);
        $this->call(Database\Seeds\Development\User2Seed::class);
        $this->call(Database\Seeds\Development\TeamSeed::class);
        $this->call(Database\Seeds\Development\CoursesSeed::class);
        $this->call(Database\Seeds\Development\LessonsCommentsSeed::class);

    }

    //seed with clean prod data
    private function seedProduction()
    {
	    $this->call( Database\Seeds\Shared\SecretQuestionsTableSeeder::class );
    }
}
