<?php

namespace App\Helpers;

use Image;

class ImageHelper
{
    public static function createThumbnailFileImage($src, $width, $height)
    {
        $img = Image::make(file_get_contents($src));

        $iHeight = $img->height();
        $iWidth = $img->width();

        if (!$height) {
            if ($iWidth < $width) {
                return false;
            }

            $img->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        } else {
            if ($iWidth > $width || $iHeight > $height) {
                $img->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else {
                return false;
            }
        }

        $path = '/tmp/' . md5($src . time());

        $img->save($path);
        
        return $path;
    }
}