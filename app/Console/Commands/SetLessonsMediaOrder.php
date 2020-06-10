<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lesson;
use App\Models\MediaTypes;
use App\Models\LessonContentOrder;

class SetLessonsMediaOrder extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:set-lessons-media-order';

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
        $media_types = MediaTypes::get();

        foreach (Lesson::get() as $lesson) {
            if (!LessonContentOrder::where('lesson_id', $lesson->id)->count()) {
                foreach ($media_types as $mt) {
                    LessonContentOrder::create([
                        'lesson_id' => $lesson->id,
                        'media_type_id' => $mt->id,
                        'order' => $mt->id
                    ]);
                }
            }
        }
    }
}
