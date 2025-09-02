<?php
// filepath: config\soal.php

return [
    'images' => [
        'pertanyaan' => [
            'max_width' => 800,
            'max_height' => 600,
            'quality' => 85,
            'max_size' => 5120, // KB
        ],
        'pilihan' => [
            'max_width' => 400,
            'max_height' => 300,
            'quality' => 80,
            'max_size' => 2048, // KB
        ],
        'pembahasan' => [
            'max_width' => 800,
            'max_height' => 600,
            'quality' => 85,
            'max_size' => 5120, // KB
        ],
        'allowed_types' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
    ],

    'display' => [
        'pertanyaan_height' => 'auto',
        'pilihan_layout' => 'vertical', // vertical, horizontal, grid
        'show_thumbnails' => true,
        'lazy_loading' => true,
    ]
];
