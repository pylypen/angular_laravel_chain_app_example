<?php

namespace App\Http\Traits;

use ZipArchive;

trait ArchiveTrait
{
    /**
     * Extract Zip Archive
     *
     * @param string $path
     * @param string $pathZip
     *
     * @return bool
     */
    private function extractZip(string $path, string $pathZip)
    {
        $zip = new ZipArchive;
        if ($zip->open($pathZip) === TRUE) {
            $zip->extractTo($path);
            $zip->close();
            return true;
        }
        
        return false;
    }

    /**
     * delete Extracted Archive
     *
     * @param string $dir
     *
     * @return bool
     */
    private static function delExtractedArchive(string $dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            if (is_dir("$dir/$file"))
                self::delExtractedArchive($dir . DIRECTORY_SEPARATOR . $file);
             else
                unlink($dir . DIRECTORY_SEPARATOR . $file);
        }

        return rmdir($dir);
    }
}