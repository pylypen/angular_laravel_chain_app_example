<?php

namespace App\Jobs;

use App\Helpers\ImageHelper;
use App\Models\Files;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class ImageThumbnailProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $file;
    protected $path;
    protected $width;
    protected $height = false;
    /**
     * Create a new job instance.
     *
     * @param Files $file
     * @param string $type
     * @param string $path
     */
    public function __construct(Files $file, $path, $type)
    {
        $this->file = $file;
        $this->path = $path;

        switch ($type) {
            case 'avatar':
            case 'logo':
                $this->width = env('IMG_AVATAR_LOGO_WIDTH', false);
                $this->height = env('IMG_AVATAR_LOGO_HEIGHT', false);
                break;
            case 'cover':
                $this->width = env('IMG_COVER_WIDTH', false);
                break;
            case 'background':
                $this->width = env('IMG_BACKGROUND_WIDTH', false);
                break;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pathImg = ImageHelper::createThumbnailFileImage($this->file->src, $this->width, $this->height);
        
        if ($pathImg) {
            $src = explode('/', $this->file->src);
            $src = $src[count($src) - 1];
            $src = explode('.', $src);
            $src = '.' . $src[count($src) - 1];

            Storage::disk('s3')->putFileAs(
                $this->path,
                new File($pathImg),
                md5($pathImg) . $src,
                'public'
            );

            $file = Files::find($this->file->id);
            $file->thumbnail = env('AWS_S3_PATH', false) . $this->path . '/' . md5($pathImg) . $src;
            $file->save();
        }
    }
}
