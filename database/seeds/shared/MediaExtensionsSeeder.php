<?php

namespace Database\Seeds\Shared;

use App\Models\MediaTypes;
use App\Models\MediaExtensions;
use Illuminate\Database\Seeder;

class MediaExtensionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * // Database\Seeds\Shared\MediaExtensionsSeeder
     *
     * @return void
     */
    public function run()
    {
        MediaExtensions::truncate();

        $mediaTypes = MediaTypes::get();

        $validExtensions = [
            'Video' => [
                ['me' => 'ogv', 'mm' => 'video/ogg'],
                ['me' => 'mp4', 'mm' => 'video/mp4'],
                ['me' => 'mp4', 'mm' => 'video/x-m4v'],
                ['me' => 'webm', 'mm' => 'video/webm'],
                ['me' => 'mov', 'mm' => 'video/quicktime'],
                ['me' => 'mov', 'mm' => 'video/x-quicktime'],
                ['me' => 'avi', 'mm' => 'video/avi'],
                ['me' => 'avi', 'mm' => 'video/msvideo'],
                ['me' => 'avi', 'mm' => 'video/x-msvideo']
            ],
            'Audio' => [
                ['me' => 'mp3', 'mm' => 'audio/mpeg'],
                ['me' => 'mp3', 'mm' => 'audio/mp3']
            ],
            'Document' => [
                ['me' => 'doc', 'mm' => 'application/msword'],
                ['me' => 'docx', 'mm' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                ['me' => 'pdf', 'mm' => 'application/pdf'],
            ],
            'Embed' => [
                ['me' => 'youtube', 'mm' => '']
            ],
            'Articulate' => [
                ['me' => 'zip', 'mm' => 'application/zip'],
                ['me' => 'zip', 'mm' => 'application/x-zip-compressed'],
                ['me' => 'zip', 'mm' => 'multipart/x-zip']
            ]
        ];

        $extensions = [];

        foreach ($mediaTypes as $mediaType) {
            foreach ($validExtensions[$mediaType->name] as $type) {
                @$extensions[] = [
                    'media_type_id' => $mediaType->id,
                    'media_extension' => $type['me'],
                    'media_mime' => $type['mm']
                ];
            }
        }

        foreach ($extensions as $extension) {
            MediaExtensions::create($extension);
        }
    }
}
