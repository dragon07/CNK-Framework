<?php

add_filter('rwmb_meta_boxes', function ($meta_boxes) {

    $meta_boxes[] = [
        'id'         => 'cnk_project',
        'title'      => 'Thông tin dự án',
        'post_types' => ['du-an'], // post type bạn tạo
        'fields'     => [
            [
                'name'            => 'Checkbox list',
                'id'              => 'field_id',
                'type'            => 'checkbox_list',
                'inline'          => true,
                'select_all_none' => true,
                'options' => [
                    'java'       => 'Java',
                    'javascript' => 'JavaScript',
                    'php'        => 'PHP',
                    'csharp'     => 'C#',
                    'kotlin'     => 'Kotlin',
                    'swift'      => 'Swift',
                ],
            ],
            [
                'id'               => 'image',
                'name'             => 'Image Advanced',
                'type'             => 'image_advanced',
                'force_delete'     => false,
                'max_file_uploads' => 2,
                'max_status'       => false,
                'image_size'       => 'thumbnail',
            ],
        ],
    ];

    return $meta_boxes;
});