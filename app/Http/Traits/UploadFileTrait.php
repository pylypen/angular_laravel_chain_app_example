<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\File;
use Image;
use Illuminate\Support\Facades\Storage;

trait UploadFileTrait
{
    /**
     * Upload File
     *
     * @param object $image
     * @param string $type
     *
     * @return bool
     */
    private function uploadFile($image, $type = null)
    {
        if (!empty($image)) {
            switch ($type) {
                case 'pdf':
                    $path = 'pdf/';
                    break;
                default:
                    $path = 'images/';
                    break;
            }
            
            $file_name = time() . "_" . $image->getClientOriginalName();
            $image->move(public_path($path), $file_name);
            return $file_name;
        }
        return false;
    }


    /**
     * Upload File Base 64 format
     *
     * @param object $image
     * @param string $path
     *
     * @return bool
     */
    private function uploadFileBase64($image, $path)
    {
        if (!empty($image)) {

            $file_name = md5(uniqid()) . '.png';
            
            Storage::disk('s3')->put(
                $path . $file_name,
                Image::make(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image)))->encode(),
                'public'
            );

            return $file_name;
        }

        return false;
    }

    /**
     * Remove File
     *
     * @param string $image_name
     * @param string $type
     *
     * @return bool
     */
    private function removeFile($image_name, $type = null)
    {
        switch ($type) {
            case 'pdf':
                $path = 'pdf/';
                break;
            default:
                $path = 'images/';
                break;
        }
        
        File::delete(public_path($path . $image_name));
    }
}