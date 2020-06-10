<?php

return [
	'mode'                  => 'utf-8',
	'format'                => 'A4-L',
	'author'                => 'LearnHub',
	'subject'               => 'Certificate Of Achievement',
	'keywords'              => '',
	'creator'               => 'Current-Stack',
	'display_mode'          => 'fullpage',
	'tempDir'               => storage_path('fonts/cache/'),
	'font_path' => storage_path('fonts/'),
	'font_data' => [
		'roboto' => [
			'R'  => 'Roboto-Regular.ttf',    // regular font
			'B'  => 'Roboto-Bold.ttf',       // optional: bold font
			'I'  => 'Roboto-Italic.ttf',     // optional: italic font
			'BI' => 'Roboto-BoldItalic.ttf' // optional: bold-italic font
		],
	]
];
