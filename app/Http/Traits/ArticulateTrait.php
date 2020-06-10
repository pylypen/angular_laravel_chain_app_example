<?php

namespace App\Http\Traits;

trait ArticulateTrait
{

    /**
     * Array for validate Articulate Dir
     *
     * @var array
     */
    private $articulateValidDir = [
        'files' => [
            'analytics-frame.html',
            'meta.xml',
            'story.html',
            'story_html5.html'
        ],
        'dirs' => [
            'html5',
           // 'mobile',
            'story_content'
        ]
    ];

    /**
     * Default Articulate Index File
     *
     * @var string
     */
    private $articulateIndexFile = 'story_html5.html';

    /**
     * Default Articulate XML File
     *
     * @var string
     */
    private $articulateXmlFile = 'meta.xml';

    /**
     * List Articulate By Dir
     *
     * @param string $path
     *
     * @return array|bool
     */
    private function listArticulateByDir(string $path)
    {
        $paths = $this->filesArticulateAtDir($path);

        if ($this->validateArticulateFiles($paths) && $this->validateArticulateDirs($paths)) {
            $articulateName = $this->validateArticulateXML($paths);
            $separator = $this->getSeparatorForArticulatePath($paths);

            if ($articulateName && $separator && $paths) {
                return [
                    'list' => $paths,
                    'separator' => $separator,
                    'name' => $articulateName
                ];
            }
        }

        return false;
    }

    /**
     * Validate Articulate Dirs
     *
     * @param array $paths
     *
     * @return bool
     */
    private function validateArticulateDirs(array $paths)
    {
        foreach ($this->articulateValidDir['dirs'] as $dir) {
            $check = false;
            foreach ($paths as $path) {
                if (substr_count($path, DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR))
                    $check = true;
            }

            if (!$check)
                return false;
        }

        return true;
    }

    /**
     * Validate Articulate Files
     *
     * @param array $paths
     *
     * @return bool
     */
    private function validateArticulateFiles(array $paths)
    {
        foreach ($this->articulateValidDir['files'] as $file) {
            $check = false;
            foreach ($paths as $path) {
                if (substr_count($path, $file))
                    $check = true;
            }

            if (!$check)
                return false;
        }

        return true;
    }

    /**
     * Validate Articulate XML
     *
     * @param array $paths
     *
     * @return bool|string
     */
    private function validateArticulateXML(array $paths)
    {
        $metaPath = $this->getArticulateMetaPath($paths);
        $xml = simplexml_load_file($metaPath, null, LIBXML_NOCDATA);
        $xml = json_encode($xml);
        $xml = json_decode($xml, true);

        if (!empty($xml['project']['@attributes']['title'])
            && !empty($xml['project']['application']['@attributes']['name'])
            && $xml['project']['application']['@attributes']['name'] = 'Articulate Storyline') {

            return $xml['project']['@attributes']['title'];
        }

        return false;
    }

    /**
     * Get Articulate Meta Path
     *
     * @param array $paths
     *
     * @return bool|string
     */
    private function getArticulateMetaPath(array $paths)
    {
        $meta = false;
        
        foreach ($paths as $path) {
            if (substr_count($path, DIRECTORY_SEPARATOR . $this->articulateXmlFile))
                $meta = $path;
        }

        return $meta;
    }

    /**
     * Get Separator For Articulate Path
     *
     * @param array $paths
     *
     * @return bool|string
     */
    private function getSeparatorForArticulatePath(array $paths)
    {
        $k = [];

        foreach ($paths as $p) {
            if (substr_count($p, DIRECTORY_SEPARATOR . $this->articulateIndexFile))
                $k = explode(DIRECTORY_SEPARATOR . $this->articulateIndexFile, $p);
        }

        if (count($k) > 1) {
            return $k[0];
        }

        return false;

    }

    /**
     * filesArticulateAtDir
     *
     * @param string $dirPath
     *
     * @return array
     */
    private function filesArticulateAtDir(string $dirPath)
    {
        $result = [];
        $dir = scandir($dirPath);
        foreach ($dir as $key => $value) {
            if (!in_array($value, [".", ".."])) {
                if (is_dir($dirPath . DIRECTORY_SEPARATOR . $value)){
                    foreach ($this->filesArticulateAtDir($dirPath . DIRECTORY_SEPARATOR . $value) as $v) {
                        if (!empty($v))
                            $result[] = $v;
                    }
                } else {
                    $result[] = $dirPath . DIRECTORY_SEPARATOR . $value;
                }
            }
        }

        return $result;
    }

    /**
     * Clean Articulate Name
     *
     * @param string $string
     *
     * @return string
     */
    private function cleanArticulateName(string $string)
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\_\.]/', '', $string); // Removes special chars.
    }
}