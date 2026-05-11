<?php

use App\Models\Product;

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),
    'prefix' => env('SCOUT_PREFIX', ''),
    'queue' => env('SCOUT_QUEUE', false),
    'after_commit' => false,
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify' => env('SCOUT_IDENTIFY', false),
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            Product::class => [
                'filterableAttributes' => [
                    'category_slug',
                    'price_visibility',
                    'is_active',
                    'spec_material',
                    'spec_gender',
                    'spec_pose',
                    'spec_mount_type',
                    'spec_head_type',
                    'spec_base_type',
                ],
                'sortableAttributes' => [
                    'id',
                    'base_price',
                    'created_at',
                    'updated_at',
                ],
            ],
        ],
    ],
];
