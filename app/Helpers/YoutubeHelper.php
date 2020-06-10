<?php

namespace App\Helpers;

class YoutubeHelper
{
    /* todo: Use Youtube SDK and API, post MVP */
    /**
     * Get Youtube Title from url
     *
     * @param  string  $url
     * @return string
     */
    public static function getYoutubeTitle($url)
    {
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = FALSE;

        try {
            $doc->loadHTMLFile($url);

            $title_div = $doc->getElementById('eow-title');
            $title = $title_div->nodeValue;
        } catch (\Exception $e) {
            $title = $url;
        }

        return $title;
    }
    
    /**
     * Get Video Id from url
     *
     * @param  string  $url
     * @return string
     */
    public static function getVideoId($url)
    {
        if (substr_count($url, 'youtube.com/watch?v=')) {
            $parts = explode('/watch?v=', $url);
        } else {
            $parts = explode('/', $url);
        }

        return last($parts);
    }

    /**
     * Get Youtube Url
     *
     * @return string
     */
    public static function getYoutubeUrl()
    {
        return 'https://www.youtube.com/embed/';
    }

}