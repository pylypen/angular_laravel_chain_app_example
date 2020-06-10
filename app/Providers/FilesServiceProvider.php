<?php

namespace App\Providers;

use App\Models\Files;
use App\Models\Media;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;

class FilesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Files::updating(function ($fileNew) {
            $file = Files::find($fileNew->id);

            if ($file->src != $fileNew->src && !empty($file->src)) {
                $src = explode(env('AWS_S3_PATH', false), $file->src);
                if (count($src) > 1)
                    Storage::disk('s3')->delete($src[1]);
            }
            if ($file->thumbnail != $fileNew->thumbnail && !empty($file->thumbnail)) {
                $thumbnail = explode(env('AWS_S3_PATH', false), $file->thumbnail);
                if (count($thumbnail) > 1)
                    Storage::disk('s3')->delete($thumbnail[1]);
            }
        });

        Files::deleting(function ($fileNew) {
            $file = Files::find($fileNew->id);

            if (!empty($file->src)) {
                $src = explode(env('AWS_S3_PATH', false), $file->src);
                if (count($src) > 1)
                    Storage::disk('s3')->delete($src[1]);
            }
            if (!empty($file->thumbnail)) {
                $thumbnail = explode(env('AWS_S3_PATH', false), $file->thumbnail);
                if (count($thumbnail) > 1)
                    Storage::disk('s3')->delete($thumbnail[1]);
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}