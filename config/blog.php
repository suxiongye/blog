<?php
/**
 * Created by PhpStorm.
 * User: suxiongye
 * Date: 12/24/16
 * Time: 4:04 PM
 */
return [
    'name' => "suxiongye's blog",
    'title' => 'My Blog',
    'subtitle' => 'https://www.suxiongye.cn',
    'description' => 'Personal blog for learning.',
    'author' => "suxiongye",
    "page_image" => 'home-bg.jpg',
    'posts_per_page' => 10,
    'rss_size' => 25,
    'uploads' => [
        'storage' => 'local',
        'webpath' => '/uploads',
    ],
    'contact_email' => env('MAIL_FROM'),
];
